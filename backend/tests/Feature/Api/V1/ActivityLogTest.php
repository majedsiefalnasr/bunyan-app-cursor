<?php

namespace Tests\Feature\Api\V1;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_activity_logs(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/activity-log');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_non_admin_cannot_list_admin_activity_logs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/admin/activity-log');

        $response->assertStatus(403);
    }

    public function test_project_stakeholder_can_view_project_activity(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $owner->id]);

        ActivityLog::query()->create([
            'user_id' => $owner->id,
            'action' => 'created',
            'subject_type' => $project->getMorphClass(),
            'subject_id' => $project->id,
            'properties_json' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($owner)->getJson("/api/v1/projects/{$project->id}/activity");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_stranger_cannot_view_project_activity(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $owner->id]);

        $response = $this->actingAs($stranger)->getJson("/api/v1/projects/{$project->id}/activity");

        $response->assertStatus(403);
    }

    public function test_project_update_writes_activity_log(): void
    {
        $customer = User::factory()->create();
        $contractor = User::factory()->contractor()->create();
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
            'name' => 'Before',
        ]);

        $this->actingAs($contractor)->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'After',
            'location' => $project->location ?? 'Riyadh',
        ])->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'subject_id' => $project->id,
            'action' => 'updated',
        ]);
    }
}
