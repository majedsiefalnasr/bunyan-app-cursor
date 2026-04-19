<?php

namespace Database\Seeders;

use App\Enums\PhaseStatus;
use App\Models\Phase;
use App\Models\Project;
use Illuminate\Database\Seeder;

class PhaseSeeder extends Seeder
{
    public function run(): void
    {
        // Get projects
        $villaProject = Project::where('name', 'مشروع إعادة تطوير الفيلا')->first();
        $complexProject = Project::where('name', 'مشروع العمارة السكنية')->first();
        $hotelProject = Project::where('name', 'مشروع الفندق البوتيكي')->first();
        $centerProject = Project::where('name', 'مشروع مركز تجاري')->first();
        $schoolProject = Project::where('name', 'مشروع المدرسة الخاصة')->first();

        $phases = [
            // Villa phases
            [
                'project_id' => $villaProject?->id,
                'sort_order' => 1,
                'name' => 'مرحلة التخطيط والتصميم',
                'name_ar' => 'مرحلة التخطيط والتصميم',
                'name_en' => 'Planning & Design Phase',
                'description' => 'تخطيط شامل للمشروع والتصاميم المعمارية',
                'status' => PhaseStatus::Completed,
                'budget' => 50000.00,
                'progress' => 100,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->subMonths(1),
            ],
            [
                'project_id' => $villaProject?->id,
                'sort_order' => 2,
                'name' => 'مرحلة الأساسات',
                'name_ar' => 'مرحلة الأساسات',
                'name_en' => 'Foundation Phase',
                'description' => 'حفر وتنفيذ الأساسات',
                'status' => PhaseStatus::Completed,
                'budget' => 80000.00,
                'progress' => 100,
                'start_date' => now()->subMonths(1),
                'end_date' => now()->subDays(15),
            ],
            [
                'project_id' => $villaProject?->id,
                'sort_order' => 3,
                'name' => 'مرحلة الهيكل والدرج',
                'name_ar' => 'مرحلة الهيكل والدرج',
                'name_en' => 'Structure Phase',
                'description' => 'بناء الهيكل الخرساني والدرج',
                'status' => PhaseStatus::InProgress,
                'budget' => 150000.00,
                'progress' => 60,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addMonths(1),
            ],
            [
                'project_id' => $villaProject?->id,
                'sort_order' => 4,
                'name' => 'مرحلة التشطيبات',
                'name_ar' => 'مرحلة التشطيبات',
                'name_en' => 'Finishing Phase',
                'description' => 'تشطيبات داخلية وخارجية',
                'status' => PhaseStatus::Pending,
                'budget' => 220000.00,
                'progress' => 0,
                'start_date' => now()->addMonths(1),
                'end_date' => now()->addMonths(3),
            ],

            // Complex phases
            [
                'project_id' => $complexProject?->id,
                'sort_order' => 1,
                'name' => 'مرحلة الأساسات والحفريات',
                'name_ar' => 'مرحلة الأساسات والحفريات',
                'name_en' => 'Foundation & Excavation',
                'description' => 'حفر الموقع وتنفيذ الأساسات العميقة',
                'status' => PhaseStatus::Completed,
                'budget' => 400000.00,
                'progress' => 100,
                'start_date' => now()->subMonths(6),
                'end_date' => now()->subMonths(4),
            ],
            [
                'project_id' => $complexProject?->id,
                'sort_order' => 2,
                'name' => 'مرحلة الهيكل والأعمدة',
                'name_ar' => 'مرحلة الهيكل والأعمدة',
                'name_en' => 'Structure Phase',
                'description' => 'بناء الهيكل الخرساني للعمارة',
                'status' => PhaseStatus::InProgress,
                'budget' => 600000.00,
                'progress' => 75,
                'start_date' => now()->subMonths(4),
                'end_date' => now()->addMonths(2),
            ],
            [
                'project_id' => $complexProject?->id,
                'sort_order' => 3,
                'name' => 'مرحلة التشطيبات والديكور',
                'name_ar' => 'مرحلة التشطيبات والديكور',
                'name_en' => 'Finishing Phase',
                'description' => 'التشطيبات الداخلية والخارجية والديكور',
                'status' => PhaseStatus::Pending,
                'budget' => 1000000.00,
                'progress' => 0,
                'start_date' => now()->addMonths(2),
                'end_date' => now()->addMonths(8),
            ],

            // Hotel phases
            [
                'project_id' => $hotelProject?->id,
                'sort_order' => 1,
                'name' => 'مرحلة التصاميم والموافقات',
                'name_ar' => 'مرحلة التصاميس والموافقات',
                'name_en' => 'Design & Approvals',
                'description' => 'إعداد التصاميم والحصول على الموافقات',
                'status' => PhaseStatus::InProgress,
                'budget' => 500000.00,
                'progress' => 40,
                'start_date' => now()->addMonths(1),
                'end_date' => now()->addMonths(3),
            ],
            [
                'project_id' => $hotelProject?->id,
                'sort_order' => 2,
                'name' => 'مرحلة التنفيذ',
                'name_ar' => 'مرحلة التنفيذ',
                'name_en' => 'Construction Phase',
                'description' => 'تنفيذ المشروع بكامل مراحله',
                'status' => PhaseStatus::Pending,
                'budget' => 4500000.00,
                'progress' => 0,
                'start_date' => now()->addMonths(3),
                'end_date' => now()->addMonths(15),
            ],

            // School phases
            [
                'project_id' => $schoolProject?->id,
                'sort_order' => 1,
                'name' => 'مرحلة التصاميم',
                'name_ar' => 'مرحلة التصاميس',
                'name_en' => 'Design Phase',
                'description' => 'إعداد التصاميس المعمارية',
                'status' => PhaseStatus::Completed,
                'budget' => 150000.00,
                'progress' => 100,
                'start_date' => now()->subYear()->addMonths(1),
                'end_date' => now()->subYear()->addMonths(3),
            ],
            [
                'project_id' => $schoolProject?->id,
                'sort_order' => 2,
                'name' => 'مرحلة البناء الأساسي',
                'name_ar' => 'مرحلة البناء الأساسي',
                'name_en' => 'Basic Construction',
                'description' => 'بناء الهيكل الأساسي للمدرسة',
                'status' => PhaseStatus::Completed,
                'budget' => 900000.00,
                'progress' => 100,
                'start_date' => now()->subYear()->addMonths(3),
                'end_date' => now()->subMonths(6),
            ],
            [
                'project_id' => $schoolProject?->id,
                'sort_order' => 3,
                'name' => 'مرحلة التشطيبات والتجهيزات',
                'name_ar' => 'مرحلة التشطيبات والتجهيزات',
                'name_en' => 'Finishing & Equipment',
                'description' => 'التشطيبات والأثاث والتجهيزات',
                'status' => PhaseStatus::Completed,
                'budget' => 450000.00,
                'progress' => 100,
                'start_date' => now()->subMonths(6),
                'end_date' => now()->subMonths(2),
            ],
        ];

        foreach ($phases as $phaseData) {
            if ($phaseData['project_id']) {
                Phase::updateOrCreate(
                    [
                        'project_id' => $phaseData['project_id'],
                        'sort_order' => $phaseData['sort_order'],
                    ],
                    $phaseData
                );
            }
        }
    }
}
