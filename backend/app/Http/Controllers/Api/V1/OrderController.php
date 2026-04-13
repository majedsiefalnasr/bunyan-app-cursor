<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Requests\Api\V1\CreateOrderRequest;
use App\Http\Requests\Api\V1\UpdateOrderStatusRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $orders = $this->orderService->paginateForUser(
            $request->user(),
            $request->only(['status', 'per_page'])
        );

        return $this->sendSuccess(
            OrderResource::collection($orders),
            'تم جلب الطلبات بنجاح',
            200
        );
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);
        $order->load(['items.product', 'items.variant', 'project', 'quotation', 'supplierProfile']);

        return $this->sendSuccess(
            new OrderResource($order),
            'تم جلب الطلب بنجاح',
            200
        );
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);
        $validated = $request->validated();
        $order = $this->orderService->createFromItems($request->user(), $validated);

        return $this->sendSuccess(
            new OrderResource($order),
            'تم إنشاء الطلب بنجاح',
            201
        );
    }

    public function confirm(Request $request, Order $order): JsonResponse
    {
        $this->authorize('confirm', $order);
        $order = $this->orderService->confirm($request->user(), $order);

        return $this->sendSuccess(
            new OrderResource($order),
            'تم تأكيد الطلب بنجاح',
            200
        );
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);
        $order = $this->orderService->cancel($request->user(), $order);

        return $this->sendSuccess(
            new OrderResource($order),
            'تم إلغاء الطلب بنجاح',
            200
        );
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->authorize('transitionStatus', $order);
        $status = OrderStatus::from((string) $request->validated('status'));
        $order = $this->orderService->transitionStatus($request->user(), $order, $status);

        return $this->sendSuccess(
            new OrderResource($order),
            'تم تحديث حالة الطلب بنجاح',
            200
        );
    }
}
