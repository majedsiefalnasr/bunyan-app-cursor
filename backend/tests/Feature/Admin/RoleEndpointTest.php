<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    // ── List Roles ──────────────────────────────────────────────────

    public function test_admin_can_list_roles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/roles');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'label', 'users_count', 'permissions_count'],
                ],
            ]);
    }

    public function test_non_admin_cannot_list_roles(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->getJson('/api/v1/admin/roles');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_list_roles(): void
    {
        $response = $this->getJson('/api/v1/admin/roles');

        $response->assertStatus(401);
    }

    // ── Role Permissions ────────────────────────────────────────────

    public function test_admin_can_view_role_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $role = Role::where('name', 'customer')->first();

        $response = $this->actingAs($admin)->getJson("/api/v1/admin/roles/{$role->id}/permissions");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'role' => ['id', 'name', 'label'],
                    'permissions' => [
                        '*' => ['id', 'name', 'description'],
                    ],
                ],
            ]);
    }

    // ── List Users ──────────────────────────────────────────────────

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/users');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'contractor']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/users?role=contractor');

        $response->assertOk();
    }

    // ── Assign Role ─────────────────────────────────────────────────

    public function test_admin_can_assign_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/users/{$user->id}/role", [
            'role' => 'contractor',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'contractor');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'contractor']);
    }

    public function test_assign_role_validates_role_value(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/users/{$user->id}/role", [
            'role' => 'invalid_role',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_change_last_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::where('role', 'admin')->where('id', '!=', $admin->id)->delete();

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/users/{$admin->id}/role", [
            'role' => 'customer',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'RBAC_LAST_ADMIN');
    }

    public function test_non_admin_cannot_assign_role(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $target = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->postJson("/api/v1/admin/users/{$target->id}/role", [
            'role' => 'contractor',
        ]);

        $response->assertStatus(403);
    }

    // ── Remove Role ─────────────────────────────────────────────────

    public function test_admin_can_remove_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'contractor']);

        $response = $this->actingAs($admin)->deleteJson("/api/v1/admin/users/{$user->id}/role");

        $response->assertOk()
            ->assertJsonPath('data.role', 'customer');
    }
}
