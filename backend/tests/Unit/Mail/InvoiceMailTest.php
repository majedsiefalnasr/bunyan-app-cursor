<?php

namespace Tests\Unit\Mail;

use App\Enums\InvoiceStatus;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_envelope_subject_includes_invoice_number(): void
    {
        $customer = User::factory()->create();

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-20260101-0999',
            'order_id' => null,
            'customer_id' => $customer->id,
            'supplier_id' => null,
            'subtotal' => 10,
            'vat_amount' => 1.5,
            'vat_percentage' => 15,
            'total' => 11.5,
            'status' => InvoiceStatus::Draft,
            'due_date' => now()->addDays(7)->toDateString(),
            'zatca_qr_data' => 'dGVzdA==',
            'notes' => null,
        ]);

        $mail = new InvoiceMail($invoice);

        $this->assertStringContainsString('INV-20260101-0999', $mail->envelope()->subject);
    }
}
