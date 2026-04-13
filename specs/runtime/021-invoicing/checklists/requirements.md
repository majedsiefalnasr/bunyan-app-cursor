# Requirements Checklist — Invoicing

> **Stage:** Invoicing  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T12:05:00Z

## Functional

- [x] Auto invoice on order `completed` when none exists
- [x] Manual invoice create with line items and VAT 15%
- [x] List/show scoped by customer, supplier, admin
- [x] PDF download endpoint returns binary PDF
- [x] Email send endpoint with authorization
- [x] Void invoice (admin) with state guards

## Technical

- [x] Migrations `invoices`, `invoice_items` with rollback
- [x] Repository + service layer; thin controller
- [x] Form Requests for store, send, void
- [x] Policies + `role` middleware on all routes
- [x] ZATCA Phase-1 TLV stored in `zatca_qr_data`
- [x] Feature tests for RBAC and auto-generation

## Quality

- [x] PHPUnit feature tests for invoice flows
- [x] Nuxt pages + composable smoke-tested manually
- [x] No N+1 on invoice detail (`with([...])`)
