<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('correlation_id')->index();
            $table->string('error_code')->index();
            $table->string('message', 500);
            $table->json('details')->nullable();
            $table->json('context')->nullable();
            $table->string('severity', 50)->index();
            $table->unsignedSmallInteger('http_status')->nullable()->index();
            $table->string('exception_class', 255)->nullable();
            $table->longText('stack_trace')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_role', 100)->nullable()->index();
            $table->string('request_method', 16)->nullable();
            $table->string('request_path', 500)->nullable();
            $table->string('request_ip', 45)->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['error_code', 'created_at']);
            $table->index(['severity', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
