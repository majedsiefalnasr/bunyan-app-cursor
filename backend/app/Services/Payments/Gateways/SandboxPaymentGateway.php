<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\PaymentGatewayContract;
use App\Models\Payment;
use Illuminate\Support\Str;

class SandboxPaymentGateway implements PaymentGatewayContract
{
    public function startCharge(Payment $payment): array
    {
        $reference = 'sandbox_'.Str::uuid()->toString();

        return [
            'gateway_reference' => $reference,
            'response' => [
                'mode' => 'sandbox',
                'payment_id' => $payment->id,
            ],
        ];
    }

    public function capture(Payment $payment): array
    {
        return [
            'response' => [
                'mode' => 'sandbox',
                'action' => 'capture',
                'payment_id' => $payment->id,
            ],
        ];
    }

    public function refund(Payment $payment, string $amount): array
    {
        $gatewayId = 'sandbox_refund_'.Str::uuid()->toString();

        return [
            'gateway_id' => $gatewayId,
            'response' => [
                'mode' => 'sandbox',
                'amount' => $amount,
                'payment_id' => $payment->id,
            ],
        ];
    }
}
