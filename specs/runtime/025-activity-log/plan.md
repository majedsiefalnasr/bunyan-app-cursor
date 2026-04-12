# Technical Plan — Activity Log (STAGE_25)

## Architecture alignment

- **Routes:** `GET /api/v1/admin/activity-log` inside existing `admin` + `role:admin` group; `GET /api/v1/{entity}/{id}/activity` inside `auth:sanctum` with `whereIn` constraint on `entity`.
- **Layers:** `ActivityLogController` → `ActivityLogService` → `ActivityLogRepository` → `ActivityLog` model.
- **DTO shaping:** `ActivityLogResource` for API output; Form Requests `ActivityLogIndexRequest`, `ActivitySubjectIndexRequest`.
- **Logging hook:** `App\Models\Concerns\LogsModelActivity` trait mixed into `Project`; uses `ActivityLogService` via container in `booted` callbacks.
- **Pruning:** `config/activity_log.php` + `php artisan activity-log:prune` command calling service prune method.

## Database

Migration `create_activity_logs_table` with columns per spec; morph index; `action` string column storing enum values.

## Authorization

- Admin index: middleware only (no additional policy required beyond admin role).
- Subject timeline: resolve model, `Gate::authorize('view', $model)` using existing policies (`ProjectPolicy`, `OrderPolicy`).

## Frontend

- `pages/admin/activity-log.vue` lists admin endpoint with filters (minimal: page/per_page).
- `components/dashboard/ActivityTimeline.vue` loads subject endpoint.

## Testing

- `ActivityLogAdminTest`: admin OK, customer 403 on admin index.
- `ActivityLogSubjectTest`: project stakeholder OK, stranger 403; list contains created event after project create (if logged).

## Rollout

Forward-only migration; no data backfill required.
