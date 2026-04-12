<?php

namespace App\Services;

use App\Models\WorkflowConfiguration;
use App\Repositories\WorkflowConfigurationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkflowDefinitionService
{
    public function __construct(private WorkflowConfigurationRepository $workflowConfigurations)
    {
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->workflowConfigurations->paginateAll($perPage);
    }

    public function find(int $id): WorkflowConfiguration
    {
        /** @var WorkflowConfiguration */
        return $this->workflowConfigurations->findByIdOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): WorkflowConfiguration
    {
        /** @var WorkflowConfiguration */
        return $this->workflowConfigurations->create($data);
    }
}
