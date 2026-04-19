<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Get demo users
        $customer1 = User::where('email', 'customer@example.com')->first();
        $customer2 = User::where('email', 'mohammad.alarian@example.com')->first();
        $customer3 = User::where('email', 'fatima.alsahli@example.com')->first();
        $customer4 = User::where('email', 'abdulrahman.alshehri@example.com')->first();

        $contractor1 = User::where('email', 'contractor@example.com')->first();
        $contractor2 = User::where('email', 'advanced.construction@example.com')->first();
        $contractor3 = User::where('email', 'alnoor.contracting@example.com')->first();

        $architect1 = User::where('email', 'architect@example.com')->first();
        $architect2 = User::where('email', 'dr.ali.alruwayili@example.com')->first();

        $projects = [
            [
                'name' => 'مشروع إعادة تطوير الفيلا',
                'name_ar' => 'مشروع إعادة تطوير الفيلا',
                'name_en' => 'Villa Renovation Project',
                'description' => 'مشروع شامل لإعادة تطوير وتجديد فيلا سكنية حديثة',
                'customer_id' => $customer1?->id,
                'contractor_id' => $contractor1?->id,
                'supervising_architect_id' => $architect1?->id,
                'status' => ProjectStatus::InProgress,
                'budget' => 500000.00,
                'budget_estimated' => 480000.00,
                'budget_actual' => 320000.00,
                'location' => 'حي النرجس، الرياض',
                'city' => 'الرياض',
                'district' => 'النرجس',
                'location_lat' => 24.7136,
                'location_lng' => 46.6753,
                'project_type' => 'residential',
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(4),
            ],
            [
                'name' => 'مشروع العمارة السكنية',
                'name_ar' => 'مشروع العمارة السكنية - القاهرة',
                'name_en' => 'Residential Complex Cairo',
                'description' => 'مشروع بناء عمارة سكنية متعددة الطوابق',
                'customer_id' => $customer2?->id,
                'contractor_id' => $contractor2?->id,
                'supervising_architect_id' => $architect2?->id,
                'status' => ProjectStatus::InProgress,
                'budget' => 2000000.00,
                'budget_estimated' => 1900000.00,
                'budget_actual' => 1200000.00,
                'location' => 'حي التجمع الخامس، القاهرة',
                'city' => 'القاهرة',
                'district' => 'التجمع الخامس',
                'location_lat' => 30.0050,
                'location_lng' => 31.4872,
                'project_type' => 'residential',
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addMonths(8),
            ],
            [
                'name' => 'مشروع الفندق البوتيكي',
                'name_ar' => 'مشروع الفندق البوتيكي',
                'name_en' => 'Boutique Hotel Project',
                'description' => 'مشروع بناء فندق بوتيكي فاخر',
                'customer_id' => $customer3?->id,
                'contractor_id' => $contractor3?->id,
                'supervising_architect_id' => $architect1?->id,
                'status' => ProjectStatus::Planning,
                'budget' => 5000000.00,
                'budget_estimated' => 5000000.00,
                'budget_actual' => 100000.00,
                'location' => 'منطقة البلد، جدة',
                'city' => 'جدة',
                'district' => 'البلد',
                'location_lat' => 21.5433,
                'location_lng' => 39.1727,
                'project_type' => 'commercial',
                'start_date' => now()->addMonths(1),
                'end_date' => now()->addMonths(18),
            ],
            [
                'name' => 'مشروع مركز تجاري',
                'name_ar' => 'مشروع مركز تجاري متطور',
                'name_en' => 'Advanced Shopping Center',
                'description' => 'مشروع مركز تجاري حديث مع مواقف سيارات',
                'customer_id' => $customer4?->id,
                'contractor_id' => $contractor1?->id,
                'supervising_architect_id' => $architect2?->id,
                'status' => ProjectStatus::Draft,
                'budget' => 3000000.00,
                'budget_estimated' => 3000000.00,
                'budget_actual' => 0.00,
                'location' => 'حي الملز، الرياض',
                'city' => 'الرياض',
                'district' => 'الملز',
                'location_lat' => 24.7625,
                'location_lng' => 46.7127,
                'project_type' => 'commercial',
                'start_date' => now()->addMonths(3),
                'end_date' => now()->addMonths(15),
            ],
            [
                'name' => 'مشروع المدرسة الخاصة',
                'name_ar' => 'مشروع المدرسة الخاصة',
                'name_en' => 'Private School Project',
                'description' => 'مشروع بناء مدرسة خاصة حديثة',
                'customer_id' => $customer1?->id,
                'contractor_id' => $contractor2?->id,
                'supervising_architect_id' => $architect1?->id,
                'status' => ProjectStatus::Completed,
                'budget' => 1500000.00,
                'budget_estimated' => 1450000.00,
                'budget_actual' => 1420000.00,
                'location' => 'حي الزعيم، الرياض',
                'city' => 'الرياض',
                'district' => 'الزعيم',
                'location_lat' => 24.7830,
                'location_lng' => 46.8058,
                'project_type' => 'educational',
                'start_date' => now()->subYear(),
                'end_date' => now()->subMonths(2),
            ],
        ];

        foreach ($projects as $projectData) {
            if ($projectData['customer_id'] && $projectData['contractor_id'] && $projectData['supervising_architect_id']) {
                Project::updateOrCreate(
                    ['name' => $projectData['name']],
                    $projectData
                );
            }
        }
    }
}
