<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreWorkflowDefinitionRequest;
use App\Http\Resources\Api\V1\WorkflowDefinitionResource;
use App\Models\WorkflowConfiguration;
use App\Services\WorkflowDefinitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkflowDefinitionController extends BaseController
{
    public function __construct(private WorkflowDefinitionService $workflowDefinitions)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);
        $paginator = $this->workflowDefinitions->paginate($perPage);

        return $this->sendSuccess(
            WorkflowDefinitionResource::collection($paginator)->response()->getData(true),
            'تم جلب تكوينات سير العمل',
            Response::HTTP_OK,
        );
    }

    public function store(StoreWorkflowDefinitionRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('is_active', $data)) {
            $data['is_active'] = true;
        }

        $config = $this->workflowDefinitions->create($data);

        return $this->sendSuccess(
            new WorkflowDefinitionResource($config->load('approvalRules')),
            'تم إنشاء تكوين سير العمل',
            Response::HTTP_CREATED,
        );
    }

    public function show(WorkflowConfiguration $workflowConfiguration): JsonResponse
    {
        $workflowConfiguration->load('approvalRules');

        return $this->sendSuccess(
            new WorkflowDefinitionResource($workflowConfiguration),
            'تم جلب تكوين سير العمل',
            Response::HTTP_OK,
        );
    }
}
