<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_relationship_methods_return_expected_relation_types(): void
    {
        $role = new Role;
        $permission = new Permission;

        $this->assertInstanceOf(BelongsToMany::class, $role->permissions());
        $this->assertInstanceOf(BelongsToMany::class, $role->users());
        $this->assertInstanceOf(BelongsToMany::class, $permission->roles());
    }

    public function test_scope_ordered_is_available_on_base_model_and_trait_users(): void
    {
        $roleQuery = Role::query()->ordered('created_at', 'asc');
        $this->assertNotEmpty($roleQuery->getQuery()->orders);
        $this->assertSame('created_at', $roleQuery->getQuery()->orders[0]['column']);
        $this->assertSame('asc', $roleQuery->getQuery()->orders[0]['direction']);

        $userQuery = User::query()->ordered('created_at', 'desc');
        $this->assertNotEmpty($userQuery->getQuery()->orders);
        $this->assertSame('desc', $userQuery->getQuery()->orders[0]['direction']);
    }
}
