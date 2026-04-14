<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analytics_metric_rollups')) {
            return;
        }

        Schema::create('analytics_metric_rollups', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key')->index();
            $table->string('bucket')->index(); // day|week|month
            $table->date('bucket_start')->index();
            $table->date('bucket_end')->index(); // exclusive

            $table->decimal('value', 18, 4);
            $table->json('dimensions')->nullable();
            $table->char('dimensions_hash', 64)->index();

            $table->dateTime('computed_at');
            $table->timestamps();

            $table->index(['metric_key', 'bucket', 'bucket_start']);
            $table->unique(['metric_key', 'bucket', 'bucket_start', 'dimensions_hash'], 'analytics_rollups_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_metric_rollups');
    }
};
