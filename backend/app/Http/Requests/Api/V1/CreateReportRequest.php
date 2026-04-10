<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'field_engineer' || $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'معرف المشروع مطلوب',
            'project_id.exists' => 'المشروع غير موجود',
            'title.required' => 'عنوان التقرير مطلوب',
            'content.required' => 'محتوى التقرير مطلوب',
        ];
    }
}
