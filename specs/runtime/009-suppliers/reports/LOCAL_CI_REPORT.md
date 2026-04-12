# Local CI Report — Suppliers

> Generated: 2026-04-12

## Executed

| Command                                                   | Result                                                                                                                        |
| --------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `php artisan test --filter=SupplierProfileControllerTest` | PASS                                                                                                                          |
| `php artisan test --filter=ProductControllerTest`         | PASS                                                                                                                          |
| `npm run lint` (frontend)                                 | PASS                                                                                                                          |
| `./vendor/bin/pint` (targeted files)                      | PASS                                                                                                                          |
| `php artisan migrate --pretend`                           | **Skipped in agent env** (MySQL credentials to remote host denied from sandbox) — run locally in CI with sqlite/mysql service |
