<?php

namespace App\Contracts\Payments;

use App\Models\Payment;

interface PaymentGatewayContract
{
    /**
     * @return array{gateway_reference: string, response: array<string, mixed>}
     */
    public function startCharge(Payment $payment): array;

    /**
     * @return array{response: array<string, mixed>}
     */
    public function capture(Payment $payment): array;

    /**
     * @return array{gateway_id: string, response: array<string, mixed>}
     */
    public function refund(Payment $payment, string $amount): array;
}
