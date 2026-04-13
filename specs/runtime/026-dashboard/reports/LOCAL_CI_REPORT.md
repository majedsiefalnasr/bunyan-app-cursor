# Local CI Report — Dashboard

> **Generated:** 2026-04-13T21:40:00Z

## Commands Run

- `composer run lint` (backend) — pass
- `php artisan test --filter=DashboardControllerTest` — pass
- `npm run lint` + `npm run typecheck` + `npm run test` (frontend) — pass
- `php artisan migrate --pretend` — failed: MySQL connection refused in this environment (expected when DB not running)

## Follow-ups

- Run full `php artisan test` and `php artisan migrate --pretend` in CI or against a live MySQL instance before merge to `develop`.
