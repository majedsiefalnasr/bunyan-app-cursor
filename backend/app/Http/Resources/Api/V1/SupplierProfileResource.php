<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $verificationStatus = $this->verification_status;

        return [
            'id' => $this->id,
            'company_name_ar' => $this->company_name_ar,
            'company_name_en' => $this->company_name_en,
            'commercial_reg' => $this->commercial_reg,
            'tax_number' => $this->tax_number,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'phone' => $this->phone,
            'verification_status' => $verificationStatus instanceof \BackedEnum ? $verificationStatus->value : $verificationStatus,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'rating_avg' => (string) $this->rating_avg,
            'total_ratings' => $this->total_ratings,
            'user' => $this->whenLoaded('user', function ($user) {
                if ($user === null) {
                    return null;
                }

                $role = $user->role;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $role instanceof \BackedEnum ? $role->value : $role,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
