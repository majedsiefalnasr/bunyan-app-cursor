<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\UserRole;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roleEnum = $this->role instanceof UserRole ? $this->role : UserRole::tryFrom($this->role);
        $roleValue = $roleEnum !== null ? $roleEnum->value : $this->role;
        $roleLabel = $roleEnum !== null ? $roleEnum->label() : $this->role;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $roleValue,
            'role_label' => $roleLabel,
            'permissions' => app(RoleService::class)->getUserPermissions($this->resource),
            'phone' => $this->phone,
            'active' => $this->active,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
