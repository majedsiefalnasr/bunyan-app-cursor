<?php

namespace Tests\Feature\Payments;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['payments.webhook_secret' => 'test-webhook-secret']);
    }

    public function test_customer_initiate_capture_refund_flow(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total_amount' => 100.50,
        ]);

        $init = $this->actingAs($user)->postJson('/api/v1/payments/initiate', [
            'payable_type' => 'order',
            'payable_id' => $order->id,
            'method' => 'mada',
        ]);

        $init->assertStatus(201)->assertJson(['success' => true]);
        $paymentId = (int) $init->json('data.id');
        $this->assertGreaterThan(0, $paymentId);

        $capture = $this->actingAs($user)->postJson("/api/v1/payments/{$paymentId}/capture");
        $capture->assertStatus(200)->assertJsonPath('data.status', 'completed');

        $refund = $this->actingAs($user)->postJson("/api/v1/payments/{$paymentId}/refund", []);
        $refund->assertStatus(200)->assertJsonPath('data.status', 'refunded');
    }

    public function test_payment_history_lists_own_payments(): void
    {
        $user = User::factory()->create();
        Payment::query()->create([
            'payable_type' => Order::class,
            'payable_id' => Order::factory()->create(['customer_id' => $user->id])->id,
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'SAR',
            'method' => 'mada',
            'status' => 'completed',
            'gateway_reference' => 'gw_1',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/payments/history');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_webhook_updates_payment_with_valid_secret(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['customer_id' => $user->id, 'total_amount' => 50]);
        $payment = Payment::query()->create([
            'payable_type' => Order::class,
            'payable_id' => $order->id,
            'user_id' => $user->id,
            'amount' => 50,
            'currency' => 'SAR',
            'method' => 'card',
            'status' => 'processing',
            'gateway_reference' => 'gw_hook',
        ]);

        $response = $this->postJson('/api/v1/webhooks/payment', [
            'gateway_reference' => 'gw_hook',
            'status' => 'completed',
        ], [
            'X-Payment-Webhook-Secret' => 'test-webhook-secret',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.status', 'completed');
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_webhook_rejects_invalid_secret(): void
    {
        $response = $this->postJson('/api/v1/webhooks/payment', [
            'gateway_reference' => 'x',
            'status' => 'completed',
        ], [
            'X-Payment-Webhook-Secret' => 'wrong',
        ]);

        $response->assertStatus(403);
    }
}
