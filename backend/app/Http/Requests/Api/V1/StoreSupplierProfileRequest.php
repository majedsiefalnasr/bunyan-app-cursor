<?php

namespace App\Http\Requests\Api\V1;

use App\Models\SupplierProfile;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SupplierProfile::class);
    }

    public function rules(): array
    {
        return [
            'company_name_ar' => ['required', 'string', 'max:255'],
            'company_name_en' => ['nullable', 'string', 'max:255'],
            'commercial_reg' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:40'],
        ];
    }
}
