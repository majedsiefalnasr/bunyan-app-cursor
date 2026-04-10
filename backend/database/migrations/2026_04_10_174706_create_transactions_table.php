<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type'); // payment, withdrawal, refund
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('payment_method')->nullable(); // stripe, bank_transfer, etc.
            $table->string('reference')->nullable(); // External transaction ID
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('project_id');
            $table->index('order_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
