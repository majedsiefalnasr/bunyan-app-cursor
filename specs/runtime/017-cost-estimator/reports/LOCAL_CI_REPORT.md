# Local CI Report — Cost Estimator

> **Generated:** 2026-04-12T20:45:00Z

## Commands

- `composer run test` — PASS
- `composer run lint` — PASS
- `composer run analyze` — PASS
- `php artisan migrate --pretend` — **SKIP/FAIL** in agent shell (MySQL access denied; use project `.env` + local DB)
- `npm run lint` — PASS
- `npm run typecheck` — PASS
- `npm run test` — PASS

## Follow-up

Run `php artisan migrate --pretend` (and real migrate) on a machine with configured `DB_*` for Bunyan.
