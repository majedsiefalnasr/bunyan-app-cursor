# PR — Projects (STAGE_12)

## Summary

**Stage:** Projects  
**Phase:** 03_PROJECT_MANAGEMENT  
**Branch:** `spec/012-projects` → `develop`  
**Tasks:** 14 / 14 completed

## What Changed

### Backend

- Added `ProjectService` and expanded `ProjectRepository` for paginated, role-scoped listings.
- New migrations for `projects` and `phases` columns; legacy `projects.status` values remapped to the new lifecycle enum.
- Replaced `ProjectStatus` enum with `draft → planning → in_progress → on_hold → completed → closed`.
- New endpoints: `PUT /api/v1/projects/{project}/status`, `GET /api/v1/projects/{project}/timeline`.
- `ProjectPolicy` now covers field-engineer visibility via reports and `transitionStatus`.
- `ProjectController` delegates to the service and authorizes `show`; new `TransitionProjectStatusRequest`.
- Expanded `ProjectResource` / `PhaseResource`; tests and factories updated.

### Frontend

- New pages: `pages/projects/index.vue`, `new.vue`, `[id].vue` using `useApi`, Nuxt UI, and `projects.*` i18n keys (`ar.json` / `en.json`).

### Database

- `2026_04_12_120000_evolve_projects_for_stage_12.php`
- `2026_04_12_120001_evolve_phases_for_stage_12.php`

## Breaking Changes

- **API:** `ProjectStatus` string values changed (`pending`/`active`/`cancelled` migrated at DB level; clients must use the new lifecycle values for writes).
- **Behavior:** `GET /api/v1/projects/{id}` now enforces `ProjectPolicy::view` (403 for unrelated users).

## Testing

- [x] Full PHPUnit suite (`php artisan test`)
- [x] Frontend Vitest (`npm run test`)
- [x] Laravel Pint (`composer run lint`)
- [x] ESLint + Nuxt typecheck (`npm run lint`, `npm run typecheck`)
- [x] PHPStan (pre-commit `lint-staged` on staged PHP)

## Checklist

- [x] RBAC middleware on new status route; timeline inherits authenticated + policy checks
- [x] Form Request validation for create, update, and status transition
- [x] Arabic/RTL strings via i18n keys for new UI
- [x] Bunyan JSON error contract preserved

## Related

- Stage spec: `specs/phases/03_PROJECT_MANAGEMENT/STAGE_12_PROJECTS.md`
- Runtime: `specs/runtime/012-projects/`
