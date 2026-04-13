<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\InvoiceRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepository $invoices,
        private readonly ZatcaQrPayloadBuilder $zatca,
    ) {
    }

    public function paginateForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->invoices->paginateForActor($user, $filters);
    }

    public function createFromOrderIfMissing(Order $order): ?Invoice
    {
        if ($order->status !== OrderStatus::Completed) {
            return null;
        }

        $orderId = (int) $order->getKey();

        return DB::transaction(function () use ($order, $orderId) {
            if ($this->invoices->findByOrderIdForUpdate($orderId) !== null) {
                return null;
            }

            $order->loadMissing(['items.product', 'customer', 'supplierProfile']);

            if ($order->items->isEmpty()) {
                return null;
            }

            $vatRate = (float) config('invoicing.default_vat_rate', 15);
            $rows = [];
            $subtotal = 0.0;
            foreach ($order->items as $line) {
                $unit = $this->orderItemUnitPrice($line);
                $qty = (int) $line->quantity;
                $lineSub = round($unit * $qty, 2);
                $lineVat = round($lineSub * ($vatRate / 100.0), 2);
                $lineTot = round($lineSub + $lineVat, 2);
                $subtotal += $lineSub;
                $labelAr = $line->product?->name ?? ($line->description ?? 'بند');
                $rows[] = [
                    'description_ar' => $labelAr,
                    'description_en' => $line->product?->name ?? ($line->description ?? 'Item'),
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'vat_rate' => $vatRate,
                    'line_subtotal' => $lineSub,
                    'line_vat' => $lineVat,
                    'line_total' => $lineTot,
                ];
            }

            $subtotal = round($subtotal, 2);
            $vatAmount = round($subtotal * ($vatRate / 100.0), 2);
            $total = round($subtotal + $vatAmount, 2);

            $invoice = $this->persistInvoice(
                orderId: $orderId,
                customerId: (int) $order->customer_id,
                supplierId: $order->supplier_id !== null ? (int) $order->supplier_id : null,
                subtotal: $subtotal,
                vatAmount: $vatAmount,
                vatRate: $vatRate,
                total: $total,
                rows: $rows,
            );

            Log::info('invoice.created_from_order', [
                'action' => 'invoice.created_from_order',
                'invoice_id' => $invoice->id,
                'order_id' => $orderId,
            ]);

            return $invoice;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createManual(User $actor, array $payload): Invoice
    {
        return DB::transaction(function () use ($actor, $payload) {
            $customerId = (int) ($payload['customer_id'] ?? $actor->id);
            if ($actor->role !== UserRole::Admin && $customerId !== (int) $actor->id) {
                throw ValidationException::withMessages([
                    'customer_id' => ['غير مسموح بتعيين عميل آخر'],
                ]);
            }

            $orderId = isset($payload['order_id']) ? (int) $payload['order_id'] : null;
            if ($orderId !== null && $this->invoices->findByOrderIdForUpdate($orderId) !== null) {
                throw ValidationException::withMessages([
                    'order_id' => ['توجد فاتورة مرتبطة بهذا الطلب'],
                ]);
            }

            $defaultRate = (float) config('invoicing.default_vat_rate', 15);
            $rows = [];
            $subtotal = 0.0;
            foreach ($payload['items'] as $item) {
                $rate = (float) ($item['vat_rate'] ?? $defaultRate);
                $unit = (float) $item['unit_price'];
                $qty = (int) $item['quantity'];
                $lineSub = round($unit * $qty, 2);
                $lineVat = round($lineSub * ($rate / 100.0), 2);
                $lineTot = round($lineSub + $lineVat, 2);
                $subtotal += $lineSub;
                $rows[] = [
                    'description_ar' => (string) ($item['description_ar'] ?? ''),
                    'description_en' => (string) ($item['description_en'] ?? ''),
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'vat_rate' => $rate,
                    'line_subtotal' => $lineSub,
                    'line_vat' => $lineVat,
                    'line_total' => $lineTot,
                ];
            }

            $subtotal = round($subtotal, 2);
            $vatAmount = 0.0;
            foreach ($rows as $r) {
                $vatAmount += $r['line_vat'];
            }
            $vatAmount = round($vatAmount, 2);
            $total = round($subtotal + $vatAmount, 2);

            $supplierId = isset($payload['supplier_id']) ? (int) $payload['supplier_id'] : null;

            $headerVatRate = $rows !== [] ? (float) $rows[0]['vat_rate'] : (float) config('invoicing.default_vat_rate', 15);

            $invoice = $this->persistInvoice(
                orderId: $orderId,
                customerId: $customerId,
                supplierId: $supplierId,
                subtotal: $subtotal,
                vatAmount: $vatAmount,
                vatRate: $headerVatRate,
                total: $total,
                rows: $rows,
                notes: $payload['notes'] ?? null,
                dueDate: $payload['due_date'] ?? null,
            );

            Log::info('invoice.created_manual', [
                'action' => 'invoice.created_manual',
                'invoice_id' => $invoice->id,
                'user_id' => $actor->id,
            ]);

            return $invoice;
        });
    }

    public function voidInvoice(User $actor, Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($actor, $invoice) {
            $locked = $this->invoices->lockByIdForUpdate((int) $invoice->getKey());

            if ($locked->status === InvoiceStatus::Void) {
                throw ValidationException::withMessages([
                    'status' => ['الفاتورة ملغاة بالفعل'],
                ]);
            }

            if ($locked->status === InvoiceStatus::Paid) {
                throw ValidationException::withMessages([
                    'status' => ['لا يمكن إلغاء فاتورة مدفوعة'],
                ]);
            }

            $locked->status = InvoiceStatus::Void;
            $locked->save();

            Log::info('invoice.voided', [
                'action' => 'invoice.voided',
                'invoice_id' => $locked->id,
                'user_id' => $actor->id,
            ]);

            return $locked->fresh(['items', 'order', 'customer', 'supplierProfile']) ?? $locked;
        });
    }

    public function sendInvoice(User $actor, Invoice $invoice): Invoice
    {
        $invoice->loadMissing(['customer', 'items']);

        Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice));

        if ($invoice->status === InvoiceStatus::Draft) {
            $invoice->status = InvoiceStatus::Sent;
            $invoice->save();
        }

        Log::info('invoice.sent', [
            'action' => 'invoice.sent',
            'invoice_id' => $invoice->id,
            'user_id' => $actor->id,
        ]);

        return $invoice->fresh(['items', 'order', 'customer', 'supplierProfile']) ?? $invoice;
    }

    public function renderPdfBinary(Invoice $invoice): string
    {
        $invoice->loadMissing(['items', 'customer', 'order', 'supplierProfile']);

        $qrPng = $this->buildQrPngDataUri((string) $invoice->zatca_qr_data);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'qrDataUri' => $qrPng,
        ])->setPaper('a4');

        $binary = $pdf->output();

        Log::info('invoice.pdf_generated', [
            'action' => 'invoice.pdf_generated',
            'invoice_id' => $invoice->id,
        ]);

        return $binary;
    }

    private function buildQrPngDataUri(string $base64TlvPayload): string
    {
        $result = Builder::create()
            ->writer(new PngWriter)
            ->data($base64TlvPayload)
            ->size(160)
            ->margin(8)
            ->build();

        return 'data:'.$result->getMimeType().';base64,'.base64_encode($result->getString());
    }

    /**
     * @param  array<int, array{description_ar: string, description_en: string, quantity: int, unit_price: float, vat_rate: float, line_subtotal: float, line_vat: float, line_total: float}>  $rows
     */
    private function persistInvoice(
        ?int $orderId,
        int $customerId,
        ?int $supplierId,
        float $subtotal,
        float $vatAmount,
        float $vatRate,
        float $total,
        array $rows,
        ?string $notes = null,
        mixed $dueDate = null,
    ): Invoice {
        $ymd = now()->format('Ymd');
        $next = $this->invoices->nextDisplaySequenceForDate($ymd);
        $suffix = str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        $invoiceNumber = 'INV-'.$ymd.'-'.$suffix;

        $due = $dueDate !== null && $dueDate !== ''
            ? Carbon::parse((string) $dueDate)->toDateString()
            : now()->addDays((int) config('invoicing.due_days', 14))->toDateString();

        $seller = (string) config('invoicing.seller_name', 'Bunyan');
        $vatNo = (string) config('invoicing.vat_number', '300000000000003');
        $timestamp = now()->toIso8601String();
        $qr = $this->zatca->buildBase64(
            $seller,
            $vatNo,
            $timestamp,
            number_format($total, 2, '.', ''),
            number_format($vatAmount, 2, '.', ''),
        );

        /** @var Invoice $invoice */
        $invoice = $this->invoices->createInvoice([
            'invoice_number' => $invoiceNumber,
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'supplier_id' => $supplierId,
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'vat_percentage' => $vatRate,
            'total' => $total,
            'status' => InvoiceStatus::Draft,
            'due_date' => $due,
            'paid_at' => null,
            'zatca_qr_data' => $qr,
            'notes' => $notes,
        ]);

        foreach ($rows as $row) {
            $invoice->items()->create($row);
        }

        return $invoice->fresh(['items', 'order', 'customer', 'supplierProfile']) ?? $invoice;
    }

    private function orderItemUnitPrice(OrderItem $item): float
    {
        $u = $item->unit_price ?? null;
        if ($u !== null && (float) $u > 0) {
            return (float) $u;
        }

        return (float) ($item->price ?? 0);
    }
}
