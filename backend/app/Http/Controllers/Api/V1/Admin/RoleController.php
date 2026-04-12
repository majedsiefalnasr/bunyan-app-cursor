<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Http\Resources\Api\V1\RoleResource;
use App\Http\Resources\Api\V1\UserAdminResource;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    public function __construct(
        private RoleService $roleService,
        private UserRepository $userRepository,
    ) {
    }

    public function index(): JsonResponse
    {
        $roles = $this->roleService->getAllRoles();

        return $this->sendSuccess(
            RoleResource::collection($roles),
        );
    }

    public function permissions(Role $role): JsonResponse
    {
        $permissions = $this->roleService->getRolePermissions($role);

        return $this->sendSuccess([
            'role' => new RoleResource($role),
            'permissions' => PermissionResource::collection($permissions),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $filters = $request->only(['role', 'per_page']);
        $users = $this->userRepository->all($filters);

        return $this->sendSuccess(
            UserAdminResource::collection($users)->response()->getData(true),
        );
    }

    public function assignRole(AssignRoleRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->roleService->assignRole(
            $user,
            $request->validated('role'),
            $request->user(),
        );

        return $this->sendSuccess(
            new UserAdminResource($updatedUser),
            __('rbac.role_assigned'),
        );
    }

    public function removeRole(User $user): JsonResponse
    {
        $updatedUser = $this->roleService->removeRole(
            $user,
            request()->user(),
        );

        return $this->sendSuccess(
            new UserAdminResource($updatedUser),
            __('rbac.role_reset'),
        );
    }
}
