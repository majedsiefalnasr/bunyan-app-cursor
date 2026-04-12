# Local CI Report — Media Library

> **Generated:** 2026-04-12T16:30:00Z

## Commands

| Command                         | Result | Notes                                      |
| ------------------------------- | ------ | ------------------------------------------ |
| `composer run lint`             | PASS   | Laravel Pint                               |
| `composer run test`             | PASS   | Full PHPUnit (includes `MediaApiTest`)     |
| `composer run analyze`          | PASS   | PHPStan                                    |
| `php artisan migrate --pretend` | SKIP   | MySQL credentials unavailable in agent env |

## Follow-up

Run locally after configuring `.env`:

```bash
cd backend && php artisan migrate --pretend --no-interaction
```
