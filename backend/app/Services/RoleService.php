<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Exceptions\RbacException;
use App\Models\Role;
use App\Models\User;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleService
{
    public function __construct(
        private RoleRepository $roleRepository,
        private PermissionRepository $permissionRepository,
    ) {
    }

    public function getAllRoles(): Collection
    {
        return $this->roleRepository->allWithUserCounts();
    }

    public function getRolePermissions(Role $role): Collection
    {
        return $this->permissionRepository->getForRole($role);
    }

    /**
     * @return string[]
     */
    public function getUserPermissions(User $user): array
    {
        if ($user->role === UserRole::Admin) {
            return Cache::rememberForever('permissions:all:names', function () {
                return $this->permissionRepository->allPermissionNames()->toArray();
            });
        }

        return Cache::rememberForever(
            "user:{$user->id}:permissions",
            function () use ($user) {
                $role = $this->roleRepository->findByName($user->role->value);

                if ($role === null) {
                    return [];
                }

                return $role->permissions()->pluck('name')->toArray();
            }
        );
    }

    public function assignRole(User $user, string $roleName, ?User $assignedBy = null): User
    {
        $this->validateRoleChange($user, $roleName);

        $role = $this->roleRepository->findByName($roleName);

        DB::transaction(function () use ($user, $roleName, $role, $assignedBy) {
            $oldRole = $user->role->value;

            $user->update(['role' => $roleName]);

            if ($role !== null) {
                $this->syncUserRolePivot($user, $role, $assignedBy);
            }

            $user->tokens()->delete();

            $this->clearPermissionCache($user);

            Log::channel('api')->info('role.assigned', [
                'action' => 'role.assigned',
                'user_id' => $user->id,
                'old_role' => $oldRole,
                'new_role' => $roleName,
                'assigned_by' => $assignedBy?->id,
                'tokens_revoked' => true,
            ]);
        });

        return $user->fresh() ?? $user;
    }

    public function removeRole(User $user, ?User $assignedBy = null): User
    {
        return $this->assignRole($user, UserRole::Customer->value, $assignedBy);
    }

    public function syncUserRolePivot(User $user, ?Role $role = null, ?User $assignedBy = null): void
    {
        if ($role === null) {
            $role = $this->roleRepository->findByName($user->role->value);
        }

        if ($role === null) {
            return;
        }

        $user->roles()->sync([
            $role->id => [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy?->id,
            ],
        ]);
    }

    public function clearPermissionCache(User $user): void
    {
        Cache::forget("user:{$user->id}:permissions");
    }

    private function validateRoleChange(User $user, string $newRole): void
    {
        if ($user->role === UserRole::Admin && $newRole !== UserRole::Admin->value) {
            $adminCount = User::where('role', UserRole::Admin->value)
                ->where('id', '!=', $user->id)
                ->count();

            if ($adminCount === 0) {
                throw new RbacException(
                    __('errors.codes.RBAC_LAST_ADMIN.message'),
                    'RBAC_LAST_ADMIN'
                );
            }
        }
    }
}
