<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\UserRole;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role instanceof UserRole ? $this->role->value : $this->role,
            'permissions' => $this->when(
                $request->user()?->id === $this->id,
                fn () => app(RoleService::class)->getUserPermissions($this->resource),
            ),
            'phone' => $this->phone,
            'active' => $this->active,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
