# Testing Guide — STAGE_27 — Reports

> **Phase:** 06_REPORTING_AND_ANALYTICS > **Generated:** 2026-04-13T23:00:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
# Configure DB in .env then:
php artisan migrate:fresh --seed

cd ../frontend && npm ci
```

## Running Tests

### Backend Feature Tests (this stage)

```bash
cd backend
php artisan test --filter=BusinessAnalyticsReportControllerTest
```

### Full Backend Suite

```bash
cd backend && php artisan test
```

### Frontend

```bash
cd frontend && npm run test && npm run typecheck && npm run lint
```

## Manual Test Scenarios

### Scenario 1 — Admin loads report catalog

**Preconditions:** Seeded DB; user with `role = admin` exists (e.g. from factory or seeder).

**Steps:**

1. Log in via `POST /api/v1/auth/login` with admin credentials; capture Bearer token.
2. `GET https://<API_HOST>/api/v1/admin/analytics/reports/types` with `Authorization: Bearer <token>`.

**Expected Result:** HTTP 200, `success: true`, `data.types` is a non-empty array including `sales_summary`.

### Scenario 2 — Sales summary JSON with date filters

**Preconditions:** At least one row in `orders` (use `Order::factory()` or seed data).

**Steps:**

1. As admin, `GET /api/v1/admin/analytics/reports/sales_summary?date_from=2020-01-01&date_to=2030-12-31`.

**Expected Result:** HTTP 200, `data.summary.order_count` ≥ 1, `data.rows` is an array, `data.meta.report_type` equals `sales_summary`.

### Scenario 3 — XLSX export

**Steps:**

1. As admin, `GET /api/v1/admin/analytics/reports/orders_summary/export?format=xlsx` (optional date query params).

**Expected Result:** HTTP 200, `Content-Type` contains `spreadsheetml`, file downloads with `.xlsx` payload.

### Scenario 4 — Admin UI (Nuxt)

**Preconditions:** Frontend dev server and API base URL configured; admin session.

**Steps:**

1. Open `/admin/reports` in the browser.
2. Choose report type **Sales summary**, optionally set dates, click **Run report**.
3. Click **Export Excel**.

**Expected Result:** Table fills with rows; browser downloads an `.xlsx` file without 403.

### Scenario 5 — RBAC denial

**Steps:**

1. Authenticate as `customer` role.
2. `GET /api/v1/admin/analytics/reports/types`.

**Expected Result:** HTTP 403, error code consistent with RBAC denial.

## API Test Endpoints

| Method | Endpoint                                                          | Auth            | Expected Status |
| ------ | ----------------------------------------------------------------- | --------------- | --------------- |
| GET    | `/api/v1/admin/analytics/reports/types`                           | Admin Bearer    | 200             |
| GET    | `/api/v1/admin/analytics/reports/sales_summary`                   | Admin Bearer    | 200             |
| GET    | `/api/v1/admin/analytics/reports/sales_summary/export?format=pdf` | Admin Bearer    | 200             |
| GET    | `/api/v1/admin/analytics/reports/types`                           | Customer Bearer | 403             |

## Common Issues

| Issue                           | Cause                                | Fix                                        |
| ------------------------------- | ------------------------------------ | ------------------------------------------ |
| 401 on analytics routes         | Missing/expired Sanctum token        | Re-login, send `Authorization: Bearer`     |
| Empty rows                      | No data in source tables for filters | Widen `date_from` / `date_to` or seed data |
| migrate --pretend fails locally | MySQL not running                    | Start Docker/Sail DB or run in CI          |
