<?php

namespace App\Http\Controllers\Api\V1;

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

        if ($request->user()->role !== 'admin') {
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
        if ($order->customer_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return $this->sendError('غير مصرح', [], 403);
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
            'total_price' => 0,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        $order->update(['total_price' => $order->items()->sum('price')]);

        return $this->sendSuccess(
            new OrderResource($order),
            'تم إنشاء الطلب بنجاح',
            201
        );
    }
}
