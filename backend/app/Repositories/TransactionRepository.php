<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository extends BaseRepository
{
    protected function model(): string
    {
        return Transaction::class;
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->with(['user', 'project', 'order'])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with(['user', 'project', 'order'])->findOrFail($id);
    }

    public function allByUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->byUser($userId)
            ->with(['project', 'order'])
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->byType($type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function allByProject(int $projectId, array $filters = []): Collection
    {
        /** @var Collection<int, Transaction> */
        return $this->newQuery()
            ->where('project_id', $projectId)
            ->with(['user', 'order'])
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->byType($type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }

    public function allCompleted(array $filters = []): Collection
    {
        /** @var Collection<int, Transaction> */
        return $this->newQuery()
            ->completed()
            ->with(['user', 'project'])
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->byType($type))
            ->orderByDesc('created_at')
            ->get();
    }
}
