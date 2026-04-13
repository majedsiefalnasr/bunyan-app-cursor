# Local CI Report — Payments

> **Generated:** 2026-04-13T20:55:00Z

## Commands

| Command                                                   | Result                   |
| --------------------------------------------------------- | ------------------------ |
| `cd backend && composer run lint`                         | PASS                     |
| `cd backend && php artisan test --filter=PaymentFlowTest` | PASS (4 tests)           |
| `cd frontend && npm run lint`                             | PASS                     |
| `cd frontend && npx nuxi typecheck`                       | PASS                     |
| `cd frontend && npm run test`                             | PASS (87 tests)          |
| `cd backend && php artisan migrate --pretend`             | SKIPPED (no local MySQL) |

## Notes

- CI on GitHub should run full `composer run test` with configured services.
