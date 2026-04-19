<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Customers
            [
                'name' => 'عميل تجريبي',
                'email' => 'customer@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+966501234567',
                'active' => true,
            ],
            [
                'name' => 'محمد العريان',
                'email' => 'mohammad.alarian@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+966501234501',
                'active' => true,
            ],
            [
                'name' => 'فاطمة السهلي',
                'email' => 'fatima.alsahli@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+966501234502',
                'active' => true,
            ],
            [
                'name' => 'عبدالرحمن الشهري',
                'email' => 'abdulrahman.alshehri@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+966501234503',
                'active' => true,
            ],
            // Contractors
            [
                'name' => 'مقاول تجريبي',
                'email' => 'contractor@example.com',
                'password' => Hash::make('password'),
                'role' => 'contractor',
                'phone' => '+966501234568',
                'active' => true,
            ],
            [
                'name' => 'شركة البناء المتقدمة',
                'email' => 'advanced.construction@example.com',
                'password' => Hash::make('password'),
                'role' => 'contractor',
                'phone' => '+966501234504',
                'active' => true,
            ],
            [
                'name' => 'شركة النور للمقاولات',
                'email' => 'alnoor.contracting@example.com',
                'password' => Hash::make('password'),
                'role' => 'contractor',
                'phone' => '+966501234505',
                'active' => true,
            ],
            // Supervising Architects
            [
                'name' => 'مهندس مشرف تجريبي',
                'email' => 'architect@example.com',
                'password' => Hash::make('password'),
                'role' => 'supervising_architect',
                'phone' => '+966501234569',
                'active' => true,
            ],
            [
                'name' => 'د. علي الرويلي',
                'email' => 'dr.ali.alruwayili@example.com',
                'password' => Hash::make('password'),
                'role' => 'supervising_architect',
                'phone' => '+966501234506',
                'active' => true,
            ],
            [
                'name' => 'أ. سارة المطيري',
                'email' => 'sarah.almutairi@example.com',
                'password' => Hash::make('password'),
                'role' => 'supervising_architect',
                'phone' => '+966501234507',
                'active' => true,
            ],
            // Field Engineers
            [
                'name' => 'مهندس ميداني تجريبي',
                'email' => 'engineer@example.com',
                'password' => Hash::make('password'),
                'role' => 'field_engineer',
                'phone' => '+966501234570',
                'active' => true,
            ],
            [
                'name' => 'خالد العنزي',
                'email' => 'khaled.alanzi@example.com',
                'password' => Hash::make('password'),
                'role' => 'field_engineer',
                'phone' => '+966501234508',
                'active' => true,
            ],
            [
                'name' => 'عمر الزهراني',
                'email' => 'omar.alzahrani@example.com',
                'password' => Hash::make('password'),
                'role' => 'field_engineer',
                'phone' => '+966501234509',
                'active' => true,
            ],
            [
                'name' => 'نور الدين البوسعيدي',
                'email' => 'noor.albusaidi@example.com',
                'password' => Hash::make('password'),
                'role' => 'field_engineer',
                'phone' => '+966501234510',
                'active' => true,
            ],
            // Admin
            [
                'name' => 'المسؤول',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+966501234571',
                'active' => true,
            ],
        ];

        $roles = Role::all()->keyBy('name');

        foreach ($users as $userData) {
            $user = User::firstOrCreate(['email' => $userData['email']], $userData);

            $role = $roles->get($userData['role']);
            if ($role !== null) {
                $user->roles()->syncWithoutDetaching([
                    $role->id => [
                        'assigned_at' => now(),
                        'assigned_by' => null,
                    ],
                ]);
            }
        }
    }
}
