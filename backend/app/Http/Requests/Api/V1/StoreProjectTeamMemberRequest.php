<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ProjectRole;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProjectTeamMemberRequest extends FormRequest
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
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'email' => ['sometimes', 'string', 'email', 'max:255'],
            'project_role' => ['required', Rule::enum(ProjectRole::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $hasUserId = $this->filled('user_id');
            $hasEmail = $this->filled('email');

            if ($hasUserId === $hasEmail) {
                $v->errors()->add('user_id', 'يجب إرسال إما user_id أو email (وليس كلاهما معاً)');
            }
        });
    }
}
