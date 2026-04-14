<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analytics_events')) {
            return;
        }

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->index();
            $table->dateTime('occurred_at')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role')->nullable();

            $table->json('metadata');

            // Linkage keys (columns, not metadata) for joinable metrics
            $table->string('session_id')->nullable()->index();
            $table->string('thread_id')->nullable()->index();
            $table->string('request_id')->nullable()->index();

            $table->timestamps();

            $table->index(['event_name', 'occurred_at']);
            $table->index(['session_id', 'event_name', 'occurred_at']);
            $table->index(['thread_id', 'event_name', 'occurred_at']);
            $table->index(['request_id', 'event_name', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
