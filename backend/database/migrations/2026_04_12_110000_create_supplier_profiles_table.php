<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('company_name_ar');
            $table->string('company_name_en')->nullable();
            $table->string('commercial_reg')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('verification_status', 32)->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('total_ratings')->default(0);
            $table->timestamps();

            $table->index('verification_status');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_profiles');
    }
};
