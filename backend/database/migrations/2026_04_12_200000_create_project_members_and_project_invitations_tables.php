<?php

use App\Enums\ProjectRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('project_role', 32);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
            $table->index('project_id');
        });

        Schema::create('project_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('email', 255);
            $table->string('project_role', 32);
            $table->string('token_hash', 128)->unique();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['project_id', 'email']);
        });

        $owner = ProjectRole::Owner->value;
        $now = now();

        DB::table('projects')->orderBy('id')->chunkById(100, function ($projects) use ($owner, $now): void {
            foreach ($projects as $project) {
                if ($project->customer_id === null) {
                    continue;
                }

                DB::table('project_members')->updateOrInsert(
                    [
                        'project_id' => $project->id,
                        'user_id' => $project->customer_id,
                    ],
                    [
                        'project_role' => $owner,
                        'joined_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invitations');
        Schema::dropIfExists('project_members');
    }
};
