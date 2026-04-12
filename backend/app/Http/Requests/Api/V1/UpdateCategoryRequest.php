<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Admin;
    }

    public function rules(): array
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        return [
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:120',
                Rule::unique('categories', 'slug')->ignore($category?->id),
            ],
            'icon' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
