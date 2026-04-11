<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationRollbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_tables_exist_after_migrate(): void
    {
        $expectedTables = [
            'users', 'roles', 'permissions', 'role_permissions',
            'role_user', 'projects', 'phases', 'tasks', 'reports',
            'orders', 'order_items', 'products', 'transactions',
            'workflow_configurations', 'approval_rules', 'personal_access_tokens',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table '{$table}' does not exist after migration"
            );
        }
    }

    public function test_migration_pretend_runs_without_error(): void
    {
        $exitCode = Artisan::call('migrate', ['--pretend' => true]);

        $this->assertSame(0, $exitCode);
    }
}
