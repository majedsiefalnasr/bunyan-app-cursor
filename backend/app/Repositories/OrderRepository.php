<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function __construct(
        private readonly Order $model,
    ) {}

    public function findById(int $id): ?Order
    {
        return $this->model->with(['customer', 'project', 'items.product', 'transactions'])->find($id);
    }

    public function findByIdOrFail(int $id): Order
    {
        return $this->model->with(['customer', 'project', 'items.product', 'transactions'])->findOrFail($id);
    }

    public function allByCustomer(int $customerId, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->byCustomer($customerId)
            ->with(['project', 'items.product'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function allByProject(int $projectId, array $filters = []): Collection
    {
        return $this->model
            ->byProject($projectId)
            ->with(['customer', 'items.product'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }

    public function allPending(): Collection
    {
        return $this->model
            ->pending()
            ->with(['customer', 'items.product'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh(['customer', 'items.product']);
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    public function restore(int $orderId): bool
    {
        return $this->model->withTrashed()->findOrFail($orderId)->restore();
    }
}
