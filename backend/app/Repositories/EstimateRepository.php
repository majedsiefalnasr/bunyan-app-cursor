<?php

namespace App\Repositories;

use App\Models\Estimate;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EstimateRepository
{
    public function paginateForProject(Project $project, int $perPage = 15): LengthAwarePaginator
    {
        return Estimate::query()
            ->where('project_id', $project->id)
            ->with(['creator'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findForProjectOrFail(Project $project, int $estimateId): Estimate
    {
        return Estimate::query()
            ->where('project_id', $project->id)
            ->whereKey($estimateId)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Estimate
    {
        return Estimate::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Estimate $estimate, array $data): Estimate
    {
        $estimate->update($data);

        return $estimate->fresh() ?? $estimate;
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, Estimate>
     */
    public function forProjectWhereIdsIn(Project $project, array $ids)
    {
        return Estimate::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->with('items')
            ->orderBy('id')
            ->get();
    }
}
