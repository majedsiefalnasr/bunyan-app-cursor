<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\WorkflowApprovalResource;
use App\Services\WorkflowEngineService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PendingApprovalController extends BaseController
{
    public function __construct(private WorkflowEngineService $workflowEngine)
    {
    }

    public function index(): JsonResponse
    {
        $rows = $this->workflowEngine->pendingForUser(request()->user());

        return $this->sendSuccess(
            WorkflowApprovalResource::collection($rows)->resolve(),
            'تم جلب الموافقات المعلقة',
            Response::HTTP_OK,
        );
    }
}
