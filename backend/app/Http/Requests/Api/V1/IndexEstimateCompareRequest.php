<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Project;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class IndexEstimateCompareRequest extends FormRequest
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
            'ids' => ['required', 'string', 'regex:/^\d+(,\d+)*$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'معرفات التقديرات مطلوبة',
        ];
    }

    /**
     * @return list<int>
     */
    public function estimateIds(): array
    {
        $raw = (string) $this->validated('ids');
        $parts = array_values(array_unique(array_filter(array_map('intval', explode(',', $raw)))));

        return $parts;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $raw = (string) $this->input('ids', '');
            $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $raw)))));
            if (count($ids) < 2 || count($ids) > 5) {
                $v->errors()->add('ids', 'يجب إرسال بين 2 و 5 معرفات تقدير صالحة');
            }
        });
    }
}
