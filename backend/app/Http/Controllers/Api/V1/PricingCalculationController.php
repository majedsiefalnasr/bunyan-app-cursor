<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CalculatePriceRequest;
use App\Models\Product;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;

class PricingCalculationController extends BaseController
{
    public function __construct(private PricingService $pricingService)
    {
    }

    public function __invoke(CalculatePriceRequest $request): JsonResponse
    {
        $data = $request->validated();
        /** @var Product $product */
        $product = Product::query()->findOrFail((int) $data['product_id']);

        $this->authorize('view', $product);

        $result = $this->pricingService->calculate(
            $product,
            isset($data['product_variant_id']) ? (int) $data['product_variant_id'] : null,
            (int) $data['quantity'],
        );

        return $this->sendSuccess($result, 'تم حساب السعر بنجاح', 200);
    }
}
