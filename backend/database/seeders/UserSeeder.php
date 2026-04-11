<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'عميل تجريبي',
                'email' => 'customer@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+966501234567',
                'active' => true,
            ],
            [
                'name' => 'مقاول تجريبي',
                'email' => 'contractor@example.com',
                'password' => Hash::make('password'),
                'role' => 'contractor',
                'phone' => '+966501234568',
                'active' => true,
            ],
            [
                'name' => 'مهندس مشرف تجريبي',
                'email' => 'architect@example.com',
                'password' => Hash::make('password'),
                'role' => 'supervising_architect',
                'phone' => '+966501234569',
                'active' => true,
            ],
            [
                'name' => 'مهندس ميداني تجريبي',
                'email' => 'engineer@example.com',
                'password' => Hash::make('password'),
                'role' => 'field_engineer',
                'phone' => '+966501234570',
                'active' => true,
            ],
            [
                'name' => 'المسؤول',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+966501234571',
                'active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }
    }
}
