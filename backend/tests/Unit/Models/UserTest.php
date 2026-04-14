<?php

namespace Tests\Unit\Models;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_relationship_methods_return_expected_relation_types(): void
    {
        $user = new User;

        $this->assertInstanceOf(HasMany::class, $user->projects());
        $this->assertInstanceOf(HasMany::class, $user->contractorProjects());
        $this->assertInstanceOf(HasMany::class, $user->supervisedProjects());
        $this->assertInstanceOf(HasMany::class, $user->assignedTasks());
        $this->assertInstanceOf(HasMany::class, $user->reports());
        $this->assertInstanceOf(HasMany::class, $user->transactions());
        $this->assertInstanceOf(HasMany::class, $user->orders());
        $this->assertInstanceOf(HasMany::class, $user->conversationParticipants());
        $this->assertInstanceOf(HasMany::class, $user->sentMessages());
        $this->assertInstanceOf(HasMany::class, $user->uploadedMedia());
        $this->assertInstanceOf(HasOne::class, $user->supplierProfile());
        $this->assertInstanceOf(MorphMany::class, $user->notifications());
        $this->assertInstanceOf(HasMany::class, $user->notificationPreferences());
        $this->assertInstanceOf(BelongsToMany::class, $user->roles());
    }

    public function test_scopes_filter_active_and_by_role(): void
    {
        $activeCustomer = User::factory()->customer()->create(['active' => true]);
        $inactiveCustomer = User::factory()->customer()->inactive()->create();
        $activeAdmin = User::factory()->admin()->create(['active' => true]);

        $activeIds = User::query()->active()->pluck('id')->all();
        $this->assertContains($activeCustomer->id, $activeIds);
        $this->assertContains($activeAdmin->id, $activeIds);
        $this->assertNotContains($inactiveCustomer->id, $activeIds);

        $adminIds = User::query()->byRole(UserRole::Admin->value)->pluck('id')->all();
        $this->assertSame([$activeAdmin->id], $adminIds);
    }
}
