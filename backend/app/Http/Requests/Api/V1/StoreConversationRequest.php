<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Conversation::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:direct,group'],
            'title' => ['nullable', 'string', 'max:255', 'required_if:type,group'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'نوع المحادثة مطلوب',
            'participant_ids.required' => 'المشاركون مطلوبون',
            'title.required_if' => 'عنوان المجموعة مطلوب',
        ];
    }
}
