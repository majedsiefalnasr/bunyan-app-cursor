<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Support\Collection;

class RoleRepository extends BaseRepository
{
    protected function model(): string
    {
        return Role::class;
    }

    public function findByName(string $name): ?Role
    {
        /** @var Role|null */
        return $this->newQuery()->where('name', $name)->first();
    }

    public function allWithUserCounts(): Collection
    {
        return $this->newQuery()
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get();
    }
}
