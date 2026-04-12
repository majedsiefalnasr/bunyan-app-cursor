<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TaskPriority;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');
        assert($project instanceof Project);

        return [
            'phase_id' => [
                'required',
                'integer',
                Rule::exists('phases', 'id')->where('project_id', $project->id),
            ],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['nullable', 'string', Rule::in(TaskPriority::values())],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'due_date' => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title_ar.required' => 'عنوان المهمة بالعربية مطلوب',
            'phase_id.exists' => 'المرحلة غير تابعة لهذا المشروع',
        ];
    }
}
