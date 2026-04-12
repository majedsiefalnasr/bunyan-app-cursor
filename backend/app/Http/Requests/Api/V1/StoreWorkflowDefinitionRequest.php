<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use App\Enums\WorkflowType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Admin;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_global')) {
            $this->merge([
                'is_global' => filter_var($this->input('is_global'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(WorkflowType::class)],
            'is_global' => ['required', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'project_id' => ['nullable', 'required_if:is_global,false', 'exists:projects,id'],
            'status_transitions' => ['nullable', 'array'],
            'approval_requirements' => ['nullable', 'array'],
        ];
    }
}
