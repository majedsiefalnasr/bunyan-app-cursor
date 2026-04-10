<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function __construct(
        private readonly User $model,
    ) {}

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByIdOrFail(int $id): User
    {
        return $this->model->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function all(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->byRole($role))
            ->when($filters['active'] ?? null, fn ($q) => $q->active())
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function allByRole(string $role, array $filters = []): Collection
    {
        return $this->model
            ->byRole($role)
            ->when($filters['active'] ?? null, fn ($q) => $q->active())
            ->get();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function restore(int $userId): bool
    {
        return $this->model->withTrashed()->findOrFail($userId)->restore();
    }
}
