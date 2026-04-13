<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\SendInvoiceRequest;
use App\Http\Requests\Api\V1\StoreInvoiceRequest;
use App\Http\Requests\Api\V1\VoidInvoiceRequest;
use App\Http\Resources\Api\V1\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends BaseController
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = $this->invoiceService->paginateForUser(
            $request->user(),
            $request->only(['status', 'per_page'])
        );

        return $this->sendSuccess(
            InvoiceResource::collection($invoices),
            'تم جلب الفواتير بنجاح',
            200
        );
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->createManual($request->user(), $request->validated());

        return $this->sendSuccess(
            new InvoiceResource($invoice->load(['items', 'order', 'customer', 'supplierProfile'])),
            'تم إنشاء الفاتورة بنجاح',
            201
        );
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);
        $invoice->load(['items', 'order', 'customer', 'supplierProfile']);

        return $this->sendSuccess(
            new InvoiceResource($invoice),
            'تم جلب الفاتورة بنجاح',
            200
        );
    }

    public function pdf(Invoice $invoice): Response
    {
        $this->authorize('downloadPdf', $invoice);

        $binary = $this->invoiceService->renderPdfBinary($invoice);

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }

    public function send(SendInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->sendInvoice($request->user(), $invoice);

        return $this->sendSuccess(
            new InvoiceResource($invoice->load(['items', 'order', 'customer', 'supplierProfile'])),
            'تم إرسال الفاتورة بنجاح',
            200
        );
    }

    public function void(VoidInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->voidInvoice($request->user(), $invoice);

        return $this->sendSuccess(
            new InvoiceResource($invoice->load(['items', 'order', 'customer', 'supplierProfile'])),
            'تم إلغاء الفاتورة بنجاح',
            200
        );
    }
}
