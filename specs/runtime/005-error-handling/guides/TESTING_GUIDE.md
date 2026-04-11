# Testing Guide — Error Handling & Logging (STAGE_05)

> **Phase:** 01_PLATFORM_FOUNDATION  
> **Generated:** 2026-04-11 (backfilled for orchestrator closure artifacts)  
> **Runtime dir:** `specs/runtime/005-error-handling`

This guide covers automated tests and manual checks for the unified API error contract, correlation IDs, structured logging middleware, RBAC-aware error detail filtering, and the Nuxt error surfaces (boundary, toasts, error pages).

---

## Prerequisites

From the **monorepo root**:

```bash
npm run install:backend
npm run install:frontend
```

**Backend** (MySQL must match `.env`; SQLite is fine if your local setup uses it):

```bash
cd backend
composer install
cp .env.example .env   # if you do not already have .env
php artisan key:generate
php artisan migrate:fresh --seed
```

**Frontend:**

```bash
cd frontend
npm install
```

Optional: copy `frontend/.env.example` to `frontend/.env` and set `NUXT_PUBLIC_API_BASE_URL` (e.g. `http://127.0.0.1:8000`) so the app talks to your local API.

---

## Running Tests

### Backend — full error-handling feature suite

```bash
cd backend
php artisan test tests/Feature/ErrorHandling
```

**Focused examples:**

```bash
cd backend
php artisan test tests/Feature/ErrorHandling/CorrelationIdTest.php
php artisan test tests/Feature/ErrorHandling/ErrorContractComplianceTest.php
php artisan test tests/Feature/ErrorHandling/ErrorDetailFilteringTest.php
php artisan test tests/Feature/ErrorHandling/ValidationErrorResponseTest.php
```

### Backend — unit coverage (enums, registry, middleware unit tests)

```bash
cd backend
php artisan test tests/Unit/Enums/ErrorCodeTest.php
php artisan test tests/Unit/Services/ErrorCodeRegistryTest.php
php artisan test tests/Unit/Http/Middleware/InjectCorrelationIdTest.php
```

### Frontend — Vitest (error store, composables, boundary, pages)

```bash
cd frontend
npm run test -- tests/unit/stores/error.spec.ts
npm run test -- tests/unit/composables/useErrorNotification.spec.ts
npm run test -- tests/unit/components/AppErrorBoundary.spec.ts
npm run test -- tests/unit/pages/error.spec.ts
npm run test -- tests/integration/errors/ErrorStateSyncTest.spec.ts
npm run test -- tests/integration/a11y/ErrorPageA11yTest.spec.ts
```

### Frontend — full suite + quality gates

```bash
cd frontend
npm run lint
npm run typecheck
npm run test
```

### Monorepo validation (CI-shaped)

From repo root:

```bash
npm run lint
npm run test
```

---

## Manual Test Scenarios

Assume API base `http://127.0.0.1:8000` (`php artisan serve` from `backend/`).  
Assume Nuxt dev `http://localhost:3000` (`npm run dev` from `frontend/`).  
The app uses **locale prefix** routing (`/ar/...`, `/en/...`).

### Scenario 1 — Validation error shape (`VALIDATION_ERROR`)

**Preconditions:** API server running, DB migrated.

**Steps:**

1. Send a registration payload that fails validation (invalid email, strong password still required):

```bash
curl -sS -X POST "http://127.0.0.1:8000/api/v1/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Manual Test","email":"not-an-email","password":"Abcd1234","role":"customer"}'
```

**Expected result:**

- HTTP **422**
- JSON body matches contract:

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "<localized string>",
    "details": { "<field>": ["<messages>"] }
  }
}
```

- Response header **`X-Correlation-ID`** present (non-empty).

---

### Scenario 2 — Invalid credentials (`AUTH_INVALID_CREDENTIALS`)

**Steps:**

```bash
curl -sS -i -X POST "http://127.0.0.1:8000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"nobody@example.com","password":"WrongPass1"}'
```

**Expected result:**

- HTTP **401** (per `ErrorCode::AUTH_INVALID_CREDENTIALS`)
- `success: false`, `error.code` reflects auth failure (not a stack trace for anonymous callers)
- `X-Correlation-ID` on response

---

### Scenario 3 — Unauthenticated protected route

**Steps:**

```bash
curl -sS -i "http://127.0.0.1:8000/api/v1/auth/profile" \
  -H "Accept: application/json"
```

**Expected result:**

- HTTP **401**
- JSON error contract (no HTML)
- `X-Correlation-ID` present

---

### Scenario 4 — Correlation ID propagation

**Steps:**

```bash
curl -sS -i "http://127.0.0.1:8000/api/v1/auth/profile" \
  -H "Accept: application/json" \
  -H "X-Correlation-ID: manual-correlation-001"
```

**Expected result:**

- Response header **`X-Correlation-ID: manual-correlation-001`** (same value echoed back)
- In logs (e.g. `storage/logs/laravel.log` or configured JSON channel), structured entries include the same correlation id in context when logging runs for that request.

---

### Scenario 5 — Unknown API route (404)

**Steps:**

```bash
curl -sS -i "http://127.0.0.1:8000/api/v1/does-not-exist" \
  -H "Accept: application/json"
```

**Expected result:**

- HTTP **404**
- Machine-readable JSON error (not Laravel HTML debug page when `Accept: application/json`)

---

### Scenario 6 — Nuxt error pages (RTL + i18n)

**Preconditions:** `cd frontend && npm run dev`

**Steps:**

1. Open `http://localhost:3000/ar/error/404`
2. Open `http://localhost:3000/ar/error/403`
3. Open `http://localhost:3000/ar/error/500`
4. Repeat with `/en/...` prefix.

**Expected result:**

- Pages render without console errors
- Arabic routes: `html` uses RTL (`dir="rtl"`) per app config; English routes use LTR
- Copy is driven from locale files (`locales/ar.json`, `locales/en.json`)

---

### Scenario 7 — Admin-only debug details (local / testing)

**Preconditions:** Authenticated **admin** user in **local** or **testing** environment (see `ApiErrorResponse::debugDetails`).

**Steps:**

1. Log in as an admin (per your seed data; adjust email/password).
2. Trigger an error that includes an exception mapped through `ApiExceptionRenderer` (e.g. a controlled server error in a dev-only route or test).
3. Inspect JSON `error.details._debug` for admin vs non-admin.

**Expected result:**

- **Admin** (local/testing): optional `_debug` payload may appear for supported exceptions.
- **Non-admin** or **production**: no stack trace or internal `_debug` leakage.

---

## API reference (manual / smoke)

| Method | Endpoint                  | Auth                | Notes                                                                                                                                                                   |
| ------ | ------------------------- | ------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `POST` | `/api/v1/auth/register`   | No                  | Use invalid body for `VALIDATION_ERROR`                                                                                                                                 |
| `POST` | `/api/v1/auth/login`      | No                  | Wrong password → `AUTH_INVALID_CREDENTIALS`                                                                                                                             |
| `GET`  | `/api/v1/auth/profile`    | Bearer **required** | No token → 401                                                                                                                                                          |
| `GET`  | `/api/v1/__errors/{type}` | Varies              | **Registered only when `app()->runningUnitTests()`** — not available on a normal `php artisan serve` instance. Use PHPUnit or the public routes above for manual smoke. |

Replace `{type}` in tests with values supported by `ErrorHandlingTestController` (e.g. `domain_validation`, `resource_not_found`, `server_error`) — see `backend/app/Http/Controllers/Api/ErrorHandlingTestController.php`.

---

## Common issues

| Issue                                   | Cause                                              | Fix                                                                            |
| --------------------------------------- | -------------------------------------------------- | ------------------------------------------------------------------------------ |
| HTML stack trace instead of JSON        | Missing `Accept: application/json`                 | Add header on `curl` or API client                                             |
| CORS errors from Nuxt to API            | API URL / Sanctum / CORS not aligned               | Set `NUXT_PUBLIC_API_BASE_URL`; configure Laravel CORS for dev origin          |
| `422` on register with “valid” password | Laravel `Password::min(8)->mixedCase()->numbers()` | Use ≥8 chars with upper, lower, and digit                                      |
| No `X-Correlation-ID`                   | Very early failure before middleware               | Check `bootstrap/app.php` — `InjectCorrelationId` should be prepended globally |
| Tests fail DB                           | `.env` DB unreachable                              | Run `php artisan migrate:fresh --seed` against local DB                        |

---

## Related artifacts

| Artifact           | Path                                                                      |
| ------------------ | ------------------------------------------------------------------------- |
| Specification      | `specs/runtime/005-error-handling/spec.md`                                |
| PR summary         | `specs/runtime/005-error-handling/PR_SUMMARY.md`                          |
| Quickstart         | `specs/runtime/005-error-handling/quickstart.md`                          |
| Exception contract | `specs/runtime/005-error-handling/contracts/laravel-exception-handler.md` |
