<?php

namespace App\Repositories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportRepository extends BaseRepository
{
    protected function model(): string
    {
        return Report::class;
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->with(['task', 'phase', 'project', 'creator'])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with(['task', 'phase', 'project', 'creator'])->findOrFail($id);
    }

    public function allByProject(int $projectId, array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->byProject($projectId)
            ->with(['task', 'phase', 'creator'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function allByPhase(int $phaseId, array $filters = []): Collection
    {
        /** @var Collection<int, Report> */
        return $this->newQuery()
            ->byPhase($phaseId)
            ->with(['task', 'creator'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }

    public function allByTask(int $taskId, array $filters = []): Collection
    {
        /** @var Collection<int, Report> */
        return $this->newQuery()
            ->byTask($taskId)
            ->with(['creator'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->get();
    }
}
