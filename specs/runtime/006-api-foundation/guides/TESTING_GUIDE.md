# Testing Guide — API Foundation

> **Phase:** 01_PLATFORM_FOUNDATION > **Generated:** 2026-04-12T12:50:00Z

## Prerequisites

```bash
cd backend
composer install
cp .env.example .env   # if not already configured
php artisan key:generate
```

For automated tests, PHPUnit uses sqlite in-memory per `phpunit.xml` (no MySQL required).

## Running automated tests

### Health endpoint only

```bash
cd backend
php artisan test --filter=HealthEndpointTest
```

Expected: two tests pass (`health returns success envelope`, `health echoes correlation id header`).

### Full backend suite

```bash
cd backend
php artisan test
```

### Monorepo gates (match CI)

```bash
cd /path/to/bunyan-app-cursor
npm run lint
npm run typecheck
npm run test
```

## Manual test scenarios

### Scenario 1 — JSON health (local)

**Preconditions:** `php artisan serve` running on `http://127.0.0.1:8000`.

**Steps:**

1. `curl -sS http://127.0.0.1:8000/api/v1/health | jq .`

**Expected:**

- HTTP 200
- `success` is `true`
- `data.service` is `bunyan-api`
- `data.version` is `v1`
- `data.time` is a non-empty ISO-8601 string
- `data.app` matches `config('app.name')` (default **Laravel** unless overridden)
- `data.correlation_id` is a non-null string (auto-generated when header omitted)
- `error` is `null`

### Scenario 2 — Correlation header echo

**Steps:**

1. `curl -sS -H "X-Correlation-ID: manual-corr-001" http://127.0.0.1:8000/api/v1/health | jq .data.correlation_id`

**Expected:** Output is exactly `manual-corr-001`.

### Scenario 3 — Framework `/up` unchanged

**Steps:**

1. `curl -sS -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/up`

**Expected:** `200`

### Scenario 4 — CI readiness route (testing / local only)

**Steps:**

1. `APP_ENV=testing php artisan test --filter=CiReadyRouteTest`

**Expected:** Test passes (`/__ci_ready` returns body `ok` in testing).

## CORS smoke (browser)

**Preconditions:** Nuxt dev server on `http://localhost:3000` calling API on another origin.

**Steps:**

1. From browser devtools on the Nuxt origin, perform a credentialed `fetch` to `GET /sanctum/csrf-cookie` then `GET /api/v1/health`.

**Expected:** No CORS errors when origin is listed in `config/cors.php` (`http://localhost:3000`, `http://127.0.0.1:3000`, or `FRONTEND_URL`).

## OpenAPI file sanity

```bash
# optional: if yq or swagger-cli is installed
swagger-cli validate backend/docs/openapi/openapi.yaml
```

**Expected:** Valid OpenAPI 3.0.3 document (baseline; tooling optional).
