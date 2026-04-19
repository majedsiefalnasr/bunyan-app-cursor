<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Get users
        $customer1 = User::where('email', 'customer@example.com')->first();
        $customer2 = User::where('email', 'mohammad.alarian@example.com')->first();
        $customer3 = User::where('email', 'fatima.alsahli@example.com')->first();

        $contractor1 = User::where('email', 'contractor@example.com')->first();
        $contractor2 = User::where('email', 'advanced.construction@example.com')->first();

        // Get projects
        $villaProject = Project::where('name', 'مشروع إعادة تطوير الفيلا')->first();
        $complexProject = Project::where('name', 'مشروع العمارة السكنية')->first();
        $schoolProject = Project::where('name', 'مشروع المدرسة الخاصة')->first();

        $transactions = [
            // Villa project customer payments
            [
                'project_id' => $villaProject?->id,
                'user_id' => $customer1?->id,
                'type' => 'payment',
                'status' => 'completed',
                'amount' => 100000.00,
                'description' => 'دفعة أولى - مشروع الفيلا',
                'payment_method' => 'bank_transfer',
                'reference' => 'TXN-'.now()->subMonths(2)->format('YmdHis'),
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ],
            [
                'project_id' => $villaProject?->id,
                'user_id' => $customer1?->id,
                'type' => 'payment',
                'status' => 'completed',
                'amount' => 100000.00,
                'description' => 'دفعة ثانية - مشروع الفيلا',
                'payment_method' => 'bank_transfer',
                'reference' => 'TXN-'.now()->subMonths(1)->format('YmdHis'),
                'created_at' => now()->subMonths(1),
                'updated_at' => now()->subMonths(1),
            ],
            [
                'project_id' => $villaProject?->id,
                'user_id' => $customer1?->id,
                'type' => 'payment',
                'status' => 'pending',
                'amount' => 100000.00,
                'description' => 'دفعة ثالثة - مشروع الفيلا',
                'payment_method' => 'bank_transfer',
                'reference' => 'TXN-'.now()->format('YmdHis'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Contractor withdrawal/payment
            [
                'project_id' => $villaProject?->id,
                'user_id' => $contractor1?->id,
                'type' => 'withdrawal',
                'status' => 'completed',
                'amount' => 80000.00,
                'description' => 'سحب أرباح من مشروع الفيلا',
                'payment_method' => 'bank_transfer',
                'reference' => 'WTH-'.now()->subMonths(1)->format('YmdHis'),
                'created_at' => now()->subMonths(1),
                'updated_at' => now()->subMonths(1),
            ],
            [
                'project_id' => $villaProject?->id,
                'user_id' => $contractor1?->id,
                'type' => 'withdrawal',
                'status' => 'completed',
                'amount' => 60000.00,
                'description' => 'سحب أرباح من مشروع الفيلا',
                'payment_method' => 'bank_transfer',
                'reference' => 'WTH-'.now()->subDays(15)->format('YmdHis'),
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ],

            // Complex project transactions
            [
                'project_id' => $complexProject?->id,
                'user_id' => $customer2?->id,
                'type' => 'payment',
                'status' => 'completed',
                'amount' => 400000.00,
                'description' => 'دفعة أولى - مشروع العمارة السكنية',
                'payment_method' => 'bank_transfer',
                'reference' => 'TXN-'.now()->subMonths(6)->format('YmdHis'),
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subMonths(6),
            ],
            [
                'project_id' => $complexProject?->id,
                'user_id' => $customer2?->id,
                'type' => 'payment',
                'status' => 'completed',
                'amount' => 400000.00,
                'description' => 'دفعة ثانية - مشروع العمارة السكنية',
                'payment_method' => 'bank_transfer',
                'reference' => 'TXN-'.now()->subMonths(4)->format('YmdHis'),
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subMonths(4),
            ],
            [
                'project_id' => $complexProject?->id,
                'user_id' => $customer2?->id,
                'type' => 'payment',
                'status' => 'completed',
                'amount' => 400000.00,
                'description' => 'دفعة ثالثة - مشروع العمارة السكنية',
                'payment_method' => 'bank_transfer',
                'reference' => 'TXN-'.now()->subMonths(2)->format('YmdHis'),
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ],
            [
                'project_id' => $complexProject?->id,
                'user_id' => $contractor2?->id,
                'type' => 'withdrawal',
                'status' => 'completed',
                'amount' => 300000.00,
                'description' => 'سحب أرباح من مشروع العمارة السكنية',
                'payment_method' => 'bank_transfer',
                'reference' => 'WTH-'.now()->subMonths(3)->format('YmdHis'),
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ],

            // School project - completed transactions
            [
                'project_id' => $schoolProject?->id,
                'user_id' => $customer1?->id,
                'type' => 'payment',
                'status' => 'completed',
                'amount' => 500000.00,
                'description' => 'دفعة أولى - مشروع المدرسة',
                'payment_method' => 'bank_transfer',
                'reference' => 'TXN-'.now()->subYear()->addMonths(1)->format('YmdHis'),
                'created_at' => now()->subYear()->addMonths(1),
                'updated_at' => now()->subYear()->addMonths(1),
            ],
            [
                'project_id' => $schoolProject?->id,
                'user_id' => $customer1?->id,
                'type' => 'payment',
                'status' => 'completed',
                'amount' => 500000.00,
                'description' => 'دفعة ثانية - مشروع المدرسة',
                'payment_method' => 'bank_transfer',
                'reference' => 'TXN-'.now()->subYear()->addMonths(7)->format('YmdHis'),
                'created_at' => now()->subYear()->addMonths(7),
                'updated_at' => now()->subYear()->addMonths(7),
            ],
            [
                'project_id' => $schoolProject?->id,
                'user_id' => $customer1?->id,
                'type' => 'payment',
                'status' => 'completed',
                'amount' => 500000.00,
                'description' => 'دفعة ثالثة - مشروع المدرسة',
                'payment_method' => 'bank_transfer',
                'reference' => 'TXN-'.now()->subMonths(3)->format('YmdHis'),
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ],
            [
                'project_id' => $schoolProject?->id,
                'user_id' => $contractor1?->id,
                'type' => 'withdrawal',
                'status' => 'completed',
                'amount' => 900000.00,
                'description' => 'سحب أرباح من مشروع المدرسة - النهائي',
                'payment_method' => 'bank_transfer',
                'reference' => 'WTH-'.now()->subMonths(2)->format('YmdHis'),
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ],
        ];

        foreach ($transactions as $transactionData) {
            if ($transactionData['project_id'] && $transactionData['user_id']) {
                Transaction::updateOrCreate(
                    [
                        'project_id' => $transactionData['project_id'],
                        'user_id' => $transactionData['user_id'],
                        'reference' => $transactionData['reference'],
                    ],
                    $transactionData
                );
            }
        }
    }
}
