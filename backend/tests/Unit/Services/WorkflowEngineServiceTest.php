<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Enums\WorkflowApprovalAction;
use App\Enums\WorkflowInstanceStatus;
use App\Models\ApprovalRule;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkflowApproval;
use App\Models\WorkflowConfiguration;
use App\Models\WorkflowInstance;
use App\Services\WorkflowEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkflowEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkflowEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WorkflowEngineService::class);
    }

    public function test_start_for_project_requires_configuration(): void
    {
        $actor = User::factory()->admin()->create();
        $project = Project::factory()->create();

        $this->expectException(ValidationException::class);
        $this->service->startForProject($project, $actor);
    }

    public function test_start_for_project_creates_instance_and_approvals_and_blocks_second_open_instance(): void
    {
        $actor = User::factory()->admin()->create();
        $project = Project::factory()->create();

        $config = WorkflowConfiguration::query()->create([
            'project_id' => null,
            'is_global' => true,
            'name' => 'Global',
            'description' => null,
        ]);

        ApprovalRule::query()->create([
            'workflow_configuration_id' => $config->id,
            'entity_type' => 'project',
            'status_from' => 'draft',
            'status_to' => 'planning',
            'approver_role' => UserRole::Admin->value,
        ]);

        $instance = $this->service->startForProject($project, $actor);

        $this->assertInstanceOf(WorkflowInstance::class, $instance);
        $this->assertSame(WorkflowInstanceStatus::InProgress, $instance->status);
        $this->assertCount(1, $instance->approvals);
        $this->assertSame(WorkflowApprovalAction::Pending, $instance->approvals->first()->action);

        $this->expectException(ValidationException::class);
        $this->service->startForProject($project, $actor);
    }

    public function test_start_for_project_completes_immediately_when_no_rules(): void
    {
        $actor = User::factory()->admin()->create();
        $project = Project::factory()->create();

        WorkflowConfiguration::query()->create([
            'project_id' => null,
            'is_global' => true,
            'name' => 'Global',
            'description' => null,
        ]);

        $instance = $this->service->startForProject($project, $actor);
        $this->assertSame(WorkflowInstanceStatus::Completed, $instance->status);
        $this->assertCount(0, $instance->approvals);
    }

    public function test_approve_marks_terminal_and_completes_when_last_pending(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create();

        $config = WorkflowConfiguration::query()->create([
            'project_id' => null,
            'is_global' => true,
            'name' => 'Global',
            'description' => null,
        ]);

        ApprovalRule::query()->create([
            'workflow_configuration_id' => $config->id,
            'entity_type' => 'project',
            'status_from' => 'draft',
            'status_to' => 'planning',
            'approver_role' => UserRole::Admin->value,
        ]);

        $instance = $this->service->startForProject($project, $admin);
        $approval = $instance->approvals->firstOrFail();

        $updated = $this->service->approve($approval, $admin, 'ok');
        $this->assertSame(WorkflowInstanceStatus::Completed, $updated->status);

        $approval = WorkflowApproval::query()->findOrFail($approval->id);
        $this->assertSame(WorkflowApprovalAction::Approved, $approval->action);
        $this->assertSame($admin->id, $approval->acted_by);
    }

    public function test_reject_marks_instance_rejected_and_blocks_double_handling(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create();

        $config = WorkflowConfiguration::query()->create([
            'project_id' => null,
            'is_global' => true,
            'name' => 'Global',
            'description' => null,
        ]);

        ApprovalRule::query()->create([
            'workflow_configuration_id' => $config->id,
            'entity_type' => 'project',
            'status_from' => 'draft',
            'status_to' => 'planning',
            'approver_role' => UserRole::Admin->value,
        ]);

        $instance = $this->service->startForProject($project, $admin);
        $approval = $instance->approvals->firstOrFail();

        $rejected = $this->service->reject($approval, $admin, 'no');
        $this->assertSame(WorkflowInstanceStatus::Rejected, $rejected->status);

        $this->expectException(ValidationException::class);
        $this->service->approve($approval->fresh(), $admin, null);
    }
}
