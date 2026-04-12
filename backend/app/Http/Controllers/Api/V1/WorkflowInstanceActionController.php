<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ResolveWorkflowApprovalRequest;
use App\Http\Resources\Api\V1\WorkflowInstanceResource;
use App\Models\WorkflowApproval;
use App\Models\WorkflowInstance;
use App\Services\WorkflowEngineService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WorkflowInstanceActionController extends BaseController
{
    public function __construct(private WorkflowEngineService $workflowEngine)
    {
    }

    public function approve(ResolveWorkflowApprovalRequest $request, WorkflowInstance $workflowInstance): JsonResponse
    {
        $approval = WorkflowApproval::query()->findOrFail($request->validated('approval_id'));
        if ($approval->workflow_instance_id !== $workflowInstance->id) {
            return $this->notFound();
        }

        $this->authorize('respond', $approval);

        $instance = $this->workflowEngine->approve(
            $approval,
            $request->user(),
            $request->validated('notes'),
        );

        return $this->sendSuccess(
            new WorkflowInstanceResource($instance),
            'تمت الموافقة',
            Response::HTTP_OK,
        );
    }

    public function reject(ResolveWorkflowApprovalRequest $request, WorkflowInstance $workflowInstance): JsonResponse
    {
        $approval = WorkflowApproval::query()->findOrFail($request->validated('approval_id'));
        if ($approval->workflow_instance_id !== $workflowInstance->id) {
            return $this->notFound();
        }

        $this->authorize('respond', $approval);

        $instance = $this->workflowEngine->reject(
            $approval,
            $request->user(),
            $request->validated('notes'),
        );

        return $this->sendSuccess(
            new WorkflowInstanceResource($instance),
            'تم الرفض',
            Response::HTTP_OK,
        );
    }
}
