<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;

class SendConversationMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Conversation $conversation */
        $conversation = $this->route('conversation');

        return $this->user()->can('send', $conversation);
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:10000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.max' => 'حجم الملف يتجاوز 5 ميجابايت',
            'attachment.mimes' => 'نوع الملف غير مسموح',
        ];
    }
}
