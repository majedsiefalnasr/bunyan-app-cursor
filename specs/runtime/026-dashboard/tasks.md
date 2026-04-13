# Tasks — Dashboard

## Backend

- [x] T001 [US1] Add `backend/config/dashboard.php` with `cache_ttl_seconds` from env `DASHBOARD_CACHE_TTL` default 60.
- [x] T002 [US1] Create `backend/app/Repositories/DashboardRepository.php` — role-scoped counts, revenue sum, activity pagination (eager-load `actor`).
- [x] T003 [US1] Create `backend/app/Services/DashboardService.php` — `getOverview`, `getMetrics`, `getRecentActivity` with `Cache::remember` per user.
- [x] T004 [US1] Add `backend/app/Http/Requests/Api/V1/DashboardIndexRequest.php` (authorize authenticated user).
- [x] T005 [US3] Add `backend/app/Http/Requests/Api/V1/DashboardRecentActivityRequest.php` — validate `per_page` 5–50.
- [x] T006 [US1] Add `backend/app/Http/Controllers/Api/V1/DashboardController.php` — thin methods delegating to service.
- [x] T007 [US1] Register GET `dashboard`, `dashboard/metrics`, `dashboard/recent-activity` in `backend/routes/api.php` with Sanctum + role middleware + throttle.
- [x] T008 [US1] Add `backend/tests/Feature/Api/V1/DashboardControllerTest.php` — guest 401; customer, contractor, architect, field_engineer, admin 200; activity scope smoke test.

## Frontend

- [x] T009 [US4] Add `frontend/composables/useDashboard.ts` using `useApi().apiFetch` for the three endpoints.
- [x] T010 [US4] Update `frontend/pages/dashboard/index.vue` — show KPI cards and recent activity list with loading/error states.
- [x] T011 [US4] Extend `frontend/i18n/locales/ar.json` with `dashboard.*` keys for new UI strings.
- [x] T012 [US4] Extend `frontend/i18n/locales/en.json` with matching `dashboard.*` keys.

## Validation

- [x] T013 [P] Run `cd backend && composer run lint && php artisan test --filter=DashboardControllerTest`.
- [x] T014 [P] Run `cd frontend && npm run lint && npm run typecheck`.
