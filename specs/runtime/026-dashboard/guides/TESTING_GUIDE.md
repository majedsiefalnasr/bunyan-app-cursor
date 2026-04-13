# Testing Guide — Dashboard

> **Phase:** 06_REPORTING_AND_ANALYTICS > **Generated:** 2026-04-13T21:50:00Z

## Prerequisites

- Backend: `cd backend && composer install && cp .env.example .env && php artisan key:generate`
- SQLite is sufficient for automated tests (`phpunit.xml` uses `:memory:`).
- Frontend: `cd frontend && npm install`

## Automated Commands

```bash
cd backend && composer run lint && php artisan test --filter=DashboardControllerTest
```

```bash
cd frontend && npm run lint && npm run typecheck && npm run test
```

## Manual Scenario 1 — Customer KPIs

**Preconditions:** Seed or register a **customer** user and obtain a Sanctum token (login via `/api/v1/auth/login`).

**Steps:**

1. `curl -sS -H "Authorization: Bearer <TOKEN>" -H "Accept: application/json" http://localhost:8000/api/v1/dashboard`
2. Verify JSON: `"success": true`, `"data.role": "customer"`, `"data.kpis.projects"` and `"data.kpis.orders"` are non-negative integers.

## Manual Scenario 2 — Admin global activity

**Preconditions:** **Admin** user token; at least one row in `activity_logs` (any `user_id`).

**Steps:**

1. `curl -sS -H "Authorization: Bearer <ADMIN_TOKEN>" "http://localhost:8000/api/v1/dashboard/recent-activity?per_page=10"`
2. Confirm `data.data` is a non-empty array when logs exist and entries may include multiple distinct `user_id` values.

## Manual Scenario 3 — Non-admin activity scope

**Preconditions:** Two users with activity logs; token for **user A** only.

**Steps:**

1. As user A, call `/api/v1/dashboard/recent-activity?per_page=10`.
2. Confirm every returned row has `user_id` equal to user A’s id (no other actors).

## Manual Scenario 4 — Nuxt dashboard UI

**Preconditions:** `npm run dev` on `http://localhost:3000`, valid session.

**Steps:**

1. Open `http://localhost:3000/ar/dashboard` (or default locale path).
2. Confirm KPI cards render after load and “Recent activity” appears when the API returns rows.
3. Toggle locale to English and confirm labels change (`dashboard.kpi_*` keys).

## Manual Scenario 5 — Validation error

**Steps:**

1. As any authenticated user: `GET /api/v1/dashboard/recent-activity?per_page=3`
2. Expect HTTP **422** (`per_page` below minimum 5).
