<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class CreateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::FieldEngineer || $this->user()->role === UserRole::Admin;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'attachments' => ['sometimes', 'array', 'max:10'],
            'attachments.*' => ['required', 'string', 'max:2048'],
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
