<?php

namespace Tests\Feature\Api\V1;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_created_when_order_completed(): void
    {
        Mail::fake();

        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Delivered,
            'subtotal' => 100,
            'total_amount' => 100,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50,
            'subtotal' => 100,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/v1/orders/{$order->id}/status", [
                'status' => OrderStatus::Completed->value,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
            'customer_id' => $customer->id,
        ]);

        $this->actingAs($customer)
            ->getJson('/api/v1/invoices')
            ->assertStatus(200)
            ->assertJsonPath('data.0.order_id', $order->id);
    }

    public function test_customer_cannot_view_other_invoice(): void
    {
        $a = User::factory()->customer()->create();
        $b = User::factory()->customer()->create();

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-20260101-0001',
            'order_id' => null,
            'customer_id' => $b->id,
            'supplier_id' => null,
            'subtotal' => 10,
            'vat_amount' => 1.5,
            'vat_percentage' => 15,
            'total' => 11.5,
            'status' => InvoiceStatus::Draft->value,
            'due_date' => now()->addDays(7)->toDateString(),
            'zatca_qr_data' => 'dGVzdA==',
            'notes' => null,
        ]);

        $this->actingAs($a)
            ->getJson("/api/v1/invoices/{$invoice->id}")
            ->assertStatus(403);
    }

    public function test_customer_can_create_manual_invoice(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)
            ->postJson('/api/v1/invoices', [
                'items' => [
                    [
                        'description_ar' => 'خدمة',
                        'quantity' => 1,
                        'unit_price' => 200,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
        ]);
    }

    public function test_admin_can_void_invoice(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-20260101-0002',
            'order_id' => null,
            'customer_id' => $customer->id,
            'supplier_id' => null,
            'subtotal' => 10,
            'vat_amount' => 1.5,
            'vat_percentage' => 15,
            'total' => 11.5,
            'status' => InvoiceStatus::Draft->value,
            'due_date' => now()->addDays(7)->toDateString(),
            'zatca_qr_data' => 'dGVzdA==',
            'notes' => null,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/v1/invoices/{$invoice->id}/void")
            ->assertStatus(200)
            ->assertJsonPath('data.status', InvoiceStatus::Void->value);
    }
}
