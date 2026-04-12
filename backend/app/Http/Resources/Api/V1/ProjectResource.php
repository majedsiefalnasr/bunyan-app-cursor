<?php

namespace App\Http\Resources\Api\V1;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $statusValue = $this->status instanceof BackedEnum ? $this->status->value : $this->status;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description' => $this->description,
            'status' => $statusValue,
            'budget' => number_format((float) $this->budget, 2, '.', ''),
            'budget_estimated' => $this->budget_estimated !== null
                ? number_format((float) $this->budget_estimated, 2, '.', '')
                : null,
            'budget_actual' => $this->budget_actual !== null
                ? number_format((float) $this->budget_actual, 2, '.', '')
                : null,
            'location' => $this->location,
            'city' => $this->city,
            'district' => $this->district,
            'location_lat' => $this->location_lat,
            'location_lng' => $this->location_lng,
            'project_type' => $this->project_type,
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
