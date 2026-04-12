# PR — Activity Log (STAGE_25)

## Summary

**Stage:** Activity Log  
**Phase:** 05_COMMUNICATION_AND_MEDIA  
**Branch:** `spec/025-activity-log` → `develop`  
**Tasks:** 12 / 12 completed

## What Changed

### Backend

- Added `activity_logs` table and `ActivityLog` model with `ActivityLogAction` enum.
- Added `ActivityLogRepository`, `ActivityLogService`, `ActivityLogController`, Form Requests, and `ActivityLogResource`.
- Wired `GET /api/v1/admin/activity-log` (admin-only) and `GET /api/v1/{entity}/{id}/activity` for `projects` and `orders`.
- Added `LogsModelActivity` trait to `Project` to record lifecycle events.
- Added `config/activity_log.php` and `activity-log:prune` Artisan command.
- Tests: `ActivityLogTest`, schema assertion for `activity_logs`, enum unit coverage.

### Frontend

- Admin page `pages/admin/activity-log.vue` listing admin activity API.
- `components/dashboard/ActivityTimeline.vue` embedded on `pages/projects/[id].vue`.
- i18n keys under `activityLog` in `locales/ar.json` and `locales/en.json`.
- Admin layout link to Activity log.

### Database

- `2026_04_12_130500_create_activity_logs_table.php`

## Breaking Changes

- None.

## Testing

- [x] Full PHPUnit suite (`php artisan test`)
- [x] ActivityLog feature tests
- [x] Frontend Vitest (`npm run test`)
- [x] Lint (`composer run lint`, `npm run lint`)
- [x] PHPStan (`composer run analyze`)
- [x] Nuxt typecheck (`npm run typecheck`)
- [ ] Migration pretend in CI/agent sandbox without DB (run locally with valid `.env`)

## Checklist

- [x] RBAC middleware applied on new routes
- [x] Form Request validation on new endpoints
- [x] Arabic/RTL support verified (admin strings + existing RTL shell)
- [x] Error contract followed
- [x] Actor relation eager-loaded for lists
- [x] Spec runtime artifacts updated under `specs/runtime/025-activity-log/`

## Related

- Stage file: `specs/phases/05_COMMUNICATION_AND_MEDIA/STAGE_25_ACTIVITY_LOG.md`
- Runtime: `specs/runtime/025-activity-log/`
