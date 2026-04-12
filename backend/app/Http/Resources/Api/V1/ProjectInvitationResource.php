<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectInvitationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'project_role' => $this->project_role->value,
            'project_role_label' => $this->project_role->label(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'invited_by' => $this->whenLoaded('invitedByUser', fn () => new UserResource($this->invitedByUser)),
        ];
    }
}
