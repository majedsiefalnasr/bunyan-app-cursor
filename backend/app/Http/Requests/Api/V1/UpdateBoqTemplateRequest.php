<?php

namespace App\Http\Requests\Api\V1;

use App\Models\BoqTemplate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBoqTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var BoqTemplate $boqTemplate */
        $boqTemplate = $this->route('boq_template');

        return $this->user()?->can('update', $boqTemplate) ?? false;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['sometimes', 'string', 'max:255'],
            'project_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'items_json' => ['sometimes', 'array'],
        ];
    }
}
