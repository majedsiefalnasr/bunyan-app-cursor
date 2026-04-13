<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Rfq;
use Illuminate\Foundation\Http\FormRequest;

class StoreRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Rfq::class);
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'delivery_deadline' => ['nullable', 'date'],
            'response_deadline' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:5000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001', 'max:999999999'],
            'items.*.unit' => ['required', 'string', 'max:32'],
            'items.*.specifications' => ['nullable', 'array'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}
