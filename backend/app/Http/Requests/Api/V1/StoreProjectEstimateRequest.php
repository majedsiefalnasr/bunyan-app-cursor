<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Project;
use App\Policies\EstimatePolicy;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Project $project */
        $project = $this->route('project');
        $user = $this->user();

        return $user !== null && (new EstimatePolicy)->create($user, $project);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'markup_percentage' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان التقدير مطلوب',
        ];
    }
}
