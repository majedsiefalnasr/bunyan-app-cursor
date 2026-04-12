<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Admin;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(UserRole::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => __('validation.required', ['attribute' => 'الدور']),
            'role.in' => __('validation.in', ['attribute' => 'الدور']),
        ];
    }
}
