<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب',
            'category.required' => 'فئة المنتج مطلوبة',
            'price.required' => 'سعر المنتج مطلوب',
            'price.numeric' => 'السعر يجب أن يكون رقم',
            'quantity.required' => 'الكمية مطلوبة',
            'quantity.integer' => 'الكمية يجب أن تكون رقم صحيح',
        ];
    }
}
