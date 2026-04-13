<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreEstimateItemRequest;
use App\Http\Requests\Api\V1\UpdateEstimateItemRequest;
use App\Http\Resources\Api\V1\EstimateItemResource;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Services\EstimateService;
use Illuminate\Http\JsonResponse;

class EstimateItemController extends BaseController
{
    public function __construct(
        private EstimateService $estimateService,
    ) {
    }

    public function store(StoreEstimateItemRequest $request, Estimate $estimate): JsonResponse
    {
        $item = $this->estimateService->addItem($estimate, $request->validated());

        return $this->sendSuccess(
            new EstimateItemResource($item),
            'تمت إضافة البند بنجاح',
            201,
        );
    }

    public function update(UpdateEstimateItemRequest $request, Estimate $estimate, EstimateItem $estimateItem): JsonResponse
    {
        $item = $this->estimateService->updateItem($estimate, $estimateItem, $request->validated());

        return $this->sendSuccess(
            new EstimateItemResource($item),
            'تم تحديث البند بنجاح',
            200,
        );
    }

    public function destroy(Estimate $estimate, EstimateItem $estimateItem): JsonResponse
    {
        $this->authorize('manageItems', $estimate);
        $this->estimateService->deleteItem($estimate, $estimateItem);

        return $this->sendSuccess(
            null,
            'تم حذف البند بنجاح',
            200,
        );
    }
}
