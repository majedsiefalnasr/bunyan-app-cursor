<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('order_id')->nullable()->constrained('orders')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('supplier_profiles')->nullOnDelete();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('vat_amount', 15, 2);
            $table->decimal('vat_percentage', 5, 2)->default(15.00);
            $table->decimal('total', 15, 2);
            $table->string('status')->index();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('zatca_qr_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('supplier_id');
            $table->index('created_at');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('description_ar')->nullable();
            $table->string('description_en')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('vat_rate', 5, 2)->default(15.00);
            $table->decimal('line_subtotal', 15, 2);
            $table->decimal('line_vat', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
