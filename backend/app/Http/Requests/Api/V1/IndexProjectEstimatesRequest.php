<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class IndexProjectEstimatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Project $project */
        $project = $this->route('project');

        return $this->user()?->can('view', $project) ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
