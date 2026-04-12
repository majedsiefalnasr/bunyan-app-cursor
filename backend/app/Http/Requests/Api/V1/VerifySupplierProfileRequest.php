<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\SupplierVerificationStatus;
use App\Models\SupplierProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifySupplierProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SupplierProfile $profile */
        $profile = $this->route('supplierProfile');

        return $this->user()->can('verify', $profile);
    }

    public function rules(): array
    {
        return [
            'verification_status' => ['required', 'string', Rule::in([
                SupplierVerificationStatus::Verified->value,
                SupplierVerificationStatus::Suspended->value,
            ])],
        ];
    }
}
