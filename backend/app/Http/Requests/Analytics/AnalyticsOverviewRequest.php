<?php

namespace App\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AnalyticsOverviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('viewAnalytics');
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'bucket' => ['nullable', Rule::in(['day', 'week', 'month'])],
            'compare' => ['nullable', Rule::in(['none', 'previous_period', 'previous_year'])],
        ];
    }
}
