<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PlatformDatabaseNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PlatformDatabaseNotification */
class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->data ?? [];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'preference_type' => $payload['preference_type'] ?? null,
            'title_ar' => $payload['title_ar'] ?? '',
            'title_en' => $payload['title_en'] ?? '',
            'body_ar' => $payload['body_ar'] ?? '',
            'body_en' => $payload['body_en'] ?? '',
            'channel' => $this->channel,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
