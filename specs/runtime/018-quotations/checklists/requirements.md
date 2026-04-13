# Requirements Checklist — Quotations

> **Stage:** Quotations  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T09:43:25Z

## Backend

- [ ] RFQ model exists with required fields
- [ ] RFQ item model exists and links to RFQ
- [ ] Quotation model exists and links to RFQ + supplier
- [ ] Quotation item model exists and links to quotation + rfq_item
- [ ] Forward-only migrations created with rollbacks
- [ ] All API routes under `/api/v1/` and protected by `auth:sanctum`
- [ ] All controller actions authorize via Policies
- [ ] All inputs validated via Form Requests
- [ ] Services contain business logic; controllers remain thin
- [ ] Repositories contain Eloquent queries; no direct Eloquent in services/controllers
- [ ] Error contract enforced across endpoints

## RFQ Workflow

- [ ] RFQ status transitions enforced server-side
- [ ] RFQ items immutable after send
- [ ] Response deadline enforced for quotation submission/revision
- [ ] Award flow enforces single accepted quotation and rejects others
- [ ] Close flow forbids further changes/submissions

## Frontend

- [ ] Customer RFQ create/edit draft form (RTL)
- [ ] Customer RFQ list/filter by status
- [ ] Customer RFQ details page shows items + received quotations
- [ ] Supplier RFQ inbox/list
- [ ] Supplier quotation submit + revise flow
- [ ] Customer quotation comparison table
- [ ] Award UI for customer

## Quality

- [ ] Feature tests for RBAC matrix (authorized/unauthenticated/unauthorized)
- [ ] Unit tests for service rules (transitions, award)
- [ ] No N+1 queries on list/detail endpoints
- [ ] Arabic strings via translation keys (no hard-coded UI text)
