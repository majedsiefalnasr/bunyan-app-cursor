<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\UpdateEstimateRequest;
use App\Http\Resources\Api\V1\EstimateResource;
use App\Models\Estimate;
use App\Services\EstimateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstimateController extends BaseController
{
    public function __construct(
        private EstimateService $estimateService,
    ) {
    }

    public function show(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('view', $estimate);
        $estimate->load('items');

        return $this->sendSuccess(
            new EstimateResource($estimate),
            'تم جلب التقدير بنجاح',
            200,
        );
    }

    public function update(UpdateEstimateRequest $request, Estimate $estimate): JsonResponse
    {
        $estimate = $this->estimateService->updateEstimate(
            $request->user(),
            $estimate,
            $request->validated(),
        );
        $estimate->load('items');

        return $this->sendSuccess(
            new EstimateResource($estimate),
            'تم تحديث التقدير بنجاح',
            200,
        );
    }

    public function calculate(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('calculate', $estimate);
        $estimate = $this->estimateService->recalculate($estimate);
        $estimate->load('items');

        return $this->sendSuccess(
            new EstimateResource($estimate),
            'تم إعادة حساب التقدير بنجاح',
            200,
        );
    }

    public function export(Request $request, Estimate $estimate): StreamedResponse
    {
        $this->authorize('export', $estimate);

        return $this->estimateService->exportCsvStream($estimate);
    }

    public function approve(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('approve', $estimate);
        $estimate = $this->estimateService->approve($request->user(), $estimate);
        $estimate->load('items');

        return $this->sendSuccess(
            new EstimateResource($estimate),
            'تمت الموافقة على التقدير',
            200,
        );
    }

    public function reject(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('reject', $estimate);
        $estimate = $this->estimateService->reject($request->user(), $estimate);
        $estimate->load('items');

        return $this->sendSuccess(
            new EstimateResource($estimate),
            'تم رفض التقدير',
            200,
        );
    }
}
