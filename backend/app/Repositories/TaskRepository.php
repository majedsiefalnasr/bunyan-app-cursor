<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function __construct(
        private readonly Task $model,
    ) {
    }

    public function findById(int $id): ?Task
    {
        return $this->model->with(['phase', 'assignee', 'reports'])->find($id);
    }

    public function findByIdOrFail(int $id): Task
    {
        return $this->model->with(['phase', 'assignee', 'reports'])->findOrFail($id);
    }

    public function allByPhase(int $phaseId, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->byPhase($phaseId)
            ->with(['assignee', 'reports'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function allAssignedTo(int $userId, array $filters = []): Collection
    {
        return $this->model
            ->assignedTo($userId)
            ->with(['phase.project', 'reports'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }

    public function allActiveByPhase(int $phaseId): Collection
    {
        return $this->model
            ->byPhase($phaseId)
            ->active()
            ->with(['assignee'])
            ->get();
    }

    public function create(array $data): Task
    {
        return $this->model->create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh(['phase', 'assignee']);
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function restore(int $taskId): bool
    {
        return $this->model->withTrashed()->findOrFail($taskId)->restore();
    }
}
