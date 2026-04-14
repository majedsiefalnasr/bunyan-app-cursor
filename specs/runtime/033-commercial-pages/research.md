# Commercial Pages — Research Notes

> **Generated:** 2026-04-14T08:59:00Z

## Existing Frontend Integration (Confirmed)

The repo already contains composables for the commercial domain, indicating the intended API surface:

- RFQs: `frontend/composables/useRfqs.ts` → `/v1/rfqs/*`
- Orders: `frontend/composables/useOrders.ts` → `/v1/orders/*`
- Payments: `frontend/composables/usePayments.ts` → `/v1/payments/*`
- Invoices: `frontend/composables/useInvoices.ts` → `/v1/invoices/*` (+ `/pdf` download)

## Route Naming Mismatches

Stage file names routes slightly differently than existing pages:

- Existing: `/rfqs/new` vs stage: `/rfqs/create`
- Existing: `/payments/checkout` vs stage: `/checkout`
- Existing: `/payments/:id` vs stage: `/payment/:orderId`

Plan: implement lightweight aliases/redirects to preserve existing routes and meet stage expectations.

## Invoice Details (ZATCA)

`InvoiceDetail` includes `vat_amount`, `vat_percentage`, and `zatca_qr_data`, so the UI should render:

- VAT breakdown
- QR block when `zatca_qr_data` exists (and hide gracefully when null)
