# Research — API Foundation

## Laravel 11 routing

- `bootstrap/app.php` registers `api` routes file; default prefix `api` applied by framework.
- `routes/api.php` uses `Route::prefix('v1')` → `/api/v1/...`.

## Laravel CORS

- Framework ships `fruitcake/php-cors`; configuration via `config/cors.php` when published.

## OpenAPI

- OpenAPI 3.0 YAML is sufficient for baseline; no runtime code dependency required for static file.

## References (in-repo)

- `backend/bootstrap/app.php` — middleware groups, `/up`, `__ci_ready`
- `backend/routes/api.php` — v1 groups, throttles
- `backend/app/Http/Controllers/Api/ApiResponse.php` — envelope
- `backend/app/Exceptions/ApiExceptionRenderer.php` — JSON errors
