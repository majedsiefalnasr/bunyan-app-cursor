<?php

namespace Tests\Unit\Services\Analytics;

use App\Models\User;
use App\Repositories\Analytics\AnalyticsEventRepository;
use App\Services\Analytics\AnalyticsTrackerService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalyticsTrackerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('analytics_events')) {
            Schema::create('analytics_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_name');
                $table->dateTime('occurred_at');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('role')->nullable();
                $table->text('metadata')->nullable();
                $table->string('session_id')->nullable();
                $table->string('thread_id')->nullable();
                $table->string('request_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_track_sanitizes_metadata_and_persists_event(): void
    {
        $service = new AnalyticsTrackerService(app(AnalyticsEventRepository::class));

        $user = User::factory()->contractor()->create();

        $occurredAt = CarbonImmutable::parse('2026-01-01 10:00:00', 'UTC');
        $event = $service->track(
            eventName: 'order.placed',
            user: $user,
            occurredAt: $occurredAt,
            metadata: [
                'order_id' => 123,
                'supplier_id' => str_repeat('x', 200), // trimmed
                'project_id' => null, // dropped
                'not_allowed' => 'nope', // dropped
            ],
            sessionId: 'sess',
            threadId: 'thr',
            requestId: 'req',
        );

        $this->assertSame('order.placed', $event->event_name);
        $this->assertSame($user->id, $event->user_id);
        $this->assertSame($user->role->value, $event->role);
        $this->assertSame('sess', $event->session_id);
        $this->assertSame('thr', $event->thread_id);
        $this->assertSame('req', $event->request_id);

        $metadata = (array) $event->metadata;
        $this->assertArrayHasKey('order_id', $metadata);
        $this->assertArrayHasKey('supplier_id', $metadata);
        $this->assertArrayNotHasKey('project_id', $metadata);
        $this->assertArrayNotHasKey('not_allowed', $metadata);
        $this->assertSame('123', $metadata['order_id']);
        $this->assertSame(64, mb_strlen($metadata['supplier_id']));
    }

    public function test_track_sanitizes_invalid_utf8_without_throwing(): void
    {
        $service = new AnalyticsTrackerService(app(AnalyticsEventRepository::class));

        $user = User::factory()->customer()->create();

        // Invalid UTF-8 can be normalized by mb_substr() depending on environment;
        // we just assert we still get a safe, bounded string.
        $invalidUtf8 = "\xB1\x31";
        $event = $service->track('session.started', $user, CarbonImmutable::now('UTC'), [
            'order_id' => $invalidUtf8,
        ]);

        $metadata = (array) $event->metadata;
        $this->assertArrayHasKey('order_id', $metadata);
        $this->assertIsString($metadata['order_id']);
        $this->assertLessThanOrEqual(64, mb_strlen($metadata['order_id']));
    }
}
