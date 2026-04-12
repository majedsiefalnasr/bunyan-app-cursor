<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\DocumentCategory;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Project $project */
        $project = $this->route('project');

        return $this->user()?->can('view', $project) ?? false;
    }

    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');

        return [
            'file' => ['required', 'file', 'max:25600'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(DocumentCategory::values())],
            'document_id' => [
                'nullable',
                'integer',
                Rule::exists('documents', 'id')->where(function ($query) use ($project): void {
                    $query->where('documentable_type', Project::class)
                        ->where('documentable_id', $project->id)
                        ->whereNull('deleted_at');
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'الملف مطلوب',
            'title.required' => 'العنوان مطلوب',
            'category.required' => 'تصنيف المستند مطلوب',
        ];
    }
}
