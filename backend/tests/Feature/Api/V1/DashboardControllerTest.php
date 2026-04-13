<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ActivityLogAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->getJson('/api/v1/dashboard')->assertStatus(401);
        $this->getJson('/api/v1/dashboard/metrics')->assertStatus(401);
        $this->getJson('/api/v1/dashboard/recent-activity')->assertStatus(401);
    }

    public function test_customer_dashboard_ok(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'customer')
            ->assertJsonStructure(['data' => ['kpis']]);
    }

    public function test_contractor_dashboard_ok(): void
    {
        $user = User::factory()->contractor()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/dashboard/metrics')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.metrics.orders', 0);
    }

    public function test_supervising_architect_dashboard_ok(): void
    {
        $user = User::factory()->supervisingArchitect()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.role', 'supervising_architect');
    }

    public function test_field_engineer_dashboard_ok(): void
    {
        $user = User::factory()->fieldEngineer()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.role', 'field_engineer');
    }

    public function test_admin_dashboard_ok(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.role', 'admin')
            ->assertJsonPath('data.kpis.users', 1);
    }

    public function test_non_admin_recent_activity_scoped_to_actor(): void
    {
        $alice = User::factory()->customer()->create();
        $bob = User::factory()->customer()->create();

        ActivityLog::query()->create([
            'user_id' => $alice->id,
            'action' => ActivityLogAction::Created,
            'subject_type' => User::class,
            'subject_id' => $alice->id,
            'properties_json' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now()->subMinute(),
        ]);

        ActivityLog::query()->create([
            'user_id' => $bob->id,
            'action' => ActivityLogAction::Updated,
            'subject_type' => User::class,
            'subject_id' => $bob->id,
            'properties_json' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($alice)->getJson('/api/v1/dashboard/recent-activity?per_page=10');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);
        $this->assertSame($alice->id, $data['data'][0]['user_id']);
    }

    public function test_admin_sees_all_recent_activity(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->customer()->create();

        ActivityLog::query()->create([
            'user_id' => $other->id,
            'action' => ActivityLogAction::Viewed,
            'subject_type' => User::class,
            'subject_id' => $other->id,
            'properties_json' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/dashboard/recent-activity');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data['data']));
    }

    public function test_recent_activity_per_page_validation(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/dashboard/recent-activity?per_page=3')
            ->assertStatus(422);
    }
}
