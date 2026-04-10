<?php

namespace App\Repositories;

use App\Models\WorkflowConfiguration;
use Illuminate\Database\Eloquent\Collection;

class WorkflowConfigurationRepository
{
    public function __construct(
        private readonly WorkflowConfiguration $model,
    ) {
    }

    public function findById(int $id): ?WorkflowConfiguration
    {
        return $this->model->with(['project', 'approvalRules'])->find($id);
    }

    public function findByIdOrFail(int $id): WorkflowConfiguration
    {
        return $this->model->with(['project', 'approvalRules'])->findOrFail($id);
    }

    public function findGlobal(): ?WorkflowConfiguration
    {
        return $this->model->where('is_global', true)->first();
    }

    public function findByProject(int $projectId): ?WorkflowConfiguration
    {
        return $this->model
            ->where('project_id', $projectId)
            ->with(['approvalRules'])
            ->first();
    }

    public function allGlobal(): Collection
    {
        return $this->model
            ->where('is_global', true)
            ->with(['approvalRules'])
            ->get();
    }

    public function allByProject(int $projectId): Collection
    {
        return $this->model
            ->where('project_id', $projectId)
            ->with(['approvalRules'])
            ->get();
    }

    public function create(array $data): WorkflowConfiguration
    {
        return $this->model->create($data);
    }

    public function update(WorkflowConfiguration $config, array $data): WorkflowConfiguration
    {
        $config->update($data);

        return $config->fresh(['approvalRules']);
    }

    public function delete(WorkflowConfiguration $config): bool
    {
        return $config->delete();
    }
}
