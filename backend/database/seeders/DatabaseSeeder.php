<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ProjectSeeder::class,
            PhaseSeeder::class,
            TaskSeeder::class,
            ReportSeeder::class,
            TransactionSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
