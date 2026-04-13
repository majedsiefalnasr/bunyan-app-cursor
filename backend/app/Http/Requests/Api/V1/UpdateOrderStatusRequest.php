<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(OrderStatus::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة الطلب مطلوبة',
            'status.in' => 'قيمة حالة الطلب غير صالحة',
        ];
    }
}
