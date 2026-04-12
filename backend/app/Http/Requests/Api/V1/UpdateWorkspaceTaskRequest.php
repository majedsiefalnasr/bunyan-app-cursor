<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkspaceTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'title_ar' => ['sometimes', 'string', 'max:255'],
            'title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'budget' => ['sometimes', 'numeric', 'min:0'],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'estimated_hours' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'actual_hours' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'priority' => ['sometimes', 'string', Rule::in(TaskPriority::values())],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', Rule::in(TaskStatus::values())],
            'phase_id' => ['sometimes', 'integer', 'exists:phases,id'],
        ];
    }
}
