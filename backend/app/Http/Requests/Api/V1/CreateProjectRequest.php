<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::Customer || $this->user()->role === UserRole::Admin;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'budget' => ['required', 'numeric', 'min:0'],
            'location' => ['required', 'string', 'max:500'],
            'start_date' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المشروع مطلوب',
            'budget.required' => 'الميزانية مطلوبة',
            'budget.numeric' => 'الميزانية يجب أن تكون رقم',
            'budget.min' => 'الميزانية يجب أن تكون أكبر من صفر',
            'location.required' => 'الموقع مطلوب',
            'start_date.date' => 'تاريخ البداية يجب أن يكون تاريخ صحيح',
            'start_date.after' => 'تاريخ البداية يجب أن يكون في المستقبل',
        ];
    }
}
