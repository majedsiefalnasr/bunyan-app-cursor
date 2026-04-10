<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'customer',
                'description' => 'Project customer (طالب الخدمة)',
            ],
            [
                'name' => 'contractor',
                'description' => 'Project contractor (المقاول)',
            ],
            [
                'name' => 'supervising_architect',
                'description' => 'Supervising architect (المهندس المشرف)',
            ],
            [
                'name' => 'field_engineer',
                'description' => 'Field engineer (المهندس الميداني)',
            ],
            [
                'name' => 'admin',
                'description' => 'Platform administrator (الإدارة)',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
