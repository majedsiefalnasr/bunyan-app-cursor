<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:draft,submitted,approved,rejected'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'حالة التقرير غير صحيحة',
        ];
    }
}
