<?php

namespace App\Repositories;

use App\Models\Phase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PhaseRepository
{
    public function __construct(
        private readonly Phase $model,
    ) {}

    public function findById(int $id): ?Phase
    {
        return $this->model->with(['project', 'tasks', 'reports'])->find($id);
    }

    public function findByIdOrFail(int $id): Phase
    {
        return $this->model->with(['project', 'tasks', 'reports'])->findOrFail($id);
    }

    public function allByProject(int $projectId, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->byProject($projectId)
            ->with(['tasks', 'reports'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function allActiveByProject(int $projectId): Collection
    {
        return $this->model
            ->byProject($projectId)
            ->active()
            ->with(['tasks'])
            ->get();
    }

    public function create(array $data): Phase
    {
        return $this->model->create($data);
    }

    public function update(Phase $phase, array $data): Phase
    {
        $phase->update($data);
        return $phase->fresh(['project', 'tasks']);
    }

    public function delete(Phase $phase): bool
    {
        return $phase->delete();
    }

    public function restore(int $phaseId): bool
    {
        return $this->model->withTrashed()->findOrFail($phaseId)->restore();
    }
}
