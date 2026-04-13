# PR — Payments

## Summary

**Stage:** Payments  
**Phase:** 04_COMMERCIAL_LAYER  
**Branch:** `spec/020-payments` → `develop`  
**Tasks:** 17 / 17 completed

## What Changed

### Backend

- Added polymorphic `payments` and `payment_attempts` migration, enums, models, repositories, `PaymentService`, sandbox `PaymentGatewayContract` implementation, policies, form requests, API resources, `PaymentController` + `PaymentWebhookController`, webhook signature middleware, routes, `config/payments.php`, translations `resources/lang/{ar,en}/payments.php`, feature tests `PaymentFlowTest`.

### Frontend

- Added `usePayments` composable and pages under `/payments` (list, detail, checkout) with Nuxt UI; extended `locales/ar.json` and `locales/en.json` (`nav.payments`, `payments.*`).

### Database

- `2026_04_13_120000_create_payments_and_payment_attempts_tables.php`

## Breaking Changes

- None

## Testing

- [x] Feature tests: `php artisan test --filter=PaymentFlowTest`
- [x] Frontend tests: `npm run test`
- [x] Lint: `composer run lint`, `npm run lint`
- [x] Typecheck: `npx nuxi typecheck`
- [x] PHPStan: pre-commit `phpstan analyse` (full project)

## Checklist

- [x] RBAC middleware on new authenticated routes; webhook uses secret middleware
- [x] Form Request validation on initiate/refund
- [x] Arabic/RTL strings for API messages and frontend i18n
- [x] Error contract via `BaseController`
- [x] No N+1 on history (`attempts` eager loaded)

## Related

- Stage File: `specs/phases/04_COMMERCIAL_LAYER/STAGE_20_PAYMENTS.md`
- Testing Guide: `specs/runtime/020-payments/guides/TESTING_GUIDE.md`
