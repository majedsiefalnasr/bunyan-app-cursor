<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    protected function model(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null */
        return $this->newQuery()->where('email', $email)->first();
    }

    public function all(array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
            ->when($filters['active'] ?? null, fn ($q) => $q->active())
            ->when(
                $filters['search'] ?? null,
                fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            )
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function allByRole(string $role, array $filters = []): Collection
    {
        /** @var Collection<int, User> */
        return $this->newQuery()
            ->where('role', $role)
            ->when($filters['active'] ?? null, fn ($q) => $q->active())
            ->get();
    }
}
