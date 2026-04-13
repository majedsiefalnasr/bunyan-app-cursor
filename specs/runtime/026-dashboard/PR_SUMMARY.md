# PR — Dashboard

## Summary

**Stage:** Dashboard  
**Phase:** 06_REPORTING_AND_ANALYTICS  
**Branch:** `spec/026-dashboard` → `develop`  
**Tasks:** 14 / 14 completed

## What Changed

### Backend

- New `GET /api/v1/dashboard`, `/api/v1/dashboard/metrics`, `/api/v1/dashboard/recent-activity` behind Sanctum + role middleware.
- `DashboardRepository`, `DashboardService`, `DashboardController`, and Form Requests.
- `config/dashboard.php` for `DASHBOARD_CACHE_TTL` (overview/metrics cache only).
- Feature tests in `DashboardControllerTest.php`.

### Frontend

- `useDashboard` composable calling `/v1/dashboard*` endpoints.
- Dashboard page shows KPI cards and recent activity list; new `dashboard.*` locale strings.

### Database

- None (read-only aggregates).

## Breaking Changes

- None.

## Testing

- [x] Feature tests (`php artisan test --filter=DashboardControllerTest`)
- [x] Frontend tests (`npm run test`)
- [x] Lint (`composer run lint`, `npm run lint`)
- [x] Type check (`npm run typecheck`)
- [ ] `php artisan migrate --pretend` against MySQL in CI (local agent had no DB listener)

## Checklist

- [x] RBAC middleware on new routes
- [x] Form Request validation (`per_page`)
- [x] Arabic/RTL strings added for new UI copy
- [x] Error contract via `BaseController`
- [x] Activity query eager-loads `actor`

## Artifacts

- Spec: `specs/runtime/026-dashboard/spec.md`
- Testing: `specs/runtime/026-dashboard/guides/TESTING_GUIDE.md`
- Closure: `specs/runtime/026-dashboard/reports/CLOSURE_REPORT.md`
