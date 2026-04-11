<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Requests\Api\V1\CreateOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query();

        if ($request->user()->role !== UserRole::Admin) {
            $query->where('customer_id', $request->user()->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate($request->per_page ?? 15);

        return $this->sendSuccess(
            OrderResource::collection($orders),
            'تم جلب الطلبات بنجاح',
            200
        );
    }

    public function show(Order $order): JsonResponse
    {
        if ($order->customer_id !== auth()->id() && auth()->user()?->role !== UserRole::Admin) {
            return $this->forbidden();
        }

        return $this->sendSuccess(
            new OrderResource($order),
            'تم جلب الطلب بنجاح',
            200
        );
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = Order::create([
            'customer_id' => $request->user()->id,
            'project_id' => $request->project_id,
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            $unitPrice = (float) $item['price'];
            $quantity = (int) $item['quantity'];
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $quantity,
            ]);
        }

        $order->update(['total_amount' => $order->items()->sum('subtotal')]);

        return $this->sendSuccess(
            new OrderResource($order),
            'تم إنشاء الطلب بنجاح',
            201
        );
    }
}
