<?php

namespace Tests\Unit\Services\Analytics;

use App\Enums\AnalyticsMetricKey;
use App\Repositories\Analytics\AnalyticsMetricRollupRepository;
use App\Services\Analytics\AnalyticsDateRange;
use App\Services\Analytics\AnalyticsReadService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalyticsReadServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
            });
        }
    }

    public function test_overview_returns_cached_payload_when_available(): void
    {
        $range = AnalyticsDateRange::fromDates(
            CarbonImmutable::parse('2026-01-01', 'UTC'),
            CarbonImmutable::parse('2026-01-02', 'UTC'),
        );

        Log::shouldReceive('channel')->with('audit')->once()->andReturnSelf();
        Log::shouldReceive('info')->once();

        Cache::shouldReceive('get')->once()->andReturn([
            'range' => ['from' => '2026-01-01', 'to' => '2026-01-02', 'bucket' => 'day'],
            'compare' => ['mode' => 'none'],
            'kpis' => [],
        ]);

        $service = new AnalyticsReadService(app(AnalyticsMetricRollupRepository::class));
        $payload = $service->overview($range, 'day', 'none', 10, 'admin');

        $this->assertSame('2026-01-01', $payload['range']['from']);
        $this->assertSame('none', $payload['compare']['mode']);
        $this->assertSame([], $payload['kpis']);
    }

    public function test_overview_computes_and_caches_when_lock_acquired(): void
    {
        $range = AnalyticsDateRange::fromDates(
            CarbonImmutable::parse('2026-01-01', 'UTC'),
            CarbonImmutable::parse('2026-01-01', 'UTC'),
        );

        // Seed a few rollups to sum
        app(AnalyticsMetricRollupRepository::class)->upsertRollup(
            [
                'metric_key' => AnalyticsMetricKey::PlatformActiveUsers->value,
                'bucket' => 'day',
                'bucket_start' => '2026-01-01',
                'dimensions_hash' => hash('sha256', '{}'),
            ],
            [
                'bucket_end' => '2026-01-02',
                'value' => 5,
                'dimensions' => [],
                'computed_at' => CarbonImmutable::now('UTC'),
                'dimensions_hash' => hash('sha256', '{}'),
            ],
        );

        Log::shouldReceive('channel')->with('audit')->once()->andReturnSelf();
        Log::shouldReceive('info')->once();

        $lock = new class
        {
            public function get(): bool
            {
                return true;
            }

            public function release(): void
            {
            }
        };

        Cache::shouldReceive('get')->once()->andReturn(null);
        Cache::shouldReceive('lock')->once()->andReturn($lock);
        Cache::shouldReceive('put')->once();

        $service = new AnalyticsReadService(app(AnalyticsMetricRollupRepository::class));
        $payload = $service->overview($range, 'day', 'none', 1, 'admin');

        $this->assertSame('2026-01-01', $payload['range']['from']);
        $this->assertCount(6, $payload['kpis']);

        $active = collect($payload['kpis'])->firstWhere('key', AnalyticsMetricKey::PlatformActiveUsers->value);
        $this->assertNotNull($active);
        $this->assertSame(5.0, $active['value']);
        $this->assertSame(['value' => null, 'pct' => null], $active['delta']);
    }

    public function test_overview_with_previous_period_sets_delta_value_and_pct(): void
    {
        $range = AnalyticsDateRange::fromDates(
            CarbonImmutable::parse('2026-02-01', 'UTC'),
            CarbonImmutable::parse('2026-02-01', 'UTC'),
        );

        // Current period rollup (2026-02-01)
        DB::table('analytics_metric_rollups')->insert([
            'metric_key' => AnalyticsMetricKey::PlatformActiveUsers->value,
            'bucket' => 'day',
            'bucket_start' => '2026-02-01',
            'bucket_end' => '2026-02-02',
            'value' => 20,
            'dimensions' => json_encode([], JSON_UNESCAPED_UNICODE),
            'dimensions_hash' => hash('sha256', '{}'),
            'computed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Previous period for a 1-day range (2026-01-31)
        DB::table('analytics_metric_rollups')->insert([
            'metric_key' => AnalyticsMetricKey::PlatformActiveUsers->value,
            'bucket' => 'day',
            'bucket_start' => '2026-01-31',
            'bucket_end' => '2026-02-01',
            'value' => 10,
            'dimensions' => json_encode([], JSON_UNESCAPED_UNICODE),
            'dimensions_hash' => hash('sha256', '{}'),
            'computed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::shouldReceive('channel')->with('audit')->once()->andReturnSelf();
        Log::shouldReceive('info')->once();

        Cache::shouldReceive('get')->once()->andReturn(null);
        $lock = new class
        {
            public function get(): bool
            {
                return true;
            }

            public function release(): void
            {
            }
        };
        Cache::shouldReceive('lock')->once()->andReturn($lock);
        Cache::shouldReceive('put')->once();

        $service = new AnalyticsReadService(app(AnalyticsMetricRollupRepository::class));
        $payload = $service->overview($range, 'day', 'previous_period', 1, 'admin');

        $active = collect($payload['kpis'])->firstWhere('key', AnalyticsMetricKey::PlatformActiveUsers->value);
        $this->assertNotNull($active);
        $this->assertSame(20.0, $active['value']);
        $this->assertSame(10.0, $active['delta']['value']);
        $this->assertSame(1.0, $active['delta']['pct']);
    }

    public function test_cached_serves_compute_when_lock_unavailable_and_cache_never_fills(): void
    {
        $range = AnalyticsDateRange::fromDates(
            CarbonImmutable::parse('2026-01-01', 'UTC'),
            CarbonImmutable::parse('2026-01-01', 'UTC'),
        );

        Log::shouldReceive('channel')->with('audit')->once()->andReturnSelf();
        Log::shouldReceive('info')->once();

        $lock = new class
        {
            public function get(): bool
            {
                return false;
            }
        };

        Cache::shouldReceive('get')->times(1 + 3)->andReturn(null);
        Cache::shouldReceive('lock')->once()->andReturn($lock);

        $service = new AnalyticsReadService(app(AnalyticsMetricRollupRepository::class));
        $payload = $service->overview($range, 'day', 'none', 1, 'admin');

        $this->assertArrayHasKey('range', $payload);
        $this->assertArrayHasKey('kpis', $payload);
    }

    public function test_trends_returns_series_for_multiple_keys(): void
    {
        $range = AnalyticsDateRange::fromDates(
            CarbonImmutable::parse('2026-01-01', 'UTC'),
            CarbonImmutable::parse('2026-01-02', 'UTC'),
        );

        foreach ([
            [AnalyticsMetricKey::CommerceOrderVolume->value, '2026-01-01', 2],
            [AnalyticsMetricKey::CommerceOrderVolume->value, '2026-01-02', 4],
            [AnalyticsMetricKey::CommerceGmv->value, '2026-01-01', 100],
            [AnalyticsMetricKey::CommerceGmv->value, '2026-01-02', 200],
        ] as [$key, $start, $val]) {
            DB::table('analytics_metric_rollups')->insert([
                'metric_key' => $key,
                'bucket' => 'day',
                'bucket_start' => $start,
                'bucket_end' => CarbonImmutable::parse($start, 'UTC')->addDay()->toDateString(),
                'value' => $val,
                'dimensions' => json_encode([], JSON_UNESCAPED_UNICODE),
                'dimensions_hash' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                'computed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Log::shouldReceive('channel')->with('audit')->once()->andReturnSelf();
        Log::shouldReceive('info')->once();

        Cache::shouldReceive('get')->once()->andReturn(null);
        $lock = new class
        {
            public function get(): bool
            {
                return true;
            }

            public function release(): void
            {
            }
        };
        Cache::shouldReceive('lock')->once()->andReturn($lock);
        Cache::shouldReceive('put')->once();

        $service = new AnalyticsReadService(app(AnalyticsMetricRollupRepository::class));
        $payload = $service->trends($range, 'day', [
            AnalyticsMetricKey::CommerceOrderVolume,
            AnalyticsMetricKey::CommerceGmv,
        ], 1, 'admin');

        $this->assertSame('day', $payload['bucket']);
        $this->assertCount(2, $payload['series']);
        $orders = collect($payload['series'])->firstWhere('key', AnalyticsMetricKey::CommerceOrderVolume->value);
        $gmv = collect($payload['series'])->firstWhere('key', AnalyticsMetricKey::CommerceGmv->value);
        $this->assertNotNull($orders);
        $this->assertNotNull($gmv);
        $this->assertNotEmpty($orders['points']);
        $this->assertNotEmpty($gmv['points']);
    }

    public function test_metric_series_reads_rollups_and_casts_values(): void
    {
        $range = AnalyticsDateRange::fromDates(
            CarbonImmutable::parse('2026-01-01', 'UTC'),
            CarbonImmutable::parse('2026-01-01', 'UTC'),
        );

        DB::table('analytics_metric_rollups')->insert([
            'metric_key' => AnalyticsMetricKey::CommerceOrderVolume->value,
            'bucket' => 'day',
            'bucket_start' => '2026-01-01',
            'bucket_end' => '2026-01-02',
            'value' => 2,
            'dimensions' => json_encode([], JSON_UNESCAPED_UNICODE),
            'dimensions_hash' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            'computed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(1, DB::table('analytics_metric_rollups')
            ->where('metric_key', AnalyticsMetricKey::CommerceOrderVolume->value)
            ->where('bucket', 'day')
            ->where('dimensions_hash', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855')
            ->where('bucket_start', '>=', '2026-01-01')
            ->where('bucket_start', '<', '2026-01-02')
            ->count());

        Log::shouldReceive('channel')->with('audit')->once()->andReturnSelf();
        Log::shouldReceive('info')->once();

        Cache::shouldReceive('get')->once()->andReturn(null);
        $lock = new class
        {
            public function get(): bool
            {
                return true;
            }

            public function release(): void
            {
            }
        };
        Cache::shouldReceive('lock')->once()->andReturn($lock);
        Cache::shouldReceive('put')->once();

        $service = new AnalyticsReadService(app(AnalyticsMetricRollupRepository::class));
        $payload = $service->metricSeries($range, 'day', AnalyticsMetricKey::CommerceOrderVolume, 1, 'admin');

        $this->assertSame(AnalyticsMetricKey::CommerceOrderVolume->value, $payload['key']);
        $this->assertSame('day', $payload['bucket']);
        $this->assertStringStartsWith('2026-01-01', $payload['series'][0]['t']);
        $this->assertSame(2.0, $payload['series'][0]['v']);
    }
}
