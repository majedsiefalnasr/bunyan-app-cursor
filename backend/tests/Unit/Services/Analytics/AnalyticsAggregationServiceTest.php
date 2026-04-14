<?php

namespace Tests\Unit\Services\Analytics;

use App\Enums\AnalyticsMetricKey;
use App\Models\Order;
use App\Models\Project;
use App\Models\SupplierProfile;
use App\Models\User;
use App\Repositories\Analytics\AnalyticsMetricRollupRepository;
use App\Services\Analytics\AnalyticsAggregationService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalyticsAggregationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureAnalyticsTables();
    }

    public function test_aggregate_upserts_rollups_for_all_metrics_and_computes_values(): void
    {
        $u1 = User::factory()->customer()->create();
        $u2 = User::factory()->contractor()->create();
        $u3 = User::factory()->customer()->create();

        DB::table('analytics_events')->insert([
            [
                'event_name' => 'session.started',
                'occurred_at' => '2026-01-01 10:00:00',
                'user_id' => $u1->id,
                'role' => 'customer',
                'metadata' => json_encode([], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'session.started',
                'occurred_at' => '2026-01-01 12:00:00',
                'user_id' => $u2->id,
                'role' => 'contractor',
                'metadata' => json_encode([], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'user.registered',
                'occurred_at' => '2026-01-01 13:00:00',
                'user_id' => $u3->id,
                'role' => 'customer',
                'metadata' => json_encode([], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'order.placed',
                'occurred_at' => '2026-01-01 14:00:00',
                'user_id' => $u1->id,
                'role' => 'customer',
                'metadata' => json_encode(['order_id' => '10'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Order::factory()->create([
            'total_amount' => 150.25,
            'created_at' => '2026-01-01 09:00:00',
        ]);
        Order::factory()->create([
            'total_amount' => 49.75,
            'created_at' => '2026-01-01 18:00:00',
        ]);

        Project::factory()->create([
            'created_at' => '2026-01-01 08:00:00',
        ]);

        SupplierProfile::factory()->create([
            'created_at' => '2026-01-01 07:00:00',
        ]);

        $service = new AnalyticsAggregationService(app(AnalyticsMetricRollupRepository::class));

        $service->aggregate(
            fromDateUtc: CarbonImmutable::parse('2026-01-01', 'UTC'),
            toDateUtc: CarbonImmutable::parse('2026-01-01', 'UTC'),
            bucket: 'day',
        );

        $this->assertDatabaseCount('analytics_metric_rollups', count(AnalyticsMetricKey::cases()));

        $activeUsers = DB::table('analytics_metric_rollups')
            ->where('metric_key', AnalyticsMetricKey::PlatformActiveUsers->value)
            ->first();
        $this->assertNotNull($activeUsers);
        $this->assertSame('day', $activeUsers->bucket);
        $this->assertStringStartsWith('2026-01-01', (string) $activeUsers->bucket_start);
        $this->assertStringStartsWith('2026-01-02', (string) $activeUsers->bucket_end);
        $this->assertEquals(2.0, (float) $activeUsers->value);

        $gmv = DB::table('analytics_metric_rollups')
            ->where('metric_key', AnalyticsMetricKey::CommerceGmv->value)
            ->first();
        $this->assertNotNull($gmv);
        $this->assertEquals(200.0, (float) $gmv->value);

        $avgOrder = DB::table('analytics_metric_rollups')
            ->where('metric_key', AnalyticsMetricKey::CommerceAvgOrderValue->value)
            ->first();
        $this->assertNotNull($avgOrder);
        $this->assertEquals(100.0, (float) $avgOrder->value);
    }

    public function test_aggregate_safe_divide_handles_zero_denominator(): void
    {
        $service = new AnalyticsAggregationService(app(AnalyticsMetricRollupRepository::class));

        $service->aggregate(
            fromDateUtc: CarbonImmutable::parse('2026-01-02', 'UTC'),
            toDateUtc: CarbonImmutable::parse('2026-01-02', 'UTC'),
            bucket: 'day',
        );

        $avgOrder = DB::table('analytics_metric_rollups')
            ->where('metric_key', AnalyticsMetricKey::CommerceAvgOrderValue->value)
            ->first();
        $this->assertNotNull($avgOrder);
        $this->assertEquals(0.0, (float) $avgOrder->value);

        $conversion = DB::table('analytics_metric_rollups')
            ->where('metric_key', AnalyticsMetricKey::CommerceConversionRate->value)
            ->first();
        $this->assertNotNull($conversion);
        $this->assertEquals(0.0, (float) $conversion->value);
    }

    private function ensureAnalyticsTables(): void
    {
        if (! Schema::hasTable('analytics_events')) {
            Schema::create('analytics_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_name');
                $table->dateTime('occurred_at');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('role')->nullable();
                $table->text('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('analytics_metric_rollups')) {
            Schema::create('analytics_metric_rollups', function (Blueprint $table) {
                $table->id();
                $table->string('metric_key');
                $table->string('bucket');
                $table->date('bucket_start');
                $table->date('bucket_end');
                $table->decimal('value', 16, 4)->default(0);
                $table->text('dimensions')->nullable();
                $table->string('dimensions_hash');
                $table->dateTime('computed_at')->nullable();
                $table->timestamps();

                $table->unique(['metric_key', 'bucket', 'bucket_start', 'dimensions_hash'], 'uniq_rollup_key');
            });
        }
    }
}
