<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:2000'],
            'budget' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:pending,in_progress,completed,paid'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'حالة المرحلة غير صحيحة',
            'budget.numeric' => 'الميزانية يجب أن تكون رقم',
        ];
    }
}
