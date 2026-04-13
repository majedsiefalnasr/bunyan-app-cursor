# Dashboard — Technical Plan

> **Stage:** Dashboard | **Branch:** `spec/026-dashboard` | **Generated:** 2026-04-13T20:50:00Z

## Architecture

- **Layering:** `DashboardController` (thin) → `DashboardService` (aggregation, caching) → `DashboardRepository` (Eloquent only).
- **RBAC:** Routes grouped with `middleware(['auth:sanctum', 'role:customer,contractor,supervising_architect,field_engineer,admin'])` and `throttle:60,1`.
- **Validation:** `DashboardIndexRequest` for index/metrics (authorize authenticated); `DashboardRecentActivityRequest` validates `per_page` 5–50, default 15.
- **Responses:** `BaseController::sendSuccess` with arrays or `ActivityLogResource::collection` for activity pagination data.
- **Caching:** `Illuminate\Support\Facades\Cache::remember` in service with keys `dashboard:{overview|metrics|activity}:{userId}` and TTL `config('dashboard.cache_ttl_seconds')`.
- **i18n:** New keys under `dashboard.*` in `frontend/i18n/locales/ar.json` and `en.json`.

## Files to Add/Change

| Area     | Path                                                                                               |
| -------- | -------------------------------------------------------------------------------------------------- |
| Config   | `backend/config/dashboard.php`                                                                     |
| Repo     | `backend/app/Repositories/DashboardRepository.php`                                                 |
| Service  | `backend/app/Services/DashboardService.php`                                                        |
| HTTP     | `backend/app/Http/Controllers/Api/V1/DashboardController.php`                                      |
| Requests | `backend/app/Http/Requests/Api/V1/DashboardIndexRequest.php`, `DashboardRecentActivityRequest.php` |
| Routes   | `backend/routes/api.php` (register three GET routes)                                               |
| Tests    | `backend/tests/Feature/Api/V1/DashboardControllerTest.php`                                         |
| FE       | `frontend/composables/useDashboard.ts`, `frontend/pages/dashboard/index.vue`                       |
| i18n     | `frontend/i18n/locales/ar.json`, `frontend/i18n/locales/en.json`                                   |

## Endpoint Summary

| Method | Path                                | Roles            | Description                                 |
| ------ | ----------------------------------- | ---------------- | ------------------------------------------- |
| GET    | `/api/v1/dashboard`                 | all five + admin | Role-scoped snapshot                        |
| GET    | `/api/v1/dashboard/metrics`         | same             | Flat KPI map                                |
| GET    | `/api/v1/dashboard/recent-activity` | same             | Admin: global log; others: `user_id` = self |

## Testing Strategy

- PHPUnit feature: guest 401; each role 200; assert JSON paths `success`, `data`.
- Activity: non-admin must not receive entries with other `user_id` as actor (spot-check where logs exist).

## Rollout

- No migrations. Deploy config default; tune `DASHBOARD_CACHE_TTL` in env if needed.
