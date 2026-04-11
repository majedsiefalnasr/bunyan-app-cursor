<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'location' => ['sometimes', 'string', 'max:500'],
            'status' => ['sometimes', 'in:draft,in_progress,completed,paid'],
            'end_date' => ['sometimes', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'حالة المشروع غير صحيحة',
            'budget.numeric' => 'الميزانية يجب أن تكون رقم',
            'end_date.date' => 'تاريخ الانتهاء يجب أن يكون تاريخ صحيح',
        ];
    }
}
