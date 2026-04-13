# Requirements Checklist — Payments

## Functional

- [x] Customer can initiate payment for owned order
- [x] Customer can view payment detail
- [x] Customer can capture sandbox payment
- [x] Customer can refund completed sandbox payment
- [x] Customer can list payment history
- [x] Webhook endpoint validates secret and updates state
- [x] Frontend pages: history, detail, checkout

## Security & RBAC

- [x] RBAC middleware on all authenticated payment routes
- [x] Webhook route does not use Sanctum; uses separate secret validation
- [x] Policies enforce order ownership via payment.payable
- [x] Form Request validation on all mutating endpoints
- [x] No PCI sensitive data persisted or logged

## Architecture

- [x] Controllers thin; services hold orchestration
- [x] Repositories encapsulate Eloquent queries
- [x] Gateway behind interface for future providers
- [x] Standard API success/error envelope

## Data

- [x] Migrations: `payments`, `payment_attempts` with indexes
- [x] Polymorphic payable indexed (`payable_type`, `payable_id`)

## Quality

- [x] PHPUnit feature tests for payment flows
- [x] Vitest or component tests for critical composables/pages (if present in repo patterns)

## i18n / RTL

- [x] API messages Arabic-first where aligned with existing controllers
- [x] Frontend strings via i18n keys
