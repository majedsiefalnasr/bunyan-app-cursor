<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository extends BaseRepository
{
    protected function model(): string
    {
        return Task::class;
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->with(['phase', 'assignee', 'reports'])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with(['phase', 'assignee', 'reports'])->findOrFail($id);
    }

    public function allByPhase(int $phaseId, array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->byPhase($phaseId)
            ->with(['assignee', 'reports'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function allAssignedTo(int $userId, array $filters = []): Collection
    {
        /** @var Collection<int, Task> */
        return $this->newQuery()
            ->assignedTo($userId)
            ->with(['phase.project', 'reports'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }

    public function allActiveByPhase(int $phaseId): Collection
    {
        /** @var Collection<int, Task> */
        return $this->newQuery()
            ->byPhase($phaseId)
            ->active()
            ->with(['assignee'])
            ->get();
    }
}
