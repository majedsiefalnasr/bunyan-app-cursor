# Requirements Checklist — Payments

## Functional

- [ ] Customer can initiate payment for owned order
- [ ] Customer can view payment detail
- [ ] Customer can capture sandbox payment
- [ ] Customer can refund completed sandbox payment
- [ ] Customer can list payment history
- [ ] Webhook endpoint validates secret and updates state
- [ ] Frontend pages: history, detail, checkout

## Security & RBAC

- [ ] RBAC middleware on all authenticated payment routes
- [ ] Webhook route does not use Sanctum; uses separate secret validation
- [ ] Policies enforce order ownership via payment.payable
- [ ] Form Request validation on all mutating endpoints
- [ ] No PCI sensitive data persisted or logged

## Architecture

- [ ] Controllers thin; services hold orchestration
- [ ] Repositories encapsulate Eloquent queries
- [ ] Gateway behind interface for future providers
- [ ] Standard API success/error envelope

## Data

- [ ] Migrations: `payments`, `payment_attempts` with indexes
- [ ] Polymorphic payable indexed (`payable_type`, `payable_id`)

## Quality

- [ ] PHPUnit feature tests for payment flows
- [ ] Vitest or component tests for critical composables/pages (if present in repo patterns)

## i18n / RTL

- [ ] API messages Arabic-first where aligned with existing controllers
- [ ] Frontend strings via i18n keys
