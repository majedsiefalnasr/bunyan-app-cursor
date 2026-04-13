# Requirements Checklist — Orders

> **Stage:** Orders  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T15:35:00Z

## Functional

- [ ] Order create from line items with persisted totals
- [ ] Unique `order_number` generation per business rules
- [ ] Confirm transitions reserve inventory; cancel releases
- [ ] Convert accepted quotation to order with linkage fields
- [ ] Role-scoped listing (customer / supplier / admin)
- [ ] Status timeline data exposed on order detail API

## Technical

- [ ] Service layer for order lifecycle; controllers thin
- [ ] Repositories for persistence where bulk queries needed
- [ ] Form Requests for all mutations
- [ ] Policies + middleware on all routes
- [ ] Forward-only migrations with `down()`
- [ ] Feature tests for RBAC and transitions

## Quality

- [ ] PHPUnit feature coverage for new endpoints
- [ ] Vitest or smoke test for composable if logic-heavy
- [ ] No N+1 on order detail (`with([...])`)
