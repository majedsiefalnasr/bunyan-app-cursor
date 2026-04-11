<?php

namespace Tests\Feature\Database;

use App\Enums\PhaseStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnumCastTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_cast_returns_enum(): void
    {
        $user = User::factory()->create(['role' => 'contractor']);

        $freshUser = User::find($user->id);

        $this->assertInstanceOf(UserRole::class, $freshUser->role);
        $this->assertSame(UserRole::Contractor, $freshUser->role);
        $this->assertSame('contractor', $freshUser->role->value);
        $this->assertSame('المقاول', $freshUser->role->label());
    }

    public function test_project_status_cast_returns_enum(): void
    {
        $customer = User::factory()->create();
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'active',
        ]);

        $freshProject = Project::find($project->id);

        $this->assertInstanceOf(ProjectStatus::class, $freshProject->status);
        $this->assertSame(ProjectStatus::Active, $freshProject->status);
        $this->assertSame('active', $freshProject->status->value);
        $this->assertSame('نشط', $freshProject->status->label());
    }

    public function test_phase_status_cast_returns_enum(): void
    {
        $customer = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $phase = Phase::factory()->create([
            'project_id' => $project->id,
            'status' => 'in_progress',
        ]);

        $freshPhase = Phase::find($phase->id);

        $this->assertInstanceOf(PhaseStatus::class, $freshPhase->status);
        $this->assertSame(PhaseStatus::InProgress, $freshPhase->status);
        $this->assertSame('in_progress', $freshPhase->status->value);
    }

    public function test_task_status_cast_returns_enum(): void
    {
        $customer = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'phase_id' => $phase->id,
            'status' => 'completed',
        ]);

        $freshTask = Task::find($task->id);

        $this->assertInstanceOf(TaskStatus::class, $freshTask->status);
        $this->assertSame(TaskStatus::Completed, $freshTask->status);
    }

    public function test_user_factory_states_set_correct_role(): void
    {
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();

        $this->assertSame(UserRole::Customer, $customer->role);
        $this->assertSame(UserRole::Admin, $admin->role);
    }

    public function test_project_factory_states_set_correct_status(): void
    {
        $customer = User::factory()->create();
        $project = Project::factory()->active()->create(['customer_id' => $customer->id]);

        $freshProject = Project::find($project->id);
        $this->assertSame(ProjectStatus::Active, $freshProject->status);
    }
}
