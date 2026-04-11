<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'budget' => number_format((float) $this->budget, 2, '.', ''),
            'location' => $this->location,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'customer' => new UserResource($this->whenLoaded('customer')),
            'contractor' => new UserResource($this->whenLoaded('contractor')),
            'supervising_architect' => new UserResource($this->whenLoaded('supervisingArchitect')),
            'phases_count' => $this->whenCounted('phases'),
            'tasks_count' => $this->whenCounted('tasks'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
