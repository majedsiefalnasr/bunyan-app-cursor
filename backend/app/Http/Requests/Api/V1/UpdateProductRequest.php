<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::Admin;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:2000'],
            'category' => ['sometimes', 'string', 'max:100'],
            'price' => ['sometimes', 'numeric', 'min:0.01'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'supplier_id' => ['sometimes', 'nullable', 'integer', 'exists:supplier_profiles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.numeric' => 'السعر يجب أن يكون رقم',
            'quantity.integer' => 'الكمية يجب أن تكون رقم صحيح',
        ];
    }
}
