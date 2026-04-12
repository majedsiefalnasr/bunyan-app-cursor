<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\EstimateStatus;
use App\Models\Estimate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Estimate $estimate */
        $estimate = $this->route('estimate');

        return $this->user()?->can('update', $estimate) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'markup_percentage' => ['sometimes', 'numeric', 'min:0', 'max:1000'],
            'status' => ['sometimes', 'string', Rule::in(EstimateStatus::values())],
        ];
    }
}
