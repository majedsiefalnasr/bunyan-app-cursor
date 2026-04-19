<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Phase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        // Get users
        $engineer1 = User::where('email', 'engineer@example.com')->first();
        $engineer2 = User::where('email', 'khaled.alanzi@example.com')->first();
        $engineer3 = User::where('email', 'omar.alzahrani@example.com')->first();

        $contractor1 = User::where('email', 'contractor@example.com')->first();

        // Get phases
        $phases = Phase::all();

        $tasks = [
            // Villa Foundation Phase Tasks
            [
                'phase_id' => Phase::where('name', 'مرحلة الأساسات')->first()?->id,
                'project_id' => Phase::where('name', 'مرحلة الأساسات')->first()?->project_id,
                'sort_order' => 1,
                'title_ar' => 'تنظيف وتسوية الموقع',
                'title_en' => 'Site Cleaning and Leveling',
                'name' => 'تنظيف وتسوية الموقع',
                'description' => 'تنظيف الموقع وتسوية التربة',
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::High,
                'budget' => 20000.00,
                'assigned_to' => $engineer1?->id,
                'created_by' => $contractor1?->id,
                'start_date' => now()->subMonths(1),
                'end_date' => now()->subDays(25),
                'due_date' => now()->subDays(20),
                'estimated_hours' => 80.00,
                'actual_hours' => 75.00,
            ],
            [
                'phase_id' => Phase::where('name', 'مرحلة الأساسات')->first()?->id,
                'project_id' => Phase::where('name', 'مرحلة الأساسات')->first()?->project_id,
                'sort_order' => 2,
                'title_ar' => 'حفر أساسات البئر',
                'title_en' => 'Excavation of Foundation Wells',
                'name' => 'حفر أساسات البئر',
                'description' => 'حفر الآبار للأساسات العميقة',
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::High,
                'budget' => 30000.00,
                'assigned_to' => $engineer2?->id,
                'created_by' => $contractor1?->id,
                'start_date' => now()->subDays(25),
                'end_date' => now()->subDays(15),
                'due_date' => now()->subDays(15),
                'estimated_hours' => 120.00,
                'actual_hours' => 130.00,
            ],
            [
                'phase_id' => Phase::where('name', 'مرحلة الأساسات')->first()?->id,
                'project_id' => Phase::where('name', 'مرحلة الأساسات')->first()?->project_id,
                'sort_order' => 3,
                'title_ar' => 'صب الخرسانة للأساسات',
                'title_en' => 'Concrete Pouring for Foundation',
                'name' => 'صب الخرسانة للأساسات',
                'description' => 'صب الخرسانة الأساسية',
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::High,
                'budget' => 30000.00,
                'assigned_to' => $engineer3?->id,
                'created_by' => $contractor1?->id,
                'start_date' => now()->subDays(15),
                'end_date' => now()->subDays(5),
                'due_date' => now()->subDays(5),
                'estimated_hours' => 100.00,
                'actual_hours' => 95.00,
            ],

            // Villa Structure Phase Tasks
            [
                'phase_id' => Phase::where('name', 'مرحلة الهيكل والدرج')->first()?->id,
                'project_id' => Phase::where('name', 'مرحلة الهيكل والدرج')->first()?->project_id,
                'sort_order' => 1,
                'title_ar' => 'تركيب الحديد والشدات',
                'title_en' => 'Steel Reinforcement Installation',
                'name' => 'تركيب الحديد والشدات',
                'description' => 'تركيب حديد التسليح والشدات الخشبية',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::High,
                'budget' => 50000.00,
                'assigned_to' => $engineer1?->id,
                'created_by' => $contractor1?->id,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(10),
                'due_date' => now()->addDays(10),
                'estimated_hours' => 150.00,
                'actual_hours' => 100.00,
            ],
            [
                'phase_id' => Phase::where('name', 'مرحلة الهيكل والدرج')->first()?->id,
                'project_id' => Phase::where('name', 'مرحلة الهيكل والدرج')->first()?->project_id,
                'sort_order' => 2,
                'title_ar' => 'صب الأعمدة والأرضيات',
                'title_en' => 'Columns and Floors Pouring',
                'name' => 'صب الأعمدة والأرضيات',
                'description' => 'صب الخرسانة للأعمدة والأرضيات',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::High,
                'budget' => 60000.00,
                'assigned_to' => $engineer2?->id,
                'created_by' => $contractor1?->id,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'due_date' => now()->addMonths(1),
                'estimated_hours' => 200.00,
                'actual_hours' => 50.00,
            ],
            [
                'phase_id' => Phase::where('name', 'مرحلة الهيكل والدرج')->first()?->id,
                'project_id' => Phase::where('name', 'مرحلة الهيكل والدرج')->first()?->project_id,
                'sort_order' => 3,
                'title_ar' => 'تركيب الدرج والسقالات',
                'title_en' => 'Stairs and Scaffolding Installation',
                'name' => 'تركيب الدرج والسقالات',
                'description' => 'تركيب الدرج الخرساني والسقالات الآمنة',
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::Medium,
                'budget' => 40000.00,
                'assigned_to' => $engineer3?->id,
                'created_by' => $contractor1?->id,
                'start_date' => now()->addDays(20),
                'end_date' => now()->addDays(35),
                'due_date' => now()->addDays(35),
                'estimated_hours' => 120.00,
                'actual_hours' => 0.00,
            ],

            // Complex Structure Phase Tasks
            [
                'phase_id' => Phase::where('project_id', Phase::where('name', 'مرحلة الهيكل والأعمدة')->first()?->project_id)
                    ->where('name', 'مرحلة الهيكل والأعمدة')->first()?->id,
                'project_id' => Phase::where('name', 'مرحلة الهيكل والأعمدة')->first()?->project_id,
                'sort_order' => 1,
                'title_ar' => 'صب الرقة الأولى',
                'title_en' => 'First Floor Pouring',
                'name' => 'صب الرقة الأولى',
                'description' => 'صب الخرسانة للرقة الأولى من العمارة',
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::High,
                'budget' => 150000.00,
                'assigned_to' => $engineer1?->id,
                'created_by' => $contractor1?->id,
                'start_date' => now()->subMonths(3),
                'end_date' => now()->subMonths(2),
                'due_date' => now()->subMonths(2),
                'estimated_hours' => 400.00,
                'actual_hours' => 420.00,
            ],
            [
                'phase_id' => Phase::where('name', 'مرحلة الهيكل والأعمدة')->first()?->id,
                'project_id' => Phase::where('name', 'مرحلة الهيكل والأعمدة')->first()?->project_id,
                'sort_order' => 2,
                'title_ar' => 'صب الرقات المتبقية',
                'title_en' => 'Remaining Floors Pouring',
                'name' => 'صب الرقات المتبقية',
                'description' => 'صب الخرسانة للرقات المتبقية من العمارة',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::High,
                'budget' => 300000.00,
                'assigned_to' => $engineer2?->id,
                'created_by' => $contractor1?->id,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(1),
                'due_date' => now()->addMonths(1),
                'estimated_hours' => 800.00,
                'actual_hours' => 450.00,
            ],
        ];

        foreach ($tasks as $taskData) {
            if ($taskData['phase_id'] && $taskData['project_id']) {
                Task::updateOrCreate(
                    [
                        'project_id' => $taskData['project_id'],
                        'phase_id' => $taskData['phase_id'],
                        'sort_order' => $taskData['sort_order'],
                    ],
                    $taskData
                );
            }
        }
    }
}
