<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Exceptions\RbacException;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    private RoleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->service = app(RoleService::class);
    }

    public function test_get_all_roles_returns_with_user_counts(): void
    {
        $roles = $this->service->getAllRoles();

        $this->assertGreaterThanOrEqual(5, $roles->count());
        $this->assertTrue($roles->every(fn ($r) => isset($r->users_count)));
    }

    public function test_get_role_permissions_returns_correct_permissions(): void
    {
        $role = Role::where('name', 'customer')->first();
        $permissions = $this->service->getRolePermissions($role);

        $this->assertGreaterThan(0, $permissions->count());
        $this->assertTrue($permissions->contains('name', 'project.view'));
    }

    public function test_get_user_permissions_returns_permissions_for_role(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $permissions = $this->service->getUserPermissions($user);

        $this->assertIsArray($permissions);
        $this->assertContains('project.view', $permissions);
        $this->assertNotContains('product.create', $permissions);
    }

    public function test_get_user_permissions_admin_gets_all(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $permissions = $this->service->getUserPermissions($user);

        $allPermissions = Permission::pluck('name')->toArray();
        $this->assertCount(count($allPermissions), $permissions);
    }

    public function test_get_user_permissions_caches_result(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->service->getUserPermissions($user);
        $this->assertTrue(Cache::has("user:{$user->id}:permissions"));
    }

    public function test_assign_role_updates_user_and_pivot(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        $result = $this->service->assignRole($user, 'contractor', $admin);

        $this->assertEquals('contractor', $result->role->value);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'contractor']);
        $this->assertDatabaseHas('role_user', ['user_id' => $user->id]);
    }

    public function test_assign_role_revokes_tokens(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $user->createToken('test_token');
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->service->assignRole($user, 'contractor');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_assign_role_clears_permission_cache(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Cache::put("user:{$user->id}:permissions", ['project.view']);

        $this->service->assignRole($user, 'contractor');

        $this->assertFalse(Cache::has("user:{$user->id}:permissions"));
    }

    public function test_remove_role_resets_to_customer(): void
    {
        $user = User::factory()->create(['role' => 'contractor']);

        $result = $this->service->removeRole($user);

        $this->assertEquals(UserRole::Customer->value, $result->role->value);
    }

    public function test_cannot_remove_last_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::where('role', 'admin')->where('id', '!=', $admin->id)->delete();

        $this->expectException(RbacException::class);
        $this->service->assignRole($admin, 'customer');
    }

    public function test_clear_permission_cache(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Cache::put("user:{$user->id}:permissions", ['test']);

        $this->service->clearPermissionCache($user);

        $this->assertFalse(Cache::has("user:{$user->id}:permissions"));
    }
}
