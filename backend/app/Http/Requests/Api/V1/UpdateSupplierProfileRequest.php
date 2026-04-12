<?php

namespace App\Http\Requests\Api\V1;

use App\Models\SupplierProfile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SupplierProfile $profile */
        $profile = $this->route('supplierProfile');

        return $this->user()->can('update', $profile);
    }

    public function rules(): array
    {
        return [
            'company_name_ar' => ['sometimes', 'string', 'max:255'],
            'company_name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'commercial_reg' => ['sometimes', 'nullable', 'string', 'max:100'],
            'tax_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'nullable', 'string', 'max:120'],
            'district' => ['sometimes', 'nullable', 'string', 'max:120'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:40'],
        ];
    }
}
