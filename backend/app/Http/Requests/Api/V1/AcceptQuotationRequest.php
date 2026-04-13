<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Quotation;
use App\Models\Rfq;
use Illuminate\Foundation\Http\FormRequest;

class AcceptQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Rfq $rfq */
        $rfq = $this->route('rfq');
        /** @var Quotation $quotation */
        $quotation = $this->route('quotation');

        return $this->user()->can('acceptQuotation', [$rfq, $quotation]);
    }

    public function rules(): array
    {
        return [];
    }
}
