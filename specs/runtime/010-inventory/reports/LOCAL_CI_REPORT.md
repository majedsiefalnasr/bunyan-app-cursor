# Local / CI Notes — 010-inventory

## `php artisan migrate --pretend`

Default workspace `.env` pointed at MySQL credentials that were unreachable from this environment, so `migrate --pretend` was not executed against a live server. Schema correctness was exercised via PHPUnit `RefreshDatabase` (SQLite in-memory per `phpunit.xml`).
