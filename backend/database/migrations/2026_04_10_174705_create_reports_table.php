<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('phases')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); // Field engineer
            $table->text('description');
            $table->json('attachments')->nullable(); // File paths/URLs for images/videos
            $table->string('status')->default('submitted'); // submitted, reviewed, approved
            $table->timestamps();
            $table->softDeletes();

            $table->index('task_id');
            $table->index('phase_id');
            $table->index('project_id');
            $table->index('created_by');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
