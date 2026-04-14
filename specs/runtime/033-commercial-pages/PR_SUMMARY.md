# PR — Commercial Pages

## Summary

**Stage:** Commercial Pages
**Phase:** 07_FRONTEND_APPLICATION
**Branch:** `spec/033-commercial-pages` → `develop`
**Tasks:** 20 / 20 completed

## What Changed

### Backend

- No backend changes.

### Frontend

- Added commercial compatibility routes:
  - `/rfqs/create` → `/rfqs/new`
  - `/checkout` → `/payments/checkout`
  - `/payment/:orderId` → `/payments/:id`
- Implemented `/payment/success` page with optional `paymentId` refetch and i18n support
- Implemented supplier quotation submission page `/rfqs/:id/quote`
- Added i18n keys (ar/en) for payment success and RFQ quote submission
- Added Playwright e2e coverage for compatibility redirects

### Database

- No migrations.

## Breaking Changes

- None.

## Testing

- [x] Frontend lint passes (`rtk cd frontend && rtk proxy npm run lint`)
- [x] Frontend typecheck passes (`rtk cd frontend && rtk proxy npm run typecheck`)
- [x] Frontend tests pass (`rtk cd frontend && rtk proxy npm run test`)
- [ ] Backend unit tests pass (`rtk cd backend && rtk php artisan test --testsuite=Unit`) (not required; no backend changes)
- [ ] Backend feature tests pass (`rtk cd backend && rtk php artisan test --testsuite=Feature`) (not required; no backend changes)

## Checklist

- [x] Arabic/RTL support verified (i18n keys added; RTL-first routes)
- [x] Error contract followed (via existing API composables)
- [x] No new dependencies introduced

## Related

- Stage File: `specs/phases/07_FRONTEND_APPLICATION/STAGE_33_COMMERCIAL_PAGES.md`
- Testing Guide: `specs/runtime/033-commercial-pages/guides/TESTING_GUIDE.md`
