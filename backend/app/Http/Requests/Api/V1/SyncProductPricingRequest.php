<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class SyncProductPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::Admin;
    }

    public function rules(): array
    {
        return [
            'tiers' => ['required', 'array', 'max:50'],
            'tiers.*.min_quantity' => ['required', 'integer', 'min:1'],
            'tiers.*.max_quantity' => ['nullable', 'integer', 'min:1'],
            'tiers.*.unit_price' => ['required', 'numeric', 'min:0'],
            'tiers.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ];
    }
}
