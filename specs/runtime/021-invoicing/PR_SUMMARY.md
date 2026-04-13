# PR — Invoicing

## Summary

**Stage:** Invoicing  
**Phase:** 04_COMMERCIAL_LAYER  
**Branch:** `spec/021-invoicing` → `develop`  
**Tasks:** 20 / 20 completed

## What Changed

### Backend

- Added `invoices` / `invoice_items` tables and Eloquent models with `InvoiceService`, `InvoiceRepository`, and `ZatcaQrPayloadBuilder`.
- Integrated Dompdf + Endroid QR for PDF output; `InvoiceMail` for email send.
- Auto-create invoice when `OrderService` transitions an order to `completed`.
- New API routes under Sanctum + role middleware; `InvoicePolicy` for authorization.
- Feature tests in `tests/Feature/Api/V1/InvoiceApiTest.php`.

### Frontend

- Added `useInvoices` composable and pages under `/invoices`.
- Extended navigation and `ar.json` / `en.json` with `invoice.*` and `nav.invoices`.

### Database

- `2026_04_13_200000_create_invoices_tables.php` — creates `invoices` and `invoice_items` with rollback.

## Breaking Changes

- None.

## Testing

- [x] Full PHPUnit suite (`php artisan test`)
- [x] Invoice feature tests (`php artisan test --filter=InvoiceApiTest`)
- [x] Frontend tests (`npm run test`)
- [x] Lint (`composer run lint`, `npm run lint`)
- [x] Type check (`composer run analyze`, `npm run typecheck`)

## Checklist

- [x] RBAC middleware applied on all new routes
- [x] Form Request validation on all new endpoints
- [x] Arabic/RTL support verified
- [x] Error contract followed
- [x] No N+1 on invoice show (eager loads)
- [x] Migration includes `down()` (pretend against live DB optional)

## Related

- Stage File: `specs/phases/04_COMMERCIAL_LAYER/STAGE_21_INVOICING.md`
- Testing Guide: `specs/runtime/021-invoicing/guides/TESTING_GUIDE.md`
