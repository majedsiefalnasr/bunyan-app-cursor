<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    protected function model(): string
    {
        return Order::class;
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->with(['customer', 'project', 'items.product', 'items.variant', 'quotation', 'supplierProfile', 'transactions'])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with(['customer', 'project', 'items.product', 'items.variant', 'quotation', 'supplierProfile', 'transactions'])->findOrFail($id);
    }

    public function lockOrderForUpdate(int $id): Order
    {
        /** @var Order */
        return $this->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    /**
     * Next 4-digit suffix for human-readable order numbers on a given calendar day (Asia/Riyadh uses app tz).
     */
    public function nextDisplaySequenceForDate(string $ymd): int
    {
        $prefix = 'BNY-'.$ymd.'-';
        $numbers = $this->newQuery()
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->pluck('order_number');

        $max = 0;
        foreach ($numbers as $orderNumber) {
            if (preg_match('/^BNY-\d{8}-(\d{4})$/', (string) $orderNumber, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $max + 1;
    }

    public function paginateForActor(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $this->newQuery()->with(['project', 'items.product']);

        if ($user->role === UserRole::Admin) {
            // all
        } elseif ($user->role === UserRole::Customer) {
            $query->where('customer_id', $user->id);
        } elseif ($user->role === UserRole::Contractor) {
            $supplierId = $user->supplierProfile?->id;
            if ($supplierId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('supplier_id', $supplierId);
            }
        } else {
            $query->whereRaw('1 = 0');
        }

        if (! empty($filters['status'])) {
            $query->where('status', (string) $filters['status']);
        }

        return $query->orderByDesc('created_at')->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function allByCustomer(int $customerId, array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->byCustomer($customerId)
            ->with(['project', 'items.product'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function allByProject(int $projectId, array $filters = []): Collection
    {
        /** @var Collection<int, Order> */
        return $this->newQuery()
            ->byProject($projectId)
            ->with(['customer', 'items.product'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }

    public function allPending(): Collection
    {
        /** @var Collection<int, Order> */
        return $this->newQuery()
            ->pending()
            ->with(['customer', 'items.product'])
            ->orderByDesc('created_at')
            ->get();
    }
}
