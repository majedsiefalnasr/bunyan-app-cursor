<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskRequest extends FormRequest
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
            'assigned_to' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المهمة مطلوب',
            'budget.required' => 'ميزانية المهمة مطلوبة',
            'assigned_to.exists' => 'المستخدم المحدد غير موجود',
            'end_date.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البداية',
        ];
    }
}
