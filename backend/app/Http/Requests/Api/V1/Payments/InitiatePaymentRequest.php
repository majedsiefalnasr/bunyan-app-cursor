<?php

namespace App\Http\Requests\Api\V1\Payments;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = Order::query()->find((int) $this->input('payable_id'));

        return $order !== null && $this->user()->can('pay', $order);
    }

    public function rules(): array
    {
        return [
            'payable_type' => ['required', 'string', 'in:order'],
            'payable_id' => ['required', 'integer', 'exists:orders,id'],
            'method' => ['required', 'string', 'in:card,mada,bank_transfer'],
        ];
    }
}
