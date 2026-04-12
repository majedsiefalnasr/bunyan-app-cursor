<?php

use App\Models\Task;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained('projects')->cascadeOnDelete();
            $table->string('title_ar')->nullable()->after('phase_id');
            $table->string('title_en')->nullable()->after('title_ar');
            $table->string('priority', 32)->default('medium')->after('status');
            $table->date('due_date')->nullable()->after('end_date');
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('due_date');
            $table->decimal('actual_hours', 8, 2)->nullable()->after('estimated_hours');
            $table->unsignedInteger('sort_order')->default(0)->after('actual_hours');
            $table->foreignId('created_by')->nullable()->after('sort_order')->constrained('users')->nullOnDelete();
        });

        $isPretend = in_array('--pretend', $_SERVER['argv'] ?? [], true);

        if (! $isPretend) {
            foreach (Task::query()->cursor() as $task) {
                $task->loadMissing('phase');
                $dirty = false;
                if ($task->project_id === null && $task->phase) {
                    $task->project_id = $task->phase->project_id;
                    $dirty = true;
                }
                if ($task->title_ar === null && $task->name !== null) {
                    $task->title_ar = $task->name;
                    $dirty = true;
                }
                if ($dirty) {
                    $task->saveQuietly();
                }
            }

            $statusMap = [
                'pending' => 'todo',
                'completed' => 'done',
                'approved' => 'done',
                'rejected' => 'blocked',
            ];
            foreach ($statusMap as $from => $to) {
                DB::table('tasks')->where('status', $from)->update(['status' => $to]);
            }
        }

        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index('task_id');
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('depends_on_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'depends_on_task_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'status']);
        });

        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('task_comments');

        if (! in_array('--pretend', $_SERVER['argv'] ?? [], true)) {
            $reverse = [
                'todo' => 'pending',
                'done' => 'completed',
                'blocked' => 'rejected',
            ];
            foreach ($reverse as $from => $to) {
                DB::table('tasks')->where('status', $from)->update(['status' => $to]);
            }
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'project_id',
                'title_ar',
                'title_en',
                'priority',
                'due_date',
                'estimated_hours',
                'actual_hours',
                'sort_order',
                'created_by',
            ]);
        });
    }
};
