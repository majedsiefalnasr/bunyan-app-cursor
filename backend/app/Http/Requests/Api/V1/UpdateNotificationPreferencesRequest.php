<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'preferences' => ['required', 'array', 'min:1'],
            'preferences.*.type' => ['required', 'string', Rule::enum(NotificationType::class)],
            'preferences.*.email_enabled' => ['sometimes', 'boolean'],
            'preferences.*.sms_enabled' => ['sometimes', 'boolean'],
            'preferences.*.push_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
