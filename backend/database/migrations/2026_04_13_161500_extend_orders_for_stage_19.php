<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number', 32)->nullable()->unique()->after('id');
            $table->foreignId('supplier_id')->nullable()->after('customer_id')->constrained('supplier_profiles')->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->after('project_id')->constrained('quotations')->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0)->after('status');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('shipping_amount', 15, 2)->default(0)->after('tax_amount');
            $table->timestamp('confirmed_at')->nullable()->after('delivery_address');
            $table->timestamp('shipped_at')->nullable()->after('confirmed_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            $table->string('description')->nullable()->after('quantity');
        });

        DB::table('orders')->select(['id', 'created_at'])->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                $created = $row->created_at ?? now();
                $ymd = date('Ymd', strtotime((string) $created));
                $suffix = str_pad((string) $row->id, 6, '0', STR_PAD_LEFT);
                DB::table('orders')->where('id', $row->id)->update([
                    'order_number' => 'BNY-'.$ymd.'-'.$suffix,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_id');
            $table->dropColumn('description');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropConstrainedForeignId('quotation_id');
            $table->dropColumn([
                'order_number',
                'subtotal',
                'tax_amount',
                'shipping_amount',
                'confirmed_at',
                'shipped_at',
                'delivered_at',
            ]);
        });
    }
};
