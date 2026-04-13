# Requirements Checklist — Invoicing

> **Stage:** Invoicing  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T12:05:00Z

## Functional

- [ ] Auto invoice on order `completed` when none exists
- [ ] Manual invoice create with line items and VAT 15%
- [ ] List/show scoped by customer, supplier, admin
- [ ] PDF download endpoint returns binary PDF
- [ ] Email send endpoint with authorization
- [ ] Void invoice (admin) with state guards

## Technical

- [ ] Migrations `invoices`, `invoice_items` with rollback
- [ ] Repository + service layer; thin controller
- [ ] Form Requests for store, send, void
- [ ] Policies + `role` middleware on all routes
- [ ] ZATCA Phase-1 TLV stored in `zatca_qr_data`
- [ ] Feature tests for RBAC and auto-generation

## Quality

- [ ] PHPUnit feature tests for invoice flows
- [ ] Nuxt pages + composable smoke-tested manually
- [ ] No N+1 on invoice detail (`with([...])`)
