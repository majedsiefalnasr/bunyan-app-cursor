<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class VoidInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice instanceof Invoice
            && ($this->user()?->can('void', $invoice) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
