# Requirements Checklist — Orders

> **Stage:** Orders  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T15:35:00Z

## Functional

- [x] Order create from line items with persisted totals
- [x] Unique `order_number` generation per business rules
- [x] Confirm transitions reserve inventory; cancel releases
- [x] Convert accepted quotation to order with linkage fields
- [x] Role-scoped listing (customer / supplier / admin)
- [x] Status timeline data exposed on order detail API

## Technical

- [x] Service layer for order lifecycle; controllers thin
- [x] Repositories for persistence where bulk queries needed
- [x] Form Requests for all mutations
- [x] Policies + middleware on all routes
- [x] Forward-only migrations with `down()`
- [x] Feature tests for RBAC and transitions

## Quality

- [x] PHPUnit feature coverage for new endpoints
- [x] Vitest or smoke test for composable if logic-heavy
- [x] No N+1 on order detail (`with([...])`)
