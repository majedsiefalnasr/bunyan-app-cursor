<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('status_transitions')->nullable(); // Define allowed transitions
            $table->json('approval_requirements')->nullable(); // Which statuses need approval
            $table->boolean('is_global')->default(false); // Is this the global default?
            $table->timestamps();

            $table->index('project_id');
            $table->index('is_global');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_configurations');
    }
};
