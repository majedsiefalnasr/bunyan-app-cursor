<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Project permissions
            ['name' => 'project.view', 'description' => 'View projects'],
            ['name' => 'project.create', 'description' => 'Create projects'],
            ['name' => 'project.update', 'description' => 'Update projects'],
            ['name' => 'project.delete', 'description' => 'Delete projects'],
            ['name' => 'project.approve', 'description' => 'Approve projects'],

            // Phase permissions
            ['name' => 'phase.view', 'description' => 'View phases'],
            ['name' => 'phase.create', 'description' => 'Create phases'],
            ['name' => 'phase.update', 'description' => 'Update phases'],
            ['name' => 'phase.delete', 'description' => 'Delete phases'],

            // Task permissions
            ['name' => 'task.view', 'description' => 'View tasks'],
            ['name' => 'task.create', 'description' => 'Create tasks'],
            ['name' => 'task.update', 'description' => 'Update tasks'],
            ['name' => 'task.delete', 'description' => 'Delete tasks'],

            // Report permissions
            ['name' => 'report.view', 'description' => 'View reports'],
            ['name' => 'report.create', 'description' => 'Create reports'],
            ['name' => 'report.update', 'description' => 'Update reports'],
            ['name' => 'report.delete', 'description' => 'Delete reports'],

            // Transaction permissions
            ['name' => 'transaction.view', 'description' => 'View transactions'],
            ['name' => 'transaction.create', 'description' => 'Create transactions'],
            ['name' => 'transaction.update', 'description' => 'Update transactions'],

            // Product permissions
            ['name' => 'product.view', 'description' => 'View products'],
            ['name' => 'product.create', 'description' => 'Create products'],
            ['name' => 'product.update', 'description' => 'Update products'],

            // Order permissions
            ['name' => 'order.view', 'description' => 'View orders'],
            ['name' => 'order.create', 'description' => 'Create orders'],
            ['name' => 'order.update', 'description' => 'Update orders'],
            ['name' => 'order.delete', 'description' => 'Delete orders'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
