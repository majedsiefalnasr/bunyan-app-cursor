<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\EstimateItemCategory;
use App\Models\Estimate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstimateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Estimate $estimate */
        $estimate = $this->route('estimate');

        return $this->user()?->can('manageItems', $estimate) ?? false;
    }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'string', Rule::in(EstimateItemCategory::values())],
            'quantity' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:32'],
            'unit_price' => ['sometimes', 'numeric', 'min:0'],
            'description_ar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'product_id' => ['sometimes', 'nullable', 'integer', 'exists:products,id'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
