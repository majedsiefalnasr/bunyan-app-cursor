<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Rfq;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Rfq $rfq */
        $rfq = $this->route('rfq');

        return $this->user()->can('submitQuotation', $rfq);
    }

    public function rules(): array
    {
        return [
            'valid_until' => ['nullable', 'date'],
            'delivery_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.rfq_item_id' => ['required', 'integer'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'items.*.notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
