<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->string('city')->nullable()->after('location');
            $table->string('district')->nullable()->after('city');
            $table->decimal('location_lat', 10, 7)->nullable()->after('district');
            $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            $table->string('project_type', 32)->nullable()->after('location_lng');
            $table->decimal('budget_estimated', 15, 2)->nullable()->after('budget');
            $table->decimal('budget_actual', 15, 2)->nullable()->after('budget_estimated');
        });

        DB::table('projects')->where('status', 'pending')->update(['status' => 'draft']);
        DB::table('projects')->where('status', 'active')->update(['status' => 'in_progress']);
        DB::table('projects')->where('status', 'cancelled')->update(['status' => 'closed']);

        DB::table('projects')->whereNull('name_ar')->update(['name_ar' => DB::raw('name')]);
        DB::table('projects')->whereNull('budget_estimated')->update(['budget_estimated' => DB::raw('budget')]);
    }

    public function down(): void
    {
        DB::table('projects')->where('status', 'draft')->update(['status' => 'pending']);
        DB::table('projects')->where('status', 'planning')->update(['status' => 'pending']);
        DB::table('projects')->where('status', 'in_progress')->update(['status' => 'active']);
        DB::table('projects')->where('status', 'closed')->update(['status' => 'cancelled']);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'name_ar',
                'name_en',
                'city',
                'district',
                'location_lat',
                'location_lng',
                'project_type',
                'budget_estimated',
                'budget_actual',
            ]);
        });
    }
};
