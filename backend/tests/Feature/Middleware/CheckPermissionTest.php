<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CheckPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_bypasses_permission_check(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue(Gate::forUser($admin)->check('project.create'));
        $this->assertTrue(Gate::forUser($admin)->check('user.view'));
        $this->assertTrue(Gate::forUser($admin)->check('role.assign'));
    }

    public function test_customer_has_correct_permissions(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->assertTrue(Gate::forUser($customer)->check('project.view'));
        $this->assertTrue(Gate::forUser($customer)->check('project.create'));
        $this->assertFalse(Gate::forUser($customer)->check('product.create'));
        $this->assertFalse(Gate::forUser($customer)->check('user.view'));
    }

    public function test_contractor_has_correct_permissions(): void
    {
        $contractor = User::factory()->create(['role' => 'contractor']);

        $this->assertTrue(Gate::forUser($contractor)->check('project.view'));
        $this->assertTrue(Gate::forUser($contractor)->check('phase.create'));
        $this->assertTrue(Gate::forUser($contractor)->check('task.create'));
        $this->assertFalse(Gate::forUser($contractor)->check('project.create'));
        $this->assertFalse(Gate::forUser($contractor)->check('order.create'));
    }

    public function test_field_engineer_has_correct_permissions(): void
    {
        $engineer = User::factory()->create(['role' => 'field_engineer']);

        $this->assertTrue(Gate::forUser($engineer)->check('report.create'));
        $this->assertTrue(Gate::forUser($engineer)->check('report.update'));
        $this->assertFalse(Gate::forUser($engineer)->check('project.create'));
        $this->assertFalse(Gate::forUser($engineer)->check('phase.create'));
    }
}
