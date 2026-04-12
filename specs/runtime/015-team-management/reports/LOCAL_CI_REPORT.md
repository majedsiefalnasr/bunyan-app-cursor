# Local CI Notes — Team Management

- `php artisan migrate --pretend` was not run against a live MySQL instance in this workspace because the default `.env` database user was not reachable from the agent environment.
- PHPUnit with `RefreshDatabase` applied the new migration successfully during `composer run test`.
