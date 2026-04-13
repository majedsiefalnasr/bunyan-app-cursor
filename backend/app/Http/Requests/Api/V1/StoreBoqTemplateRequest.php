<?php

namespace App\Http\Requests\Api\V1;

use App\Models\BoqTemplate;
use Illuminate\Foundation\Http\FormRequest;

class StoreBoqTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BoqTemplate::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'project_type' => ['nullable', 'string', 'max:255'],
            'items_json' => ['required', 'array'],
        ];
    }
}
