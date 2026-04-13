<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 32)->index();
            $table->date('delivery_deadline')->nullable();
            $table->timestamp('response_deadline')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->unsignedBigInteger('awarded_quotation_id')->nullable();
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['created_by', 'status', 'created_at']);
            $table->index(['status', 'sent_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->text('description');
            $table->decimal('quantity', 14, 4);
            $table->string('unit', 32);
            $table->json('specifications')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('rfq_id');
            $table->index(['rfq_id', 'sort_order']);
        });

        Schema::create('rfq_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('supplier_profiles')->cascadeOnDelete();
            $table->timestamp('invited_at');
            $table->timestamps();

            $table->unique(['rfq_id', 'supplier_id']);
            $table->index(['supplier_id', 'rfq_id']);
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('supplier_profiles')->cascadeOnDelete();
            $table->string('status', 32)->index();
            $table->decimal('total_price', 12, 2)->default(0);
            $table->unsignedInteger('delivery_days')->nullable();
            $table->text('notes')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['rfq_id', 'supplier_id']);
            $table->index(['rfq_id', 'total_price', 'submitted_at', 'id']);
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->foreign('awarded_quotation_id')
                ->references('id')
                ->on('quotations')
                ->nullOnDelete();
            $table->index('awarded_quotation_id');
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->constrained('rfq_items')->cascadeOnDelete();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('quotation_id');
            $table->index('rfq_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('rfq_targets');
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfqs');
    }
};

