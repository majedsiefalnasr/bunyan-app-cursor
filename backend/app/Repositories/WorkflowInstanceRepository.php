<?php

namespace App\Repositories;

use App\Enums\WorkflowInstanceStatus;
use App\Models\Project;
use App\Models\WorkflowInstance;
use Illuminate\Database\Eloquent\Model;

class WorkflowInstanceRepository extends BaseRepository
{
    protected function model(): string
    {
        return WorkflowInstance::class;
    }

    public function findByIdOrFail(int $id): Model
    {
        /** @var WorkflowInstance */
        return $this->newQuery()
            ->with(['workflowConfiguration', 'workflowable', 'approvals'])
            ->findOrFail($id);
    }

    public function hasOpenInstanceForProject(int $projectId, int $configurationId): bool
    {
        return $this->newQuery()
            ->where('workflow_configuration_id', $configurationId)
            ->where('workflowable_type', Project::class)
            ->where('workflowable_id', $projectId)
            ->where('status', WorkflowInstanceStatus::InProgress)
            ->exists();
    }

    public function create(array $data): Model
    {
        /** @var WorkflowInstance */
        return parent::create($data);
    }
}
