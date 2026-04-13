# Local CI Report — Invoicing

> **Generated:** 2026-04-13T22:15:00Z

## Commands Run

- `backend`: `composer run lint`, `composer run analyze`, `php artisan test`
- `frontend`: `npm run lint`, `npm run typecheck`, `npm run test`

## Notes

- `migrate --pretend` requires a running MySQL matching `.env`; skipped when DB unavailable.
