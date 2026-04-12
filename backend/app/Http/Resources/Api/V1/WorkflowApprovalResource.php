<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workflow_instance_id' => $this->workflow_instance_id,
            'approval_rule_id' => $this->approval_rule_id,
            'approver_role' => $this->approver_role,
            'action' => $this->action->value,
            'notes' => $this->notes,
            'acted_by' => $this->acted_by,
            'acted_at' => $this->acted_at?->toIso8601String(),
            'workflow_instance' => $this->whenLoaded('workflowInstance', function () {
                return [
                    'id' => $this->workflowInstance->id,
                    'status' => $this->workflowInstance->status->value,
                    'workflow_configuration_id' => $this->workflowInstance->workflow_configuration_id,
                    'workflowable_id' => $this->workflowInstance->workflowable_id,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
