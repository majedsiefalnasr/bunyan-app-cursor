<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Media::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'collection' => ['sometimes', 'nullable', 'string', 'max:64'],
            'mime_type' => ['sometimes', 'nullable', 'string', 'max:128'],
            'temporary' => ['sometimes', 'nullable', 'boolean'],
            'user_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:users,id',
                Rule::prohibitedIf(fn () => $this->user()?->role !== UserRole::Admin),
            ],
        ];
    }
}
