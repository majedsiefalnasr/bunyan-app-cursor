<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->unsignedInteger('sort_order')->default(0)->after('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'sort_order']);
        });
    }
};
