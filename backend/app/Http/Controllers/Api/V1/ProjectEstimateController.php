<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\IndexProjectEstimatesRequest;
use App\Http\Requests\Api\V1\StoreProjectEstimateRequest;
use App\Http\Resources\Api\V1\EstimateResource;
use App\Models\Project;
use App\Repositories\EstimateRepository;
use App\Services\EstimateService;
use Illuminate\Http\JsonResponse;

class ProjectEstimateController extends BaseController
{
    public function __construct(
        private EstimateRepository $estimateRepository,
        private EstimateService $estimateService,
    ) {
    }

    public function index(IndexProjectEstimatesRequest $request, Project $project): JsonResponse
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);
        $paginator = $this->estimateRepository->paginateForProject($project, $perPage);

        return $this->sendSuccess(
            EstimateResource::collection($paginator),
            'تم جلب التقديرات بنجاح',
            200,
        );
    }

    public function store(StoreProjectEstimateRequest $request, Project $project): JsonResponse
    {
        $validated = $request->validated();
        $markup = isset($validated['markup_percentage']) ? (float) $validated['markup_percentage'] : 0.0;
        $estimate = $this->estimateService->createEstimate(
            $request->user(),
            $project,
            (string) $validated['title'],
            isset($validated['description']) ? (string) $validated['description'] : null,
            $markup,
        );

        return $this->sendSuccess(
            new EstimateResource($estimate),
            'تم إنشاء التقدير بنجاح',
            201,
        );
    }
}
