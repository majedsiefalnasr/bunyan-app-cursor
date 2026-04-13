<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Rfq;
use Illuminate\Foundation\Http\FormRequest;

class SendRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Rfq $rfq */
        $rfq = $this->route('rfq');

        return $this->user()->can('send', $rfq);
    }

    public function rules(): array
    {
        return [
            'response_deadline' => ['nullable', 'date', 'after:now'],
        ];
    }
}
