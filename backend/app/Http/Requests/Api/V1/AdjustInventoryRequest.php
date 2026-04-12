<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product
            && $this->user() !== null
            && $this->user()->can('manageInventory', $product);
    }

    public function rules(): array
    {
        return [
            'quantity_delta' => ['required', 'integer', Rule::notIn([0])],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'warehouse_location' => ['nullable', 'string', 'max:191'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
