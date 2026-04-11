<?php

namespace Tests\Feature\Database;

use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_soft_delete_sets_deleted_at(): void
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_user_not_in_default_query_after_soft_delete(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $found = User::find($user->id);

        $this->assertNull($found);
    }

    public function test_user_found_with_trashed(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $found = User::withTrashed()->find($user->id);

        $this->assertNotNull($found);
    }

    public function test_user_restore_clears_deleted_at(): void
    {
        $user = User::factory()->create();
        $user->delete();
        $user->restore();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    public function test_project_soft_delete(): void
    {
        $customer = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $project->delete();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        $this->assertNull(Project::find($project->id));
        $this->assertNotNull(Project::withTrashed()->find($project->id));
    }

    public function test_phase_soft_delete(): void
    {
        $customer = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $phase->delete();

        $this->assertSoftDeleted('phases', ['id' => $phase->id]);
        $this->assertNull(Phase::find($phase->id));
    }

    public function test_task_soft_delete(): void
    {
        $customer = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create(['phase_id' => $phase->id]);

        $task->delete();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
        $this->assertNull(Task::find($task->id));
    }
}
