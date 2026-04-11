<?php

namespace App\Repositories;

use App\Models\Order;
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
        return $this->newQuery()->with(['customer', 'project', 'items.product', 'transactions'])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with(['customer', 'project', 'items.product', 'transactions'])->findOrFail($id);
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
