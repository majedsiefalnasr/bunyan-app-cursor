<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\User;
use App\Repositories\OrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly InventoryService $inventory,
    ) {
    }

    public function paginateForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->orders->paginateForActor($user, $filters);
    }

    /**
     * @param  array{project_id?: int|null, items: array<int, array{product_id: int, quantity: int, price: float|int|string, variant_id?: int|null}>}  $payload
     */
    public function createFromItems(User $user, array $payload): Order
    {
        return DB::transaction(function () use ($user, $payload) {
            $items = $payload['items'];
            $subtotal = 0.0;
            $rows = [];
            foreach ($items as $item) {
                $unit = (float) $item['price'];
                $qty = (int) $item['quantity'];
                $line = round($unit * $qty, 2);
                $subtotal += $line;
                $rows[] = [
                    'product_id' => (int) $item['product_id'],
                    'variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'subtotal' => $line,
                ];
            }

            $orderNumber = $this->allocateOrderNumber();
            /** @var Order $order */
            $order = $this->orders->create([
                'customer_id' => $user->id,
                'project_id' => $payload['project_id'] ?? null,
                'supplier_id' => null,
                'quotation_id' => null,
                'order_number' => $orderNumber,
                'status' => OrderStatus::Pending->value,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'total_amount' => round($subtotal, 2),
            ]);

            foreach ($rows as $row) {
                $order->items()->create($row);
            }

            Log::info('order.created', [
                'action' => 'order.created',
                'order_id' => $order->id,
                'user_id' => $user->id,
            ]);

            return $order->fresh(['items.product']) ?? $order;
        });
    }

    public function createFromQuotation(User $user, Quotation $quotation): Order
    {
        $quotation->loadMissing(['items.rfqItem.product', 'rfq']);

        if ($quotation->status !== QuotationStatus::Accepted) {
            throw ValidationException::withMessages([
                'quotation' => ['يجب أن يكون عرض السعر مقبولاً لإنشاء طلب'],
            ]);
        }

        $rfq = $quotation->rfq;
        if ($rfq === null) {
            throw ValidationException::withMessages([
                'quotation' => ['عرض السعر غير مرتبط بطلب تسعير'],
            ]);
        }

        if ($user->role !== UserRole::Admin && (int) $rfq->created_by !== (int) $user->id) {
            throw ValidationException::withMessages([
                'quotation' => ['غير مصرح بتحويل هذا العرض'],
            ]);
        }

        return DB::transaction(function () use ($user, $quotation, $rfq) {
            $subtotal = 0.0;
            $rows = [];
            foreach ($quotation->items as $qItem) {
                $rfqItem = $qItem->rfqItem;
                if ($rfqItem === null || $rfqItem->product_id === null) {
                    continue;
                }
                $qty = (int) max(1, (int) ceil((float) $rfqItem->quantity));
                $unit = (float) $qItem->unit_price;
                $line = round($unit * $qty, 2);
                $subtotal += $line;
                $rows[] = [
                    'product_id' => (int) $rfqItem->product_id,
                    'variant_id' => null,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'subtotal' => $line,
                    'description' => $rfqItem->description,
                ];
            }

            if ($rows === []) {
                throw ValidationException::withMessages([
                    'quotation' => ['لا توجد بنود صالحة لإنشاء الطلب'],
                ]);
            }

            $orderNumber = $this->allocateOrderNumber();
            /** @var Order $order */
            $order = $this->orders->create([
                'customer_id' => (int) $rfq->created_by,
                'project_id' => $rfq->project_id,
                'supplier_id' => $quotation->supplier_id,
                'quotation_id' => $quotation->id,
                'order_number' => $orderNumber,
                'status' => OrderStatus::Pending->value,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'total_amount' => round($subtotal, 2),
            ]);

            foreach ($rows as $row) {
                $order->items()->create($row);
            }

            Log::info('order.from_quotation', [
                'action' => 'order.from_quotation',
                'order_id' => $order->id,
                'quotation_id' => $quotation->id,
                'user_id' => $user->id,
            ]);

            return $order->fresh(['items.product']) ?? $order;
        });
    }

    public function confirm(User $user, Order $order): Order
    {
        return DB::transaction(function () use ($user, $order) {
            /** @var Order $locked */
            $locked = $this->orders->lockOrderForUpdate((int) $order->getKey());
            if ($locked->status !== OrderStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => ['لا يمكن تأكيد الطلب في هذه الحالة'],
                ]);
            }

            $locked->load('items.product');
            $this->inventory->reserveForOrder($user, $locked);

            $locked->status = OrderStatus::Confirmed;
            $locked->confirmed_at = now();
            $locked->save();

            Log::info('order.confirmed', [
                'action' => 'order.confirmed',
                'order_id' => $locked->id,
                'user_id' => $user->id,
            ]);

            return $locked->fresh(['items.product']) ?? $locked;
        });
    }

    public function cancel(User $user, Order $order): Order
    {
        return DB::transaction(function () use ($user, $order) {
            /** @var Order $locked */
            $locked = $this->orders->lockOrderForUpdate((int) $order->getKey());
            if (in_array($locked->status, [OrderStatus::Cancelled, OrderStatus::Completed, OrderStatus::Refunded], true)) {
                throw ValidationException::withMessages([
                    'status' => ['لا يمكن إلغاء الطلب في هذه الحالة'],
                ]);
            }

            if ($locked->confirmed_at !== null) {
                $locked->load('items.product');
                $this->inventory->releaseReservationsForOrder($user, $locked);
            }

            $locked->status = OrderStatus::Cancelled;
            $locked->save();

            Log::info('order.cancelled', [
                'action' => 'order.cancelled',
                'order_id' => $locked->id,
                'user_id' => $user->id,
            ]);

            return $locked->fresh(['items.product']) ?? $locked;
        });
    }

    public function transitionStatus(User $user, Order $order, OrderStatus $to): Order
    {
        return DB::transaction(function () use ($user, $order, $to) {
            /** @var Order $locked */
            $locked = $this->orders->lockOrderForUpdate((int) $order->getKey());
            $from = $locked->status;
            $this->assertTransition($from, $to);

            $locked->status = $to;
            if ($to === OrderStatus::Shipped) {
                $locked->shipped_at = now();
            }
            if ($to === OrderStatus::Delivered) {
                $locked->delivered_at = now();
            }
            $locked->save();

            Log::info('order.status_transition', [
                'action' => 'order.status_transition',
                'order_id' => $locked->id,
                'user_id' => $user->id,
                'from_status' => $from->value,
                'to_status' => $to->value,
            ]);

            return $locked->fresh(['items.product']) ?? $locked;
        });
    }

    private function assertTransition(OrderStatus $from, OrderStatus $to): void
    {
        $allowed = match ($from) {
            OrderStatus::Pending => [OrderStatus::Confirmed, OrderStatus::Cancelled],
            OrderStatus::Confirmed => [OrderStatus::Processing, OrderStatus::Cancelled],
            OrderStatus::Processing => [OrderStatus::Shipped, OrderStatus::Cancelled],
            OrderStatus::Shipped => [OrderStatus::Delivered],
            OrderStatus::Delivered => [OrderStatus::Completed],
            default => [],
        };

        if (! in_array($to, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => ['انتقال الحالة غير مسموح'],
            ]);
        }
    }

    private function allocateOrderNumber(): string
    {
        $ymd = now()->format('Ymd');
        $prefix = 'BNY-'.$ymd.'-';
        $next = $this->orders->nextDisplaySequenceForDate($ymd);
        $suffix = str_pad((string) $next, 4, '0', STR_PAD_LEFT);

        return $prefix.$suffix;
    }
}
