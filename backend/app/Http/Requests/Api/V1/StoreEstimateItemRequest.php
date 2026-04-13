<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\EstimateItemCategory;
use App\Models\Estimate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstimateItemRequest extends FormRequest
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
            'category' => ['required', 'string', Rule::in(EstimateItemCategory::values())],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:32'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'description_ar' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
