<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\WorkflowInstanceResource;
use App\Models\Project;
use App\Services\WorkflowEngineService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectWorkflowController extends BaseController
{
    public function __construct(private WorkflowEngineService $workflowEngine)
    {
    }

    public function start(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $instance = $this->workflowEngine->startForProject($project, request()->user());

        return $this->sendSuccess(
            new WorkflowInstanceResource($instance),
            'تم بدء سير العمل',
            Response::HTTP_CREATED,
        );
    }
}
