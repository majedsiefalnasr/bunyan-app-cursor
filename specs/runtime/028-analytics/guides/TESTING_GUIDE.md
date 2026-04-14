# Testing Guide — Analytics (STAGE_28)

> **Phase:** 06_REPORTING_AND_ANALYTICS  
> **Runtime dir:** `specs/runtime/028-analytics`  
> **Branch:** `spec/028-analytics`

This guide covers automated tests and manual checks for the Analytics stage: API endpoints, RBAC, caching behavior, aggregation/pruning commands, and the admin analytics page.

---

## Prerequisites

From the **monorepo root**:

```bash
npm run install
```

You’ll need a working DB configuration for Laravel. In CI this is typically MySQL; locally you can use your standard setup.

---

## Automated validation (CI-shaped)

From repo root:

```bash
npm run check
npm run test
```

Key pieces:

- Backend lint + static analysis:

```bash
cd backend
composer run lint
```

- Backend tests:

```bash
cd backend
php artisan test
```

- Frontend lint/typecheck/tests:

```bash
cd frontend
npm run lint
npm run typecheck
npm run test
```

---

## Focused backend tests

Analytics feature coverage:

```bash
cd backend
php artisan test --filter=AnalyticsEndpointsTest
```

Analytics unit coverage:

```bash
cd backend
php artisan test --filter=AnalyticsDimensionsTest
```

---

## Manual API smoke tests

### Endpoints

| Method | Endpoint                          |
| ------ | --------------------------------- |
| GET    | `/api/v1/analytics/overview`      |
| GET    | `/api/v1/analytics/metrics/{key}` |
| GET    | `/api/v1/analytics/trends`        |

All three are protected by:

- `auth:sanctum`
- server-side RBAC (`viewAnalytics` gate; Admin + Supervising Architect)
- throttling

### Example curls (replace token)

```bash
TOKEN="..."

curl -sS "http://127.0.0.1:8000/api/v1/analytics/overview" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

```bash
curl -sS "http://127.0.0.1:8000/api/v1/analytics/metrics/commerce.gmv?bucket=day" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

```bash
curl -sS "http://127.0.0.1:8000/api/v1/analytics/trends?bucket=day&keys[]=commerce.gmv&keys[]=platform.active_users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

Expected results:

- Allowed roles: HTTP **200**
- Disallowed authenticated roles: HTTP **403** with JSON error contract
- Unauthenticated: HTTP **401** with JSON error contract

---

## Aggregation + retention commands

### Aggregate rollups

Recompute recent rollups (default 14 days, daily buckets):

```bash
cd backend
php artisan analytics:aggregate
```

Override window/bucket:

```bash
cd backend
php artisan analytics:aggregate --days=30 --bucket=week
```

### Prune retained data

```bash
cd backend
php artisan analytics:prune
```

Expected:

- Command exits successfully and prints deleted counts.

---

## Admin UI smoke test

### Access

- Route: `/ar/admin/analytics` (locale-prefixed)
- Roles:
  - ✅ Admin
  - ✅ Supervising Architect
  - ❌ others (should redirect away via frontend role middleware)

### Expectations

- KPI cards render (values may be zeros until rollups exist).
- Trends section currently renders JSON (charts are a deferred follow-up).

---

## Notes

- If your local MySQL isn’t reachable, migration validation can be checked with:

```bash
cd backend
php artisan migrate --pretend --database=sqlite
```

---

## Related artifacts

- PR summary: `specs/runtime/028-analytics/PR_SUMMARY.md`
- Validation report: `specs/runtime/028-analytics/audits/VALIDATION_REPORT.md`
- Closure report: `specs/runtime/028-analytics/reports/CLOSURE_REPORT.md`
