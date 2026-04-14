<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Repositories\Analytics\AnalyticsEventRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;

final class AnalyticsTrackerService
{
    private const METADATA_ALLOWED_KEYS = [
        'order_id',
        'project_id',
        'supplier_id',
    ];

    private const METADATA_MAX_KEYS = 10;

    private const METADATA_MAX_VALUE_LEN = 64;

    private const METADATA_MAX_BYTES = 1024;

    public function __construct(
        private readonly AnalyticsEventRepository $events,
    ) {
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function track(
        string $eventName,
        ?User $user,
        CarbonImmutable $occurredAt,
        array $metadata = [],
        ?string $sessionId = null,
        ?string $threadId = null,
        ?string $requestId = null,
    ): AnalyticsEvent {
        $sanitizedMetadata = $this->sanitizeMetadata($metadata);

        /** @var AnalyticsEvent $event */
        $event = $this->events->create([
            'event_name' => $eventName,
            'occurred_at' => $occurredAt,
            'user_id' => $user?->id,
            'role' => $user?->role?->value,
            'metadata' => $sanitizedMetadata,
            'session_id' => $sessionId,
            'thread_id' => $threadId,
            'request_id' => $requestId,
        ]);

        return $event;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, string>
     */
    private function sanitizeMetadata(array $metadata): array
    {
        $only = Arr::only($metadata, self::METADATA_ALLOWED_KEYS);

        $trimmed = [];
        foreach ($only as $k => $v) {
            if ($v === null) {
                continue;
            }

            $value = (string) $v;
            $value = $this->normalizeUtf8($value);
            $value = mb_substr($value, 0, self::METADATA_MAX_VALUE_LEN);
            $trimmed[(string) $k] = $value;
        }

        $trimmed = array_slice($trimmed, 0, self::METADATA_MAX_KEYS, true);

        // Enforce total bytes cap (best-effort, deterministic)
        $json = json_encode($trimmed, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            return [];
        }

        if (strlen($json) > self::METADATA_MAX_BYTES) {
            return [];
        }

        return $trimmed;
    }

    private function normalizeUtf8(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        // Ensure invalid byte sequences don't break Eloquent JSON casts.
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
