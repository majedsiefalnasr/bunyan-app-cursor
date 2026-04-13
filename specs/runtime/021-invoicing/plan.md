# Technical Plan — Invoicing (STAGE_21)

## Architecture

- **Stack:** Laravel 11 API + Nuxt 3 + Sanctum; RBAC via `role:` middleware + policies.
- **Layers:** Controllers → Services → Repositories → Models (no Eloquent in services).

### Backend Touchpoints

- **Config:** `config/invoicing.php` — seller display name, VAT registration number, default VAT rate (15%), default due days.
- **Dependency:** `barryvdh/laravel-dompdf` for PDF stream from Blade view `resources/views/invoices/pdf.blade.php`.
- **Service:** `App\Services\InvoiceService` — numbering `INV-YYYYMMDD-XXXX`, line totals + VAT, ZATCA TLV base64 via `App\Services\ZatcaQrPayloadBuilder`, create from order, manual create, void, mark paid, send mail.
- **Repository:** `App\Repositories\InvoiceRepository` — pagination for actor, `existsForOrder`, `nextSequenceForDate`, `lockForUpdate` where needed.
- **Controller:** `App\Http\Controllers\Api\V1\InvoiceController` — index, store, show, pdf, send, void.
- **Policy:** `InvoicePolicy` — viewAny/view/create/update (void)/send/pdf aligned with customer/supplier/admin matrix.
- **Form Requests:** `StoreInvoiceRequest`, `SendInvoiceRequest`, `VoidInvoiceRequest` (or single action requests).
- **Resources:** `InvoiceResource`, `InvoiceItemResource`.
- **Enum:** `InvoiceStatus`.
- **Order integration:** extend `OrderService::transitionStatus` to call `InvoiceService::ensureFromCompletedOrder` inside the same transaction after persisting `completed`.

### Frontend

- `frontend/composables/useInvoices.ts`
- `frontend/pages/invoices/index.vue`, `[id].vue`, `create.vue`
- `frontend/i18n/locales/ar.json`, `en.json` — `invoice.*`
- Optional nav link from dashboard for customer/admin.

## Database

New migration `create_invoices_tables` (timestamp at implementation):

**`invoices`**

- `id`, `invoice_number` (string, unique), `order_id` nullable FK → `orders` restrict, `customer_id` FK → `users`, `supplier_id` nullable FK → `supplier_profiles`, decimals `subtotal`, `vat_amount`, `vat_percentage`, `total`, `status` string, `due_date` date nullable, `paid_at` nullable timestamp, `zatca_qr_data` text nullable, `notes` text nullable, timestamps, softDeletes.

**`invoice_items`**

- `id`, `invoice_id` FK cascade, `description_ar`, `description_en` nullable strings, `quantity` unsigned int, `unit_price` decimal(15,2), `vat_rate` decimal(5,2) default 15.00, `line_subtotal`, `line_vat`, `line_total` decimals (denormalized for immutability).

Indexes: `customer_id`, `supplier_id`, `order_id`, `status`, `created_at`.

## API Surface

See `contracts/api.md`.

## ZATCA Phase-1 QR

TLV tags (1 seller name, 2 VAT reg, 3 ISO8601 timestamp, 4 invoice total incl VAT, 5 VAT amount) → concatenated TLV → Base64 string stored in `zatca_qr_data` and embedded in PDF.

## Logging

`Log::channel('audit')` or `Log::info` with `action` keys for `invoice.created`, `invoice.voided`, `invoice.sent`, `invoice.pdf_generated`.

## Testing

Feature tests: RBAC; auto invoice on order completion; PDF route; void guards.

## Risks

Double invoice on concurrent completion — mitigate with unique partial index on `order_id` where deleted_at null + repository existence check inside transaction with order row lock.
