# Dashboard — Specification

> **Stage:** Dashboard  
> **Phase:** 06_REPORTING_AND_ANALYTICS  
> **Branch:** `spec/026-dashboard`  
> **Generated:** 2026-04-13T20:35:00Z  
> **Source:** `specs/phases/06_REPORTING_AND_ANALYTICS/STAGE_26_DASHBOARD.md`

## Overview

Deliver **role-scoped dashboards** with KPI metrics, recent activity, and API endpoints that aggregate existing domain data (projects, orders, users, activity logs). All reads go through **Laravel Sanctum** + **RBAC middleware**; aggregation logic lives in **services** with **repositories** for Eloquent. Responses use the standard `{ success, data, message, errors, error }` envelope. Frontend uses **Nuxt 3 + Nuxt UI** with Arabic-first i18n and existing `useApi` patterns.

## Personas & Roles

- **Admin:** platform-wide counts (users, projects, orders), revenue-style totals from completed/delivered orders, global recent activity from `activity_logs`.
- **Customer:** own projects count, own orders count, own recent activity.
- **Contractor:** supplier-linked order counts and revenue where `SupplierProfile.user_id` matches; own recent activity.
- **Supervising Architect:** supervised projects count, pending-relevant counts where available from existing models; own recent activity.
- **Field Engineer:** assigned tasks count, authored reports count; own recent activity.

## In Scope

### Backend

- `GET /api/v1/dashboard` — aggregated snapshot for authenticated user (role-scoped).
- `GET /api/v1/dashboard/metrics` — flat KPI map (same scoping rules).
- `GET /api/v1/dashboard/recent-activity?per_page=` — recent `ActivityLog` rows: admin sees global feed; non-admin sees rows where `user_id` matches current user.
- `DashboardRepository` — all Eloquent reads; no HTTP.
- `DashboardService` — orchestration, optional `Cache::remember` keyed by user id + role with TTL from `config/dashboard.php`.
- Thin `DashboardController` + Form Requests for query validation (`per_page` bounds).
- Routes under `auth:sanctum` and `role:customer,contractor,supervising_architect,field_engineer,admin`.
- Feature tests per role for HTTP 200 and JSON shape; guest 401.

### Frontend

- Extend `pages/dashboard/index.vue` to load dashboard snapshot on mount via composable (e.g. `useDashboard()` calling the new endpoints).
- Stat cards (Nuxt UI `UCard`) for primary KPIs; compact recent-activity list when data exists.
- i18n keys under `dashboard.*` for new labels (Arabic + English).

### Out of Scope

- New persistent `dashboard_*` tables (aggregates are computed on read).
- Full charting library integration (ApexCharts/Chart.js) beyond placeholder hooks or simple numeric KPIs in this slice.
- Real-time WebSocket updates.

## User Stories

### US1 — Authenticated dashboard snapshot

As any authenticated role, I can `GET /api/v1/dashboard` and receive role-appropriate aggregates without crossing tenant boundaries.

### US2 — KPI metrics endpoint

As any authenticated role, I can `GET /api/v1/dashboard/metrics` for a stable KPI map used by widgets.

### US3 — Recent activity feed

As an admin, I see platform-wide recent activity. As a non-admin, I see only log rows attributed to my user as actor.

### US4 — Dashboard UI

As a signed-in user, I see my KPI cards and activity on `/dashboard` with RTL-safe layout.

## Acceptance Criteria

- All three routes return the standard API success envelope with `data` populated.
- RBAC: only listed roles may access; unauthenticated requests receive 401.
- No N+1 on activity list: eager-load `actor` where applicable.
- `per_page` validated between 5 and 50 with default 15.
- `php artisan test` includes new feature tests green; Pint/PHPStan unchanged or improved.

## Dependencies

- Existing models: `User`, `Project`, `Order`, `SupplierProfile`, `Task`, `Report`, `ActivityLog`.
- Activity log admin listing patterns in `ActivityLogRepository` (read-only reuse patterns).

## Clarifications

### Session 2026-04-13

- **Caching:** Server-side cache TTL default **60 seconds** per user+endpoint class; configurable via `DASHBOARD_CACHE_TTL` env.
- **Revenue KPI:** Sum `orders.total_amount` where `status` in (`completed`, `delivered`) for admin global scope; for contractor scope filter by `supplier_id` = contractor’s supplier profile id.
- **Charts:** Deferred; UI shows numeric KPIs and lists only in this delivery slice.
