<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Media;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:51200'],
            'collection' => ['sometimes', 'string', 'max:64'],
            'alt_text_ar' => ['sometimes', 'nullable', 'string', 'max:500'],
            'alt_text_en' => ['sometimes', 'nullable', 'string', 'max:500'],
            'mediable_type' => ['nullable', 'string', Rule::in([Project::class]), 'required_with:mediable_id'],
            'mediable_id' => ['nullable', 'integer', 'exists:projects,id', 'required_with:mediable_type'],
            'is_temporary' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'الملف مطلوب',
            'mediable_id.exists' => 'المشروع غير موجود',
        ];
    }
}
