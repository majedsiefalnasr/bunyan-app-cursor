<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRecentActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string|int>>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('per_page')) {
            $this->merge(['per_page' => 15]);
        }
    }
}
