<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('mediable');
            $table->string('collection')->default('default')->index();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('thumb_path')->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->json('dimensions_json')->nullable();
            $table->string('alt_text_ar')->nullable();
            $table->string('alt_text_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_temporary')->default(false)->index();
            $table->timestamps();

            $table->index(['uploaded_by', 'created_at']);
            $table->index(['is_temporary', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
