<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;

class MarkConversationReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Conversation $conversation */
        $conversation = $this->route('conversation');

        return $this->user()->can('markRead', $conversation);
    }

    public function rules(): array
    {
        return [];
    }
}
