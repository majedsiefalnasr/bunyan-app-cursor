<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\AdjustInventoryRequest;
use App\Http\Resources\Api\V1\InventoryResource;
use App\Http\Resources\Api\V1\StockMovementResource;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends BaseController
{
    public function __construct(private InventoryService $inventoryService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'per_page' => $request->get('per_page'),
            'product_id' => $request->filled('product_id') ? (int) $request->get('product_id') : null,
            'warehouse_location' => $request->filled('warehouse_location')
                ? $request->string('warehouse_location')->toString()
                : null,
            'low_stock' => $request->boolean('low_stock'),
        ];

        $paginator = $this->inventoryService->paginateForUser($request->user(), $filters);

        return $this->sendSuccess(
            InventoryResource::collection($paginator),
            'تم جلب المخزون بنجاح',
            200
        );
    }

    public function lowStock(Request $request): JsonResponse
    {
        $filters = [
            'per_page' => $request->get('per_page'),
            'product_id' => $request->filled('product_id') ? (int) $request->get('product_id') : null,
        ];

        $paginator = $this->inventoryService->lowStockForUser($request->user(), $filters);

        return $this->sendSuccess(
            InventoryResource::collection($paginator),
            'تم جلب أصناف المخزون المنخفض بنجاح',
            200
        );
    }

    public function adjust(AdjustInventoryRequest $request, Product $product): JsonResponse
    {
        $line = $this->inventoryService->adjust($product, $request->user(), $request->validated());

        return $this->sendSuccess(
            new InventoryResource($line),
            'تم تعديل المخزون بنجاح',
            200
        );
    }

    public function movements(Request $request, Product $product): JsonResponse
    {
        $this->authorize('manageInventory', $product);

        $filters = [
            'per_page' => $request->get('per_page'),
            'variant_id' => $request->filled('variant_id') ? (int) $request->get('variant_id') : null,
        ];

        $paginator = $this->inventoryService->paginateMovements($product, $filters);

        return $this->sendSuccess(
            StockMovementResource::collection($paginator),
            'تم جلب حركات المخزون بنجاح',
            200
        );
    }
}
