<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository;
    }

    public function test_find_by_id_returns_model_when_found(): void
    {
        $user = User::factory()->create();

        $found = $this->repository->findById($user->id);

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->getKey());
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById(99999);

        $this->assertNull($result);
    }

    public function test_find_by_id_or_fail_throws_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findByIdOrFail(99999);
    }

    public function test_create_persists_model(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ];

        $user = $this->repository->create($data);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_update_persists_changes(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        /** @var User $updated */
        $updated = $this->repository->update($user, ['name' => 'New Name']);

        $this->assertSame('New Name', $updated->name);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_delete_soft_deletes_model(): void
    {
        $user = User::factory()->create();

        $this->repository->delete($user);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_restore_recovers_soft_deleted_model(): void
    {
        $user = User::factory()->create();
        $user->delete();

        /** @var User $restored */
        $restored = $this->repository->restore($user->id);

        $this->assertNull($restored->deleted_at);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    public function test_all_returns_paginated_results(): void
    {
        User::factory()->count(5)->create();

        $result = $this->repository->all(['per_page' => 3]);

        $this->assertSame(3, $result->perPage());
    }
}
