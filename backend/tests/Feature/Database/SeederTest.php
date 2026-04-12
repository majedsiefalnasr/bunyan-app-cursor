<?php

namespace Tests\Feature\Database;

use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_without_errors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(true);
    }

    public function test_default_categories_are_seeded(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(4, Category::count());
        $this->assertDatabaseHas('categories', ['slug' => 'building-materials']);
        $this->assertDatabaseHas('categories', ['slug' => 'electrical']);
    }

    public function test_five_roles_are_seeded(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, Role::count());
        $this->assertDatabaseHas('roles', ['name' => 'customer']);
        $this->assertDatabaseHas('roles', ['name' => 'contractor']);
        $this->assertDatabaseHas('roles', ['name' => 'supervising_architect']);
        $this->assertDatabaseHas('roles', ['name' => 'field_engineer']);
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
    }

    public function test_permissions_are_seeded(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, Permission::count());
        $this->assertDatabaseHas('permissions', ['name' => 'project.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'project.create']);
    }

    public function test_admin_has_all_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = Role::where('name', 'admin')->first();
        $allPermissions = Permission::count();

        $this->assertSame($allPermissions, $admin->permissions()->count());
    }

    public function test_customer_has_correct_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $customer = Role::where('name', 'customer')->first();
        $permissionNames = $customer->permissions()->pluck('name')->toArray();

        $this->assertContains('project.view', $permissionNames);
        $this->assertContains('project.create', $permissionNames);
        $this->assertContains('order.view', $permissionNames);
        $this->assertNotContains('project.delete', $permissionNames);
    }

    public function test_field_engineer_has_limited_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $fieldEngineer = Role::where('name', 'field_engineer')->first();
        $permissionNames = $fieldEngineer->permissions()->pluck('name')->toArray();

        $this->assertContains('task.view', $permissionNames);
        $this->assertContains('report.create', $permissionNames);
        $this->assertNotContains('project.create', $permissionNames);
        $this->assertNotContains('project.delete', $permissionNames);
    }
}
