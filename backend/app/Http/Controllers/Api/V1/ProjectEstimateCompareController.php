<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\IndexEstimateCompareRequest;
use App\Models\Project;
use App\Services\EstimateService;
use Illuminate\Http\JsonResponse;

class ProjectEstimateCompareController extends BaseController
{
    public function __construct(
        private EstimateService $estimateService,
    ) {
    }

    public function compare(IndexEstimateCompareRequest $request, Project $project): JsonResponse
    {
        $data = $this->estimateService->compare($project, $request->estimateIds());

        return $this->sendSuccess(
            $data,
            'تمت مقارنة التقديرات بنجاح',
            200,
        );
    }
}
