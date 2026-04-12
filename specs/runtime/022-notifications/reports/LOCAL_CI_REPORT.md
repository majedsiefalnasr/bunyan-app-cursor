# Local CI Report — Notifications

> **Generated:** 2026-04-12T12:45:00Z

## Commands Executed

- `php artisan test --filter=NotificationFlowTest` — PASS (9 tests)
- `./vendor/bin/pint --dirty` — PASS
- `npm run typecheck` (frontend) — PASS
- `php artisan migrate --pretend` — **FAIL** (MySQL access denied in agent environment)

## Notes

Re-run `php artisan migrate --pretend` and full `composer run test` / `npm run test` on a workstation with configured `.env` before merging.
