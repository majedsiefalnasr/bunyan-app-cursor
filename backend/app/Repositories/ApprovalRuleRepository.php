<?php

namespace App\Repositories;

use App\Models\ApprovalRule;
use Illuminate\Database\Eloquent\Collection;

class ApprovalRuleRepository
{
    public function __construct(
        private readonly ApprovalRule $model,
    ) {}

    public function findById(int $id): ?ApprovalRule
    {
        return $this->model->with(['workflowConfiguration'])->find($id);
    }

    public function findByIdOrFail(int $id): ApprovalRule
    {
        return $this->model->with(['workflowConfiguration'])->findOrFail($id);
    }

    public function allByWorkflowConfiguration(int $configId): Collection
    {
        return $this->model
            ->where('workflow_configuration_id', $configId)
            ->orderBy('entity_type')
            ->orderBy('status_from')
            ->get();
    }

    public function findRuleFor(int $configId, string $entityType, string $statusFrom, string $statusTo): ?ApprovalRule
    {
        return $this->model
            ->where('workflow_configuration_id', $configId)
            ->where('entity_type', $entityType)
            ->where('status_from', $statusFrom)
            ->where('status_to', $statusTo)
            ->first();
    }

    public function create(array $data): ApprovalRule
    {
        return $this->model->create($data);
    }

    public function update(ApprovalRule $rule, array $data): ApprovalRule
    {
        $rule->update($data);
        return $rule->fresh();
    }

    public function delete(ApprovalRule $rule): bool
    {
        return $rule->delete();
    }
}
