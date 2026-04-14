<?php

namespace Tests\Unit\Models;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_active_filters_in_progress_projects(): void
    {
        $inProgress = Project::factory()->inProgress()->create();
        $draft = Project::factory()->draft()->create();

        $ids = Project::query()->active()->pluck('id')->all();

        $this->assertContains($inProgress->id, $ids);
        $this->assertNotContains($draft->id, $ids);
    }

    public function test_scope_by_status_filters_projects(): void
    {
        $planning = Project::factory()->planning()->create();
        $completed = Project::factory()->completed()->create();

        $ids = Project::query()->byStatus(ProjectStatus::Planning->value)->pluck('id')->all();

        $this->assertSame([$planning->id], $ids);
        $this->assertNotContains($completed->id, $ids);
    }

    public function test_scope_for_user_filters_by_role_including_field_engineer_reports(): void
    {
        $customer = User::factory()->customer()->create();
        $contractor = User::factory()->contractor()->create();
        $architect = User::factory()->supervisingArchitect()->create();
        $fieldEngineer = User::factory()->fieldEngineer()->create();
        $admin = User::factory()->admin()->create();

        $customerProject = Project::factory()->create(['customer_id' => $customer->id]);
        $contractorProject = Project::factory()->create(['contractor_id' => $contractor->id]);
        $architectProject = Project::factory()->create(['supervising_architect_id' => $architect->id]);
        $fieldEngineerProject = Project::factory()->create();

        Report::factory()->create([
            'project_id' => $fieldEngineerProject->id,
            'created_by' => $fieldEngineer->id,
        ]);

        $customerIds = Project::query()->forUser($customer)->pluck('id')->all();
        $this->assertSame([$customerProject->id], $customerIds);

        $contractorIds = Project::query()->forUser($contractor)->pluck('id')->all();
        $this->assertSame([$contractorProject->id], $contractorIds);

        $architectIds = Project::query()->forUser($architect)->pluck('id')->all();
        $this->assertSame([$architectProject->id], $architectIds);

        $feIds = Project::query()->forUser($fieldEngineer)->pluck('id')->all();
        $this->assertSame([$fieldEngineerProject->id], $feIds);

        $adminIds = Project::query()->forUser($admin)->pluck('id')->all();
        $this->assertContains($customerProject->id, $adminIds);
        $this->assertContains($contractorProject->id, $adminIds);
        $this->assertContains($architectProject->id, $adminIds);
        $this->assertContains($fieldEngineerProject->id, $adminIds);
        $this->assertCount(4, $adminIds);
        $this->assertSame(UserRole::Admin, $admin->role);
    }
}
