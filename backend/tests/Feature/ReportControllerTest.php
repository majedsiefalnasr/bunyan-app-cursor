<?php

namespace Tests\Feature\Api\V1;

use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_reports_successfully()
    {
        $user = User::factory()->create(['role' => 'field_engineer']);
        $project = Project::factory()->create();
        Report::factory()->count(3)->create(['project_id' => $project->id, 'created_by' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/reports');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    public function test_show_report_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $report = Report::factory()->create(['project_id' => $project->id, 'created_by' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/reports/{$report->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $report->id);
    }

    public function test_create_report_successfully()
    {
        $user = User::factory()->create(['role' => 'field_engineer']);
        $project = Project::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/reports', [
                'project_id' => $project->id,
                'title' => 'Daily Report',
                'content' => 'Foundation work in progress',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Daily Report');
    }

    public function test_create_report_unauthorized()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/reports', [
                'project_id' => $project->id,
                'title' => 'Daily Report',
                'content' => 'Foundation work',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_report_successfully()
    {
        $user = User::factory()->create(['role' => 'field_engineer']);
        $project = Project::factory()->create();
        $report = Report::factory()->create(['project_id' => $project->id, 'created_by' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/v1/reports/{$report->id}", [
                'title' => 'Updated Report',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Report');
    }
}
