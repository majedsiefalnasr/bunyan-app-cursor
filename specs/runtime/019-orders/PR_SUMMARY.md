# PR — Orders

## Summary

**Stage:** Orders  
**Phase:** 04_COMMERCIAL_LAYER  
**Branch:** `spec/019-orders` → `develop`  
**Tasks:** 18 / 18 completed

## What Changed

### Backend

- Added `OrderService` and extended `InventoryService` for order-line reservations.
- Migration extending `orders` and `order_items` with commercial fields and FKs.
- Split order routes: read access for customer/contractor/admin; mutations for customer/admin; admin status updates; `POST quotations/{quotation}/to-order`.
- Expanded `OrderPolicy` / `QuotationPolicy`, `OrderResource`, repositories, enums, and tests.

### Frontend

- `useOrders` composable, `/orders` and `/orders/[id]` pages (Nuxt UI), nav link, `ar`/`en` strings.

### Database

- `2026_04_13_161500_extend_orders_for_stage_19.php`

## Breaking Changes

- None for public API shape beyond additive fields; `OrderPolicy::viewAny` now allows customers (aligned with list endpoint).

## Testing

- [x] Targeted PHPUnit (`OrderControllerTest`, order policy matrix, order status enum)
- [x] Laravel Pint (`composer run lint`)
- [x] PHPStan (pre-commit scope)
- [x] ESLint + Nuxt typecheck

## Checklist

- [x] RBAC middleware on new/changed routes
- [x] Form Request on admin status endpoint
- [x] Arabic strings for new UI keys
- [x] Error envelope via `BaseController`
- [x] Eager loads on order detail

## Related

- Stage File: `specs/phases/04_COMMERCIAL_LAYER/STAGE_19_ORDERS.md`
- Testing Guide: `specs/runtime/019-orders/guides/TESTING_GUIDE.md`
