<?php

namespace App\Repositories;

use App\Models\WorkflowConfiguration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class WorkflowConfigurationRepository extends BaseRepository
{
    protected function model(): string
    {
        return WorkflowConfiguration::class;
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->with(['project', 'approvalRules'])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with(['project', 'approvalRules'])->findOrFail($id);
    }

    public function findGlobal(): ?WorkflowConfiguration
    {
        /** @var WorkflowConfiguration|null */
        return $this->newQuery()->where('is_global', true)->first();
    }

    public function findByProject(int $projectId): ?WorkflowConfiguration
    {
        /** @var WorkflowConfiguration|null */
        return $this->newQuery()
            ->where('project_id', $projectId)
            ->with(['approvalRules'])
            ->first();
    }

    public function allGlobal(): Collection
    {
        /** @var Collection<int, WorkflowConfiguration> */
        return $this->newQuery()
            ->where('is_global', true)
            ->with(['approvalRules'])
            ->get();
    }

    public function allByProject(int $projectId): Collection
    {
        /** @var Collection<int, WorkflowConfiguration> */
        return $this->newQuery()
            ->where('project_id', $projectId)
            ->with(['approvalRules'])
            ->get();
    }
}
