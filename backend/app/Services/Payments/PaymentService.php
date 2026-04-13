<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayContract;
use App\Enums\OrderStatus;
use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentAttemptType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentAttemptRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private PaymentRepository $payments,
        private PaymentAttemptRepository $attempts,
        private OrderRepository $orders,
        private PaymentGatewayContract $gateway,
    ) {
    }

    public function initiate(User $user, string $payableType, int $payableId, PaymentMethod $method): Payment
    {
        if ($payableType !== 'order') {
            throw new \InvalidArgumentException(__('payments.errors.unsupported_payable'));
        }

        /** @var Order $order */
        $order = $this->orders->findByIdOrFail($payableId);
        if ($user->role !== UserRole::Admin && (int) $order->customer_id !== (int) $user->id) {
            throw new \InvalidArgumentException(__('payments.errors.order_not_owned'));
        }
        if ($order->status !== OrderStatus::Pending) {
            throw new \InvalidArgumentException(__('payments.errors.order_not_payable'));
        }

        /** @var Payment $payment */
        $payment = $this->payments->create([
            'payable_type' => Order::class,
            'payable_id' => $order->id,
            'user_id' => $order->customer_id,
            'amount' => $order->total_amount,
            'currency' => 'SAR',
            'method' => $method,
            'status' => PaymentStatus::Pending,
        ]);

        $charge = $this->gateway->startCharge($payment);

        $this->attempts->record($payment, [
            'type' => PaymentAttemptType::Charge,
            'amount' => $payment->amount,
            'status' => PaymentAttemptStatus::Succeeded,
            'gateway_id' => $charge['gateway_reference'],
            'gateway_response' => $charge['response'],
        ]);

        $this->payments->updatePayment($payment, [
            'status' => PaymentStatus::Processing,
            'gateway_reference' => $charge['gateway_reference'],
        ]);

        Log::info('payment.initiated', [
            'action' => 'payment.initiated',
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => (string) $payment->amount,
        ]);

        $payment->refresh();

        return $payment->load(['attempts', 'payable']);
    }

    public function capture(User $user, Payment $payment): Payment
    {
        $this->assertOwner($user, $payment);
        if ($payment->status !== PaymentStatus::Processing) {
            throw new \InvalidArgumentException(__('payments.errors.invalid_capture_state'));
        }

        $response = $this->gateway->capture($payment);

        $this->attempts->record($payment, [
            'type' => PaymentAttemptType::Charge,
            'amount' => $payment->amount,
            'status' => PaymentAttemptStatus::Succeeded,
            'gateway_id' => $payment->gateway_reference,
            'gateway_response' => $response['response'],
        ]);

        $this->payments->updatePayment($payment, [
            'status' => PaymentStatus::Completed,
            'paid_at' => now(),
        ]);

        Log::info('payment.captured', [
            'action' => 'payment.captured',
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'amount' => (string) $payment->amount,
        ]);

        $payment->refresh();

        return $payment->load(['attempts', 'payable']);
    }

    public function refund(User $user, Payment $payment, ?string $amount = null): Payment
    {
        $this->assertOwner($user, $payment);
        if ($payment->status !== PaymentStatus::Completed) {
            throw new \InvalidArgumentException(__('payments.errors.invalid_refund_state'));
        }

        $refundAmount = $amount ?? (string) $payment->amount;
        if (bccomp($refundAmount, (string) $payment->amount, 2) === 1) {
            throw new \InvalidArgumentException(__('payments.errors.refund_exceeds_payment'));
        }

        $gw = $this->gateway->refund($payment, $refundAmount);

        $this->attempts->record($payment, [
            'type' => PaymentAttemptType::Refund,
            'amount' => $refundAmount,
            'status' => PaymentAttemptStatus::Succeeded,
            'gateway_id' => $gw['gateway_id'],
            'gateway_response' => $gw['response'],
        ]);

        $this->payments->updatePayment($payment, [
            'status' => PaymentStatus::Refunded,
        ]);

        Log::info('payment.refunded', [
            'action' => 'payment.refunded',
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'amount' => $refundAmount,
        ]);

        $payment->refresh();

        return $payment->load(['attempts', 'payable']);
    }

    /**
     * @param  array{gateway_reference: string, status: string}  $payload
     */
    public function applyWebhookPayload(array $payload): Payment
    {
        $reference = $payload['gateway_reference'];
        $payment = $this->payments->findByGatewayReference($reference);
        if ($payment === null) {
            throw new \InvalidArgumentException(__('payments.errors.payment_not_found'));
        }

        $status = strtolower($payload['status']);
        $newStatus = match ($status) {
            'completed' => PaymentStatus::Completed,
            'failed' => PaymentStatus::Failed,
            default => throw new \InvalidArgumentException(__('payments.errors.invalid_webhook_status')),
        };

        $this->attempts->record($payment, [
            'type' => PaymentAttemptType::Charge,
            'amount' => $payment->amount,
            'status' => PaymentAttemptStatus::Succeeded,
            'gateway_id' => $reference,
            'gateway_response' => ['webhook' => true, 'status' => $status],
        ]);

        $updates = ['status' => $newStatus];
        if ($newStatus === PaymentStatus::Completed) {
            $updates['paid_at'] = now();
        }

        $this->payments->updatePayment($payment, $updates);

        Log::info('payment.webhook_applied', [
            'action' => 'payment.webhook_applied',
            'payment_id' => $payment->id,
            'status' => $newStatus->value,
        ]);

        $payment->refresh();

        return $payment->load(['attempts', 'payable']);
    }

    private function assertOwner(User $user, Payment $payment): void
    {
        if ($user->role === UserRole::Admin) {
            return;
        }
        if ((int) $payment->user_id !== (int) $user->id) {
            throw new \InvalidArgumentException(__('payments.errors.not_owner'));
        }
    }
}
