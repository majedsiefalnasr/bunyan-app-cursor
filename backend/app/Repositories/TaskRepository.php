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
        return $this->newQuery()->with(['phase', 'phase.project', 'project', 'assignee', 'reports'])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with(['phase', 'phase.project', 'project', 'assignee', 'reports'])->findOrFail($id);
    }

    public function paginateForProject(int $projectId, array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where('project_id', $projectId)
            ->with(['phase', 'assignee'])
            ->when($filters['phase_id'] ?? null, fn ($q, $phaseId) => $q->where('phase_id', $phaseId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($q, $priority) => $q->where('priority', $priority))
            ->when($filters['assigned_to'] ?? null, fn ($q, $userId) => $q->where('assigned_to', $userId))
            ->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findForProjectOrFail(int $taskId, int $projectId): Model
    {
        return $this->newQuery()
            ->whereKey($taskId)
            ->where('project_id', $projectId)
            ->with(['phase', 'project', 'assignee', 'comments.user'])
            ->firstOrFail();
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
