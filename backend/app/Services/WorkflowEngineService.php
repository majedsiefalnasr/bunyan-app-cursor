<?php

namespace App\Services;

use App\Enums\WorkflowApprovalAction;
use App\Enums\WorkflowInstanceStatus;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkflowApproval;
use App\Models\WorkflowInstance;
use App\Repositories\ApprovalRuleRepository;
use App\Repositories\WorkflowApprovalRepository;
use App\Repositories\WorkflowConfigurationRepository;
use App\Repositories\WorkflowInstanceRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WorkflowEngineService
{
    public function __construct(
        private WorkflowConfigurationRepository $workflowConfigurations,
        private WorkflowInstanceRepository $workflowInstances,
        private WorkflowApprovalRepository $workflowApprovals,
        private ApprovalRuleRepository $approvalRules,
    ) {
    }

    public function startForProject(Project $project, User $actor): WorkflowInstance
    {
        $config = $this->workflowConfigurations->findByProject($project->id)
            ?? $this->workflowConfigurations->findGlobal();

        if ($config === null) {
            throw ValidationException::withMessages([
                'workflow' => ['لا يوجد تكوين سير عمل لهذا المشروع'],
            ]);
        }

        if ($this->workflowInstances->hasOpenInstanceForProject($project->id, $config->id)) {
            throw ValidationException::withMessages([
                'workflow' => ['يوجد سير عمل نشط لهذا المشروع'],
            ]);
        }

        return DB::transaction(function () use ($project, $actor, $config) {
            /** @var WorkflowInstance $instance */
            $instance = $this->workflowInstances->create([
                'workflow_configuration_id' => $config->id,
                'workflowable_type' => Project::class,
                'workflowable_id' => $project->id,
                'status' => WorkflowInstanceStatus::InProgress,
            ]);

            $rules = $this->approvalRules->projectRulesFor($config->id);

            foreach ($rules as $rule) {
                $this->workflowApprovals->create([
                    'workflow_instance_id' => $instance->id,
                    'approval_rule_id' => $rule->id,
                    'approver_role' => $rule->approver_role,
                    'action' => WorkflowApprovalAction::Pending,
                ]);
            }

            if ($rules->isEmpty()) {
                $this->workflowInstances->update($instance, [
                    'status' => WorkflowInstanceStatus::Completed,
                ]);
            }

            Log::info('Workflow started', [
                'action' => 'workflow.started',
                'workflow_instance_id' => $instance->id,
                'project_id' => $project->id,
                'user_id' => $actor->id,
                'workflow_configuration_id' => $config->id,
            ]);

            /** @var WorkflowInstance */
            return $instance->fresh(['approvals', 'workflowConfiguration']) ?? $instance;
        });
    }

    /**
     * @return Collection<int, WorkflowApproval>
     */
    public function pendingForUser(User $user): Collection
    {
        return $this->workflowApprovals->pendingForUser($user);
    }

    public function approve(WorkflowApproval $approval, User $actor, ?string $notes): WorkflowInstance
    {
        return $this->resolveApproval($approval, $actor, $notes, WorkflowApprovalAction::Approved);
    }

    public function reject(WorkflowApproval $approval, User $actor, ?string $notes): WorkflowInstance
    {
        return $this->resolveApproval($approval, $actor, $notes, WorkflowApprovalAction::Rejected);
    }

    private function resolveApproval(
        WorkflowApproval $approval,
        User $actor,
        ?string $notes,
        WorkflowApprovalAction $terminal,
    ): WorkflowInstance {
        if ($approval->action !== WorkflowApprovalAction::Pending) {
            throw ValidationException::withMessages([
                'approval' => ['تمت معالجة هذه الموافقة مسبقاً'],
            ]);
        }

        return DB::transaction(function () use ($approval, $actor, $notes, $terminal) {
            $this->workflowApprovals->update($approval, [
                'action' => $terminal,
                'notes' => $notes,
                'acted_by' => $actor->id,
                'acted_at' => now(),
            ]);

            $instance = $approval->workflowInstance()->firstOrFail();

            if ($terminal === WorkflowApprovalAction::Rejected) {
                $this->workflowInstances->update($instance, [
                    'status' => WorkflowInstanceStatus::Rejected,
                ]);

                Log::info('Workflow approval rejected', [
                    'action' => 'workflow.approval_rejected',
                    'workflow_instance_id' => $instance->id,
                    'workflow_approval_id' => $approval->id,
                    'user_id' => $actor->id,
                ]);

                /** @var WorkflowInstance */
                return $instance->fresh(['approvals', 'workflowConfiguration']) ?? $instance;
            }

            $pending = $this->workflowApprovals->countPendingForInstance($instance->id);

            if ($pending === 0) {
                $this->workflowInstances->update($instance, [
                    'status' => WorkflowInstanceStatus::Completed,
                ]);
            }

            Log::info('Workflow approval granted', [
                'action' => 'workflow.approval_approved',
                'workflow_instance_id' => $instance->id,
                'workflow_approval_id' => $approval->id,
                'user_id' => $actor->id,
            ]);

            /** @var WorkflowInstance */
            return $instance->fresh(['approvals', 'workflowConfiguration']) ?? $instance;
        });
    }
}
