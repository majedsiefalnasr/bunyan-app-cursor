<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\SyncProductPricingRequest;
use App\Http\Resources\Api\V1\PriceTierResource;
use App\Models\Product;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;

class ProductPricingController extends BaseController
{
    public function __construct(private PricingService $pricingService)
    {
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $tiers = $this->pricingService->listTiers($product);

        return $this->sendSuccess(
            ['tiers' => PriceTierResource::collection($tiers)],
            'تم جلب التسعير بنجاح',
            200
        );
    }

    public function sync(SyncProductPricingRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $this->pricingService->syncTiers($product, $request->validated('tiers'));

        $tiers = $this->pricingService->listTiers($product);

        return $this->sendSuccess(
            ['tiers' => PriceTierResource::collection($tiers)],
            'تم حفظ شرائح السعر بنجاح',
            200
        );
    }
}
