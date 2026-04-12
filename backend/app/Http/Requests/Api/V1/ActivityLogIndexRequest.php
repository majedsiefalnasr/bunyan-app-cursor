<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ActivityLogAction;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivityLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Admin;
    }

    public function rules(): array
    {
        $actions = array_map(static fn (ActivityLogAction $a) => $a->value, ActivityLogAction::cases());

        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'action' => ['sometimes', 'nullable', 'string', Rule::in($actions)],
            'subject_type' => ['sometimes', 'nullable', 'string', Rule::in([Project::class, Order::class])],
            'subject_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'created_from' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'created_to' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }
}
