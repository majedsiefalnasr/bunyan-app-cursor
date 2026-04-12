<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionProjectStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        if (! $project instanceof Project) {
            return false;
        }

        return $this->user()->can('transitionStatus', $project);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(ProjectStatus::values())],
        ];
    }
}
