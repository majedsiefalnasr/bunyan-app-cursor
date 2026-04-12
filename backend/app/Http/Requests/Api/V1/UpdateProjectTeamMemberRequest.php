<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ProjectRole;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        if (! $project instanceof Project) {
            return false;
        }

        return $this->user()?->can('manageTeam', $project) ?? false;
    }

    public function rules(): array
    {
        return [
            'project_role' => ['required', Rule::enum(ProjectRole::class)],
        ];
    }
}
