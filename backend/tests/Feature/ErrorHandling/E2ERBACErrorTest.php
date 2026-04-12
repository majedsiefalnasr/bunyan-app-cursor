<?php

namespace Tests\Feature\ErrorHandling;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2ERBACErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_denied_on_contractor_only_action(): void
    {
        $user = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/v1/projects/{$project->id}/phases", [
            'name' => 'Foundation Phase',
            'budget' => 5000,
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'RBAC_ROLE_DENIED');
    }
}
