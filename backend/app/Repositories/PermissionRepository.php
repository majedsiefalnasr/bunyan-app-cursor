<?php

namespace App\Repositories;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;

class PermissionRepository extends BaseRepository
{
    protected function model(): string
    {
        return Permission::class;
    }

    public function findByName(string $name): ?Permission
    {
        /** @var Permission|null */
        return $this->newQuery()->where('name', $name)->first();
    }

    public function getForRole(Role $role): Collection
    {
        return $role->permissions()->orderBy('name')->get();
    }

    public function allPermissionNames(): Collection
    {
        return $this->newQuery()->pluck('name');
    }
}
