<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'exists:projects,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required' => 'معرف المنتج مطلوب',
            'items.*.product_id.exists' => 'المنتج غير موجود',
            'items.*.quantity.required' => 'الكمية مطلوبة',
            'items.*.quantity.integer' => 'الكمية يجب أن تكون رقم صحيح',
            'items.*.price.required' => 'السعر مطلوب',
        ];
    }
}
