# Security Checklist — Quotations

> **Stage:** Quotations  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T09:47:15Z

## Authentication & Authorization

- [ ] All endpoints protected with `auth:sanctum` (unless explicitly public)
- [ ] Policies enforced for every RFQ/quotation action
- [ ] Supplier can only access RFQs they are eligible to quote on
- [ ] Supplier can only access/modify their own quotation records
- [ ] Admin access is explicitly scoped (read-only unless elevated)

## Input Validation

- [ ] Form Requests validate all RFQ fields and items (lengths, types, ranges)
- [ ] Form Requests validate quotation items pricing and totals consistency
- [ ] Deadlines validated (response_deadline >= now on send, delivery_deadline rules)
- [ ] Prevent integer overflows / negative quantities / negative prices

## Abuse Prevention

- [ ] Rate limit RFQ send endpoint
- [ ] Rate limit quotation submit/revise endpoints
- [ ] Audit logging for send/award/close actions

## Data Safety

- [ ] Award operation is transactional
- [ ] No raw SQL with user input
- [ ] File uploads (if any later) must validate MIME/size (out-of-scope for stage)
