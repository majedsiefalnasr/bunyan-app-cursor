<?php

namespace App\Repositories;

use App\Enums\WorkflowApprovalAction;
use App\Models\User;
use App\Models\WorkflowApproval;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class WorkflowApprovalRepository extends BaseRepository
{
    protected function model(): string
    {
        return WorkflowApproval::class;
    }

    public function findByIdOrFail(int $id): Model
    {
        /** @var WorkflowApproval */
        return $this->newQuery()
            ->with(['workflowInstance.workflowable', 'workflowInstance.workflowConfiguration'])
            ->findOrFail($id);
    }

    /**
     * @return Collection<int, WorkflowApproval>
     */
    public function pendingForUser(User $user): Collection
    {
        $role = $user->role->value;

        /** @var Collection<int, WorkflowApproval> */
        return $this->newQuery()
            ->where('approver_role', $role)
            ->where('action', WorkflowApprovalAction::Pending)
            ->with(['workflowInstance.workflowable', 'workflowInstance.workflowConfiguration'])
            ->orderByDesc('id')
            ->get();
    }

    public function countPendingForInstance(int $instanceId): int
    {
        return $this->newQuery()
            ->where('workflow_instance_id', $instanceId)
            ->where('action', WorkflowApprovalAction::Pending)
            ->count();
    }
}
