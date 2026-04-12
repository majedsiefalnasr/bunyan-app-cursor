<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status instanceof TaskStatus ? $this->status->value : (string) $this->status;
        $priority = $this->priority instanceof TaskPriority ? $this->priority->value : ($this->priority ?? 'medium');

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'phase_id' => $this->phase_id,
            'name' => $this->name,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'description' => $this->description,
            'status' => $status,
            'priority' => $priority,
            'budget' => number_format((float) $this->budget, 2, '.', ''),
            'assigned_to' => $this->assigned_to,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'due_date' => $this->due_date?->toIso8601String(),
            'estimated_hours' => $this->estimated_hours !== null ? (string) $this->estimated_hours : null,
            'actual_hours' => $this->actual_hours !== null ? (string) $this->actual_hours : null,
            'sort_order' => $this->sort_order,
            'created_by' => $this->created_by,
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'comments' => TaskCommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
