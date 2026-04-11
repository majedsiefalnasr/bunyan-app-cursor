<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phase_id' => $this->phase_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'budget' => number_format((float) $this->budget, 2, '.', ''),
            'assigned_to' => $this->assigned_to,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
