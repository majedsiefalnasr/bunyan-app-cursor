<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowInstanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'workflow_configuration_id' => $this->workflow_configuration_id,
            'workflowable_type' => $this->workflowable_type,
            'workflowable_id' => $this->workflowable_id,
            'workflow_configuration' => new WorkflowDefinitionResource($this->whenLoaded('workflowConfiguration')),
            'approvals' => WorkflowApprovalResource::collection($this->whenLoaded('approvals')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
