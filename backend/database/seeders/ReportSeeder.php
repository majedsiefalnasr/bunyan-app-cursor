<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        // Get users
        $engineer1 = User::where('email', 'engineer@example.com')->first();
        $engineer2 = User::where('email', 'khaled.alanzi@example.com')->first();
        $engineer3 = User::where('email', 'omar.alzahrani@example.com')->first();

        $architect1 = User::where('email', 'architect@example.com')->first();

        $reports = [
            [
                'task_id' => Task::where('title_ar', 'تنظيف وتسوية الموقع')->first()?->id,
                'phase_id' => Task::where('title_ar', 'تنظيف وتسوية الموقع')->first()?->phase_id,
                'project_id' => Task::where('title_ar', 'تنظيف وتسوية الموقع')->first()?->project_id,
                'type' => 'progress',
                'title' => 'تقرير التقدم اليومي - تنظيف الموقع',
                'description' => 'تم إنجاز 80% من أعمال تنظيف الموقع.',
                'content' => 'تم إنجاز 80% من أعمال تنظيف الموقع. تم إزالة جميع المخلفات والحطام. بدء تسوية التربة باستخدام الجرافة.',
                'created_by' => $engineer1?->id,
                'status' => 'approved',
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(20),
            ],
            [
                'task_id' => Task::where('title_ar', 'تنظيف وتسوية الموقع')->first()?->id,
                'phase_id' => Task::where('title_ar', 'تنظيف وتسوية الموقع')->first()?->phase_id,
                'project_id' => Task::where('title_ar', 'تنظيف وتسوية الموقع')->first()?->project_id,
                'type' => 'inspection',
                'title' => 'تقرير الفحص والمعاينة - الموقع المهيأ',
                'description' => 'الموقع جاهز للمرحلة التالية',
                'content' => 'تم التحقق من تسوية الموقع وهو جاهز للمرحلة التالية. جميع المتطلبات تم استيفاؤها.',
                'created_by' => $engineer1?->id,
                'status' => 'approved',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ],
            [
                'task_id' => Task::where('title_ar', 'حفر أساسات البئر')->first()?->id,
                'phase_id' => Task::where('title_ar', 'حفر أساسات البئر')->first()?->phase_id,
                'project_id' => Task::where('title_ar', 'حفر أساسات البئر')->first()?->project_id,
                'type' => 'progress',
                'title' => 'تقرير التقدم - أعمال الحفر',
                'description' => 'تم إنجاز 70% من أعمال الحفر',
                'content' => 'تم إنجاز 70% من أعمال الحفر. العمق الحالي 8 متر. استمرار الحفر وفقاً للجدول الزمني.',
                'created_by' => $engineer2?->id,
                'status' => 'reviewed',
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(12),
            ],
            [
                'task_id' => Task::where('title_ar', 'حفر أساسات البئر')->first()?->id,
                'phase_id' => Task::where('title_ar', 'حفر أساسات البئر')->first()?->phase_id,
                'project_id' => Task::where('title_ar', 'حفر أساسات البئر')->first()?->project_id,
                'type' => 'progress',
                'title' => 'تقرير التقدم - إنجاز الحفر',
                'description' => 'تم إنجاز 100% من أعمال الحفر',
                'content' => 'تم إنجاز 100% من أعمال الحفر. العمق النهائي 10 متر. الموقع جاهز لصب الخرسانة.',
                'created_by' => $engineer2?->id,
                'status' => 'approved',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8),
            ],
            [
                'task_id' => Task::where('title_ar', 'صب الخرسانة للأساسات')->first()?->id,
                'phase_id' => Task::where('title_ar', 'صب الخرسانة للأساسات')->first()?->phase_id,
                'project_id' => Task::where('title_ar', 'صب الخرسانة للأساسات')->first()?->project_id,
                'type' => 'progress',
                'title' => 'تقرير صب الخرسانة',
                'description' => 'تم صب 100 متر مكعب من الخرسانة',
                'content' => 'تم صب 100 متر مكعب من الخرسانة. الخرسانة جاهزة وتتصلب. درجة الحرارة المحيطة مناسبة.',
                'created_by' => $engineer3?->id,
                'status' => 'approved',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'task_id' => Task::where('title_ar', 'تركيب الحديد والشدات')->first()?->id,
                'phase_id' => Task::where('title_ar', 'تركيب الحديد والشدات')->first()?->phase_id,
                'project_id' => Task::where('title_ar', 'تركيب الحديد والشدات')->first()?->project_id,
                'type' => 'progress',
                'title' => 'تقرير تركيب حديد التسليح',
                'description' => 'تم تركيب 50% من حديد التسليح',
                'content' => 'تم تركيب 50% من حديد التسليح للأعمدة. جودة الربط والتثبيت جيدة. استمرار العمل.',
                'created_by' => $engineer1?->id,
                'status' => 'submitted',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'task_id' => Task::where('title_ar', 'تركيب الحديد والشدات')->first()?->id,
                'phase_id' => Task::where('title_ar', 'تركيب الحديد والشدات')->first()?->phase_id,
                'project_id' => Task::where('title_ar', 'تركيب الحديد والشدات')->first()?->project_id,
                'type' => 'progress',
                'title' => 'تقرير التقدم الأخير - حديد التسليح',
                'description' => 'تم إنجاز 95% من أعمال التسليح',
                'content' => 'تم إنجاز 95% من أعمال تركيب حديد التسليح. بقي اللمسات النهائية. جودة العمل ممتازة.',
                'created_by' => $engineer1?->id,
                'status' => 'submitted',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'task_id' => Task::where('title_ar', 'صب الأعمدة والأرضيات')->first()?->id,
                'phase_id' => Task::where('title_ar', 'صب الأعمدة والأرضيات')->first()?->phase_id,
                'project_id' => Task::where('title_ar', 'صب الأعمدة والأرضيات')->first()?->project_id,
                'type' => 'progress',
                'title' => 'تقرير البدء في صب الأعمدة',
                'description' => 'بدء أعمال صب الخرسانة للأعمدة',
                'content' => 'بدء أعمال صب الخرسانة للأعمدة. حضرت كمية كبيرة من الخرسانة الجاهزة. الفريق جاهز.',
                'created_by' => $engineer2?->id,
                'status' => 'submitted',
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'task_id' => Task::where('title_ar', 'صب الأعمدة والأرضيات')->first()?->id,
                'phase_id' => Task::where('title_ar', 'صب الأعمدة والأرضيات')->first()?->phase_id,
                'project_id' => Task::where('title_ar', 'صب الأعمدة والأرضيات')->first()?->project_id,
                'type' => 'progress',
                'title' => 'تقرير يومي - أعمال الصب',
                'description' => 'تم صب 30 متر مكعب من الخرسانة اليوم',
                'content' => 'تم صب 30 متر مكعب من الخرسانة اليوم. الأعمدة بدأت تأخذ شكلها. سيستمر الصب غداً.',
                'created_by' => $engineer2?->id,
                'status' => 'submitted',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($reports as $reportData) {
            if ($reportData['task_id'] && $reportData['phase_id'] && $reportData['project_id']) {
                Report::updateOrCreate(
                    [
                        'task_id' => $reportData['task_id'],
                        'title' => $reportData['title'],
                    ],
                    $reportData
                );
            }
        }
    }
}
