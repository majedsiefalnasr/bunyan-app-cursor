# Technical Plan — Projects (STAGE_12)

## Architecture

- **Controllers:** `ProjectController` delegates list/show/store/update/destroy/status/timeline to `ProjectService`.
- **Services:** `ProjectService` owns status graph, visibility rules for transitions, and timeline DTO assembly.
- **Repositories:** `ProjectRepository` encapsulates pagination, eager loads, and persistence writes.
- **Policies:** `ProjectPolicy` extended for `view` (field engineer), `transitionStatus`; controller calls `authorize` on each action.
- **Requests:** `CreateProjectRequest`, `UpdateProjectRequest` (no `status` field), `TransitionProjectStatusRequest`.
- **Resources:** `ProjectResource` exposes bilingual names, geo, `project_type`, budgets; `PhaseResource` exposes `completion_percentage` alongside `progress`.

## Migrations

1. `evolve_projects_for_stage_12`: add nullable `name_ar`, `name_en`, `city`, `district`, `location_lat`, `location_lng`, `project_type`, `budget_estimated`, `budget_actual`; `UPDATE` map legacy `status` values; optional backfill `name_ar`/`name_en` from `name`.
2. `evolve_phases_for_stage_12`: add `name_ar`, `name_en`, `sort_order` (default 0).

## API Routes (`routes/api.php`)

- `PUT projects/{project}/status` + `GET projects/{project}/timeline` inside `auth:sanctum`, with `role:customer,contractor,supervising_architect,admin` for status; timeline same as phases read group (all project stakeholder roles including field_engineer via policy).

Actually timeline visibility = view policy - any role that can view - so middleware: same as GET show - only auth:sanctum, authorize in controller.

Status route: restrict roles that can change lifecycle - spec says customer owner, contractor, supervising architect, admin - exclude field_engineer.

## Frontend

- `pages/projects/index.vue` — fetch `GET /api/v1/projects`, table of name, status, dates.
- `pages/projects/new.vue` — POST create, redirect to detail.
- `pages/projects/[id].vue` — GET show, display badge, PUT status select (if allowed — infer from role client-side and handle 403), GET timeline section.

## Testing

- Extend `ProjectControllerTest` and add focused tests for status + timeline + policy view for field engineer with report.
- Update `DatabaseSchemaTest`, `EnumCastTest`, `ProjectStatusTest`, `ProjectFactory`.

## Risks

- Enum migration must run before application code expecting new cases deploys (single release OK in branch).

## Guardian pre-implementation

- **Architecture checker:** PASS — layering enforced.
- **API designer:** PASS — REST + error contract preserved.
