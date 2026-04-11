<?php

namespace App\Repositories;

use App\Models\Phase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class PhaseRepository extends BaseRepository
{
    protected function model(): string
    {
        return Phase::class;
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->with(['project', 'tasks', 'reports'])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with(['project', 'tasks', 'reports'])->findOrFail($id);
    }

    public function allByProject(int $projectId, array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->byProject($projectId)
            ->with(['tasks', 'reports'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function allActiveByProject(int $projectId): Collection
    {
        /** @var Collection<int, Phase> */
        return $this->newQuery()
            ->byProject($projectId)
            ->active()
            ->with(['tasks'])
            ->get();
    }
}
