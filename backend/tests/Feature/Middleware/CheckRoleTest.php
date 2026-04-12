<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authorized_role_passes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/roles');

        $response->assertOk();
    }

    public function test_unauthorized_role_returns_403(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->getJson('/api/v1/admin/roles');

        $response->assertStatus(403)
            ->assertJsonPath('error.code', 'RBAC_ROLE_DENIED');
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/admin/roles');

        $response->assertStatus(401);
    }

    public function test_customer_can_create_project(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->postJson('/api/v1/projects', [
            'name' => 'مشروع اختبار',
        ]);

        // Should not be blocked by role middleware (may fail on validation, which is fine)
        $this->assertNotEquals(403, $response->status());
    }

    public function test_field_engineer_cannot_create_project(): void
    {
        $engineer = User::factory()->create(['role' => 'field_engineer']);

        $response = $this->actingAs($engineer)->postJson('/api/v1/projects', [
            'name' => 'مشروع اختبار',
        ]);

        $response->assertStatus(403);
    }
}
