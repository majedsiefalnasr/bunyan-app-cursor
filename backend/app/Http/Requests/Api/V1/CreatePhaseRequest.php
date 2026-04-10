<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreatePhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'budget' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المرحلة مطلوب',
            'budget.required' => 'ميزانية المرحلة مطلوبة',
            'budget.numeric' => 'الميزانية يجب أن تكون رقم',
            'end_date.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البداية',
        ];
    }
}
