<?php

namespace App\Repositories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportRepository
{
    public function __construct(
        private readonly Report $model,
    ) {
    }

    public function findById(int $id): ?Report
    {
        return $this->model->with(['task', 'phase', 'project', 'creator'])->find($id);
    }

    public function findByIdOrFail(int $id): Report
    {
        return $this->model->with(['task', 'phase', 'project', 'creator'])->findOrFail($id);
    }

    public function allByProject(int $projectId, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->byProject($projectId)
            ->with(['task', 'phase', 'creator'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function allByPhase(int $phaseId, array $filters = []): Collection
    {
        return $this->model
            ->byPhase($phaseId)
            ->with(['task', 'creator'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }

    public function allByTask(int $taskId, array $filters = []): Collection
    {
        return $this->model
            ->byTask($taskId)
            ->with(['creator'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $data): Report
    {
        return $this->model->create($data);
    }

    public function update(Report $report, array $data): Report
    {
        $report->update($data);

        return $report->fresh(['task', 'phase', 'creator']);
    }

    public function delete(Report $report): bool
    {
        return $report->delete();
    }

    public function restore(int $reportId): bool
    {
        return $this->model->withTrashed()->findOrFail($reportId)->restore();
    }
}
