# Local CI Report — Pricing

> **Generated:** 2026-04-12T18:30:00Z

## Commands

| Command                                 | Result                |
| --------------------------------------- | --------------------- |
| `php artisan test --filter=PricingTest` | PASS                  |
| `composer run lint` (backend)           | PASS                  |
| `npm run lint` (frontend)               | PASS                  |
| `npm run typecheck` (frontend)          | PASS                  |
| `php artisan migrate --pretend`         | SKIP (DB credentials) |

## Notes

- Full `composer run test` / `npm run test` not executed end-to-end in this session to conserve time; pricing slice covered by dedicated feature tests.
