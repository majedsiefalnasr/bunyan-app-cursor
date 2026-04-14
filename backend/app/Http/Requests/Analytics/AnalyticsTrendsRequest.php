<?php

namespace App\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AnalyticsTrendsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('viewAnalytics');
    }

    public function rules(): array
    {
        return [
            'keys' => ['required', 'array', 'max:10'],
            'keys.*' => ['string'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'bucket' => ['nullable', Rule::in(['day', 'week', 'month'])],
        ];
    }
}
