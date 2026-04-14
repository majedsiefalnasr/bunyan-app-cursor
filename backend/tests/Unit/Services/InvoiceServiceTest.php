<?php

namespace Tests\Unit\Services;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(InvoiceService::class);
    }

    public function test_create_from_order_returns_null_if_not_completed(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending->value,
        ]);

        $this->assertNull($this->service->createFromOrderIfMissing($order));
    }

    public function test_create_from_order_returns_null_if_no_items(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::Completed->value,
        ]);

        $this->assertNull($this->service->createFromOrderIfMissing($order));
    }

    public function test_create_from_order_creates_invoice_once(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['name' => 'اسمنت']);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Completed->value,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 2,
            'unit_price' => 50.0,
            'subtotal' => 100.0,
            'description' => null,
        ]);

        $invoice = $this->service->createFromOrderIfMissing($order);
        $this->assertNotNull($invoice);
        $this->assertSame($order->id, $invoice->order_id);
        $this->assertSame($customer->id, $invoice->customer_id);
        $this->assertCount(1, $invoice->items);
        $this->assertNotEmpty((string) $invoice->zatca_qr_data);

        $this->assertNull($this->service->createFromOrderIfMissing($order->fresh()));
    }

    public function test_create_manual_rejects_setting_another_customer_for_non_admin(): void
    {
        $actor = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $this->expectException(ValidationException::class);

        $this->service->createManual($actor, [
            'customer_id' => $otherCustomer->id,
            'items' => [
                [
                    'description_ar' => 'بند',
                    'description_en' => 'Item',
                    'quantity' => 1,
                    'unit_price' => 10,
                ],
            ],
        ]);
    }

    public function test_void_invoice_transitions_to_void_and_blocks_paid(): void
    {
        $actor = User::factory()->create(['role' => 'admin']);

        $draft = Invoice::query()->create([
            'invoice_number' => 'INV-20260101-0001',
            'order_id' => null,
            'customer_id' => $actor->id,
            'supplier_id' => null,
            'subtotal' => 0,
            'vat_amount' => 0,
            'vat_percentage' => 15,
            'total' => 0,
            'status' => InvoiceStatus::Draft,
            'due_date' => now()->toDateString(),
            'paid_at' => null,
            'zatca_qr_data' => 'qr',
            'notes' => null,
        ]);

        $voided = $this->service->voidInvoice($actor, $draft);
        $this->assertSame(InvoiceStatus::Void, $voided->status);

        $paid = $draft->fresh();
        $paid->status = InvoiceStatus::Paid;
        $paid->save();

        $this->expectException(ValidationException::class);
        $this->service->voidInvoice($actor, $paid);
    }

    public function test_send_invoice_sends_email_and_marks_as_sent(): void
    {
        Mail::fake();

        $customer = User::factory()->create(['role' => 'customer', 'email' => 'customer@example.test']);
        $actor = User::factory()->create(['role' => 'admin']);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-20260101-0002',
            'order_id' => null,
            'customer_id' => $customer->id,
            'supplier_id' => null,
            'subtotal' => 10,
            'vat_amount' => 1.5,
            'vat_percentage' => 15,
            'total' => 11.5,
            'status' => InvoiceStatus::Draft,
            'due_date' => now()->toDateString(),
            'paid_at' => null,
            'zatca_qr_data' => 'qr',
            'notes' => null,
        ]);

        $sent = $this->service->sendInvoice($actor, $invoice);
        $this->assertSame(InvoiceStatus::Sent, $sent->status);
    }
}
