<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('workflow_configurations', function (Blueprint $table) {
            $table->string('type')->nullable()->after('description');
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->boolean('is_active')->default(true)->after('is_global');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_configurations', function (Blueprint $table) {
            $table->dropColumn(['type', 'name_ar', 'name_en', 'is_active']);
        });
    }
};
