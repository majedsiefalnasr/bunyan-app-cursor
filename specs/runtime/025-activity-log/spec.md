# Specification — Activity Log (STAGE_25)

**Phase:** 05_COMMUNICATION_AND_MEDIA  
**Authority:** `specs/phases/05_COMMUNICATION_AND_MEDIA/STAGE_25_ACTIVITY_LOG.md`

## Summary

Deliver a **domain activity log** (distinct from HTTP `LogApiActivity` middleware) persisted in `activity_logs` for audit trail and user activity: who performed which action, when, on which subject, with optional before/after payload. Admin can list and filter globally; authenticated users who may **view** a subject (e.g. `Project`) can list activity for that subject. Implementation follows Bunyan layering: Form Requests, thin controllers, `ActivityLogService` + `ActivityLogRepository`, policies, API Resources, Sanctum + `role:admin` for global index, feature tests, and a minimal Nuxt admin page plus a reusable timeline component.

## User stories

1. **US1 — Admin global list**  
   As an admin, I can paginate and filter activity entries by user, action, subject type, subject id, and date range.

2. **US2 — Subject timeline**  
   As a user authorized to view a subject, I can fetch a paginated activity timeline for that subject.

3. **US3 — Automatic model logging**  
   As the platform, when configured models change, the system records `created` / `updated` / `deleted` rows with safe metadata (no secrets).

4. **US4 — Retention**  
   As an operator, I can configure maximum retention (days) and prune historical rows via an Artisan command (scheduled wiring optional in this stage).

5. **US5 — Admin UI**  
   As an admin, I can open a Nuxt admin page listing recent activity with RTL layout and Nuxt UI.

## Functional requirements

### Backend

- **Migration (forward-only):** `activity_logs` with `id`, `user_id` (nullable FK `users`), `action` (string/enum: `created`, `updated`, `deleted`, `viewed`, `exported`), `subject_type`, `subject_id` (morph), `properties_json` (nullable JSON for `old`/`new` snapshots on updates), `ip_address` (nullable string), `user_agent` (nullable text), `created_at` (immutable rows; no `updated_at` required). Indexes on `user_id`, morph columns, `action`, `created_at`.
- **Layers:** `ActivityLogRepository` (queries only), `ActivityLogService` (orchestration: record, list admin, list for subject, prune), thin `ActivityLogController`.
- **Endpoints (Sanctum):**
  - `GET /api/v1/admin/activity-log` — **admin only** (`role:admin`), paginated index with validated filters.
  - `GET /api/v1/{entity}/{id}/activity` — timeline for allowlisted `entity` values (`projects`, `orders` in this stage), authorize using existing model policies (`view` on resolved model).
- **Recording:** `ActivityLogService::record(...)` invoked from a reusable model trait (`LogsModelActivity`) registered via model `booted` callbacks for `created`, `updated`, `deleted` on **Project** (initial scope); optional extension to `Order` without changing API contract.
- **Validation:** Dedicated Form Request(s) for index filters; strict allowlists for `entity` → model class map.
- **Authorization:** Admin list is role-gated; subject timeline uses `$this->authorize('view', $model)` after resolving `{entity}/{id}`.
- **Privacy / safety:** Never store passwords/tokens; cap JSON payload size/keys for update diffs; strip known sensitive attributes from snapshots.

### Frontend

- **Admin page:** `frontend/pages/admin/activity-log.vue` — `middleware: ['auth','role']`, `roles: ['admin']`, table or list via Nuxt UI, `useApi` composable, i18n strings.
- **Component:** `frontend/components/dashboard/ActivityTimeline.vue` — props: `entity`, `id`, optional heading; fetches `/v1/{entity}/{id}/activity`.

### Non-goals (this stage)

- Full SIEM/export streaming pipelines beyond a simple `exported` action hook placeholder.
- Real-time websockets for live activity.
- Immutable WORM storage / legal hold workflows.

## Acceptance criteria

- RBAC: global list is admin-only; subject timeline respects existing subject policies.
- Feature tests: admin can index; non-admin forbidden on admin index; project participant can read project activity; outsider forbidden.
- `composer run lint` + `composer run test` pass; frontend `npm run lint`, `npm run typecheck`, `npm run test` pass.
- `php artisan migrate --pretend` succeeds.

## Clarifications

### Session 2026-04-12

- **Route placement:** Global admin index lives under existing `/api/v1/admin/*` group for consistency with other admin APIs. Subject timeline uses stage shape `/api/v1/{entity}/{id}/activity` with `entity` allowlist.
- **Automatic logging scope:** Apply trait to `Project` only in this stage; `orders` appears in allowlist for future logging but may return empty timelines until wired.
- **Retention default:** `365` days in config; prune command deletes rows older than configured days.
