<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'type' => $this->type,
            'participants' => $this->when(
                $this->resource->relationLoaded('participants'),
                fn () => UserResource::collection($this->participants->map->user),
            ),
            'latest_message' => new MessageResource($this->whenLoaded('latestMessage')),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
