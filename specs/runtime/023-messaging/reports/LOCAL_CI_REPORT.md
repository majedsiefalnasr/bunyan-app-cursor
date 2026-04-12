# Local / CI Notes — Messaging

## Migration pretend (local)

If `php artisan migrate --pretend` fails against MySQL in `.env`, run with sqlite override:

```bash
cd backend && DB_CONNECTION=sqlite DB_DATABASE=":memory:" php artisan migrate --pretend --no-interaction
```

## PHPUnit

Full suite executed via `composer run test` — exit code 0 on 2026-04-12.
