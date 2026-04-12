<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(TaskStatus::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'حالة المهمة غير صحيحة',
        ];
    }
}
