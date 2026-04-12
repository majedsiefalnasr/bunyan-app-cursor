<?php

namespace App\Http\Resources\Api\V1;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = $this->type instanceof BackedEnum ? $this->type->value : $this->type;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description' => $this->description,
            'type' => $type,
            'is_global' => (bool) $this->is_global,
            'is_active' => (bool) $this->is_active,
            'project_id' => $this->project_id,
            'status_transitions' => $this->status_transitions,
            'approval_requirements' => $this->approval_requirements,
            'approval_rules' => $this->whenLoaded('approvalRules', function () {
                return $this->approvalRules->map(fn ($rule) => [
                    'id' => $rule->id,
                    'entity_type' => $rule->entity_type,
                    'status_from' => $rule->status_from,
                    'status_to' => $rule->status_to,
                    'approver_role' => $rule->approver_role,
                    'approval_count' => $rule->approval_count,
                ]);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
