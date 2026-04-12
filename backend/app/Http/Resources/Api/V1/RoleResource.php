<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enum = UserRole::tryFrom($this->name);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'label' => $enum?->label() ?? $this->name,
            'users_count' => $this->whenCounted('users'),
            'permissions_count' => $this->whenCounted('permissions'),
        ];
    }
}
