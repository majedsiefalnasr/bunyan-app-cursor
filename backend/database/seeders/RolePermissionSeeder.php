<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    private const ROLE_PERMISSIONS = [
        'customer' => [
            'project.view',
            'project.create',
            'order.view',
            'order.create',
            'transaction.view',
            'report.view',
        ],
        'contractor' => [
            'project.view',
            'project.update',
            'phase.view',
            'phase.create',
            'phase.update',
            'task.view',
            'task.create',
            'task.update',
            'report.view',
            'report.create',
            'transaction.view',
        ],
        'supervising_architect' => [
            'project.view',
            'project.approve',
            'phase.view',
            'phase.update',
            'task.view',
            'task.update',
            'report.view',
            'report.create',
        ],
        'field_engineer' => [
            'task.view',
            'task.update',
            'report.view',
            'report.create',
            'report.update',
        ],
    ];

    public function run(): void
    {
        $allPermissions = Permission::all()->keyBy('name');
        $roles = Role::all()->keyBy('name');

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissionNames) {
            $role = $roles->get($roleName);
            if ($role === null) {
                continue;
            }

            $permissionIds = collect($permissionNames)
                ->map(fn (string $name) => $allPermissions->get($name)?->id)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->syncWithoutDetaching($permissionIds);
        }

        $adminRole = $roles->get('admin');
        if ($adminRole !== null) {
            $adminRole->permissions()->sync($allPermissions->pluck('id')->all());
        }
    }
}
