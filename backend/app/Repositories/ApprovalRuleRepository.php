<?php

namespace App\Repositories;

use App\Models\ApprovalRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ApprovalRuleRepository extends BaseRepository
{
    protected function model(): string
    {
        return ApprovalRule::class;
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->with(['workflowConfiguration'])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with(['workflowConfiguration'])->findOrFail($id);
    }

    public function allByWorkflowConfiguration(int $configId): Collection
    {
        /** @var Collection<int, ApprovalRule> */
        return $this->newQuery()
            ->where('workflow_configuration_id', $configId)
            ->orderBy('entity_type')
            ->orderBy('status_from')
            ->get();
    }

    public function findRuleFor(int $configId, string $entityType, string $statusFrom, string $statusTo): ?ApprovalRule
    {
        /** @var ApprovalRule|null */
        return $this->newQuery()
            ->where('workflow_configuration_id', $configId)
            ->where('entity_type', $entityType)
            ->where('status_from', $statusFrom)
            ->where('status_to', $statusTo)
            ->first();
    }

    /**
     * @return Collection<int, ApprovalRule>
     */
    public function projectRulesFor(int $configId): Collection
    {
        /** @var Collection<int, ApprovalRule> */
        return $this->newQuery()
            ->where('workflow_configuration_id', $configId)
            ->where('entity_type', 'project')
            ->orderBy('id')
            ->get();
    }
}
