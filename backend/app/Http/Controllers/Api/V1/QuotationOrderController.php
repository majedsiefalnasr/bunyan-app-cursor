<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Quotation;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationOrderController extends BaseController
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
    }

    public function store(Request $request, Quotation $quotation): JsonResponse
    {
        $this->authorize('convertToOrder', $quotation);

        $order = $this->orderService->createFromQuotation($request->user(), $quotation);

        return $this->sendSuccess(
            new OrderResource($order),
            'تم إنشاء الطلب من عرض السعر بنجاح',
            201
        );
    }
}
