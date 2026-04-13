<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Rfq;
use Illuminate\Foundation\Http\FormRequest;

class CloseRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Rfq $rfq */
        $rfq = $this->route('rfq');

        return $this->user()->can('close', $rfq);
    }

    public function rules(): array
    {
        return [];
    }
}
