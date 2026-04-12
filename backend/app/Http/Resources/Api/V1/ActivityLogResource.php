<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\ActivityLogAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $actionValue = $this->action instanceof ActivityLogAction
            ? $this->action->value
            : (string) $this->action;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'action' => $actionValue,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'properties' => $this->properties_json,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at?->toIso8601String(),
            'actor' => $this->whenLoaded('actor', function () {
                return [
                    'id' => $this->actor->id,
                    'name' => $this->actor->name,
                ];
            }),
        ];
    }
}
