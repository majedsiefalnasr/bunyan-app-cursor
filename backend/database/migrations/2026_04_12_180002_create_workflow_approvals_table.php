<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
            $table->foreignId('approval_rule_id')->nullable()->constrained('approval_rules')->nullOnDelete();
            $table->string('approver_role', 64);
            $table->string('action', 32)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index(['workflow_instance_id', 'action']);
            $table->index('approver_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_approvals');
    }
};
