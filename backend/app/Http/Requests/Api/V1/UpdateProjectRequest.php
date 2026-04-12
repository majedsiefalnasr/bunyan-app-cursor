<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        if (! $project instanceof Project) {
            return false;
        }

        return $this->user()->can('update', $project);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'name_ar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'budget' => ['sometimes', 'numeric', 'min:0'],
            'budget_estimated' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'budget_actual' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'location' => ['sometimes', 'string', 'max:500'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'district' => ['sometimes', 'nullable', 'string', 'max:255'],
            'location_lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'project_type' => ['sometimes', 'nullable', 'in:residential,commercial,infrastructure'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'start_date' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'budget.numeric' => 'الميزانية يجب أن تكون رقم',
            'end_date.date' => 'تاريخ الانتهاء يجب أن يكون تاريخ صحيح',
        ];
    }
}
