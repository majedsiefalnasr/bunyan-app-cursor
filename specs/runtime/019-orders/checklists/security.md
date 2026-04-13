# Security Checklist — Orders

> **Stage:** Orders  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T15:35:00Z

## Authentication & Authorization

- [ ] All order routes use `auth:sanctum`
- [ ] Customers only mutate own orders unless admin
- [ ] Suppliers only read orders where `supplier_id` matches profile
- [ ] Quotation conversion verifies RFQ ownership and quotation `ACCEPTED`

## Input Validation

- [ ] Form Requests validate quantities, prices, status values
- [ ] Status updates reject unknown enum values and illegal transitions

## Abuse & Integrity

- [ ] Confirm/cancel idempotent-safe under concurrency (DB locks on inventory)
- [ ] Rate limits on create/convert where shared throttles exist
