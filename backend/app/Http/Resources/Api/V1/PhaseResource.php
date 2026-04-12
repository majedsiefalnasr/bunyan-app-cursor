<?php

namespace App\Http\Resources\Api\V1;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $statusValue = $this->status instanceof BackedEnum ? $this->status->value : $this->status;

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'sort_order' => $this->sort_order,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description' => $this->description,
            'status' => $statusValue,
            'budget' => number_format((float) $this->budget, 2, '.', ''),
            'progress' => $this->progress,
            'completion_percentage' => $this->progress,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'tasks_count' => $this->whenCounted('tasks'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
