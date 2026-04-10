<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_configuration_id')->constrained('workflow_configurations')->cascadeOnDelete();
            $table->string('entity_type'); // project, phase, task
            $table->string('status_from'); // e.g., pending
            $table->string('status_to'); // e.g., in_progress
            $table->string('approver_role'); // Role required to approve
            $table->integer('approval_count')->default(1); // Number of approvals required
            $table->timestamps();

            $table->index('workflow_configuration_id');
            $table->index(['entity_type', 'status_from', 'status_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_rules');
    }
};
