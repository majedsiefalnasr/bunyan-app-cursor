<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository
{
    public function __construct(
        private readonly Transaction $model,
    ) {}

    public function findById(int $id): ?Transaction
    {
        return $this->model->with(['user', 'project', 'order'])->find($id);
    }

    public function findByIdOrFail(int $id): Transaction
    {
        return $this->model->with(['user', 'project', 'order'])->findOrFail($id);
    }

    public function allByUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->byUser($userId)
            ->with(['project', 'order'])
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->byType($type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function allByProject(int $projectId, array $filters = []): Collection
    {
        return $this->model
            ->where('project_id', $projectId)
            ->with(['user', 'order'])
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->byType($type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }

    public function allCompleted(array $filters = []): Collection
    {
        return $this->model
            ->completed()
            ->with(['user', 'project'])
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->byType($type))
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $data): Transaction
    {
        return $this->model->create($data);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);
        return $transaction->fresh(['user', 'project']);
    }
}
