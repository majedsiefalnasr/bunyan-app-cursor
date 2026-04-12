# PR — Pricing

## Summary

**Stage:** Pricing  
**Phase:** 02_CATALOG_AND_INVENTORY  
**Branch:** `spec/011-pricing` → `develop`  
**Tasks:** 12 / 12 completed

## What Changed

### Backend

- Added `price_tiers` and `price_histories` migrations, models, repositories, and `PricingService` (tier sync with overlap validation, SAR calculation with variant → product-tier fallback).
- New endpoints: `GET products/{product}/pricing`, `PUT admin/products/{product}/pricing`, `POST pricing/calculate`.
- `ProductService::update` records `price_histories` when base `price` changes.
- `ProductResource` exposes `price_tiers` when loaded; `ProductService::loadDisplay` eager-loads tiers.

### Frontend

- `useSarPriceFormat` composable; product detail page shows quantity tiers and SAR formatting.
- Admin page `/admin/products/[id]/pricing` for tier management.
- New `catalog.*` i18n keys (ar/en).

### Database

- `2026_04_12_180000_create_price_tiers_table.php`
- `2026_04_12_180001_create_price_histories_table.php`

## Breaking Changes

- None

## Testing

- [x] Feature tests: `php artisan test --filter=PricingTest`
- [x] Lint: `composer run lint` (backend), `npm run lint` (frontend)
- [x] Type check: `npm run typecheck` (frontend)
- [ ] Full `php artisan test` / `npm run test` (run in CI or locally before merge if required by policy)

## Checklist

- [x] RBAC middleware applied on all new routes
- [x] Form Request validation on all new endpoints
- [x] Arabic/RTL support verified (strings + existing RTL layout)
- [x] Error contract followed
- [x] No intentional N+1 on tier reads (per-product queries)
- [x] Migration reviewed (`down()` present)

## Review artifacts

- `specs/runtime/011-pricing/reports/CLOSURE_REPORT.md`
- `specs/runtime/011-pricing/guides/TESTING_GUIDE.md`
- `specs/runtime/011-pricing/audits/VALIDATION_REPORT.md`
