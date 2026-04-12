# Specification — Projects (STAGE_12)

**Phase:** 03_PROJECT_MANAGEMENT  
**Authority:** `specs/phases/03_PROJECT_MANAGEMENT/STAGE_12_PROJECTS.md`

## Summary

Deliver a production-ready **projects** module: persisted projects and phases in MySQL (existing `projects` and `phases` tables, evolved with bilingual fields and richer metadata), REST API under `/api/v1/projects` with explicit status transitions and timeline read model, service and repository layers, policies and Form Requests, and Nuxt dashboard pages (list, create, detail) aligned with Arabic-first RTL and the Bunyan error contract.

## User stories

1. **US1 — List and view (role-scoped)**  
   As an authenticated user with a stake in a project, I can list and open project details without seeing unrelated projects.

2. **US2 — Create (customer)**  
   As a customer (or admin), I can create a project with required metadata and bilingual names.

3. **US3 — Update (customer / contractor)**  
   As a customer or assigned contractor, I can update editable fields on a project I am allowed to manage.

4. **US4 — Status lifecycle**  
   As an authorized user, I can transition project status only along the allowed graph (server-enforced).

5. **US5 — Phases**  
   As a contractor (or admin), I can add and update phases under a project; supervising architect can delete phases per existing route matrix.

6. **US6 — Timeline**  
   As an authenticated stakeholder, I can read a consolidated timeline payload (project dates + phases) for dashboard visualization.

7. **US7 — Dashboard UI**  
   As a user, I can use Nuxt pages to browse projects, create a project, and inspect a project overview with status and key dates.

## Functional requirements

### Backend

- **Schema evolution (forward-only migrations):** extend `projects` with `name_ar`, `name_en` (optional complement to `name`), `city`, `district`, `location_lat`, `location_lng`, `project_type` (`residential` \| `commercial` \| `infrastructure`, nullable), `budget_estimated`, `budget_actual` (nullable decimals), retaining `name`, `budget`, and `location` for backward compatibility until consumers migrate.
- **Schema evolution — phases:** add `name_ar`, `name_en`, `sort_order` to `phases`; keep `progress` as the persisted completion percentage (maps to “completion_percentage” in API resource naming where documented).
- **Status enum:** canonical lifecycle values `draft`, `planning`, `in_progress`, `on_hold`, `completed`, `closed` with a one-time data migration from legacy `pending`, `active`, `cancelled` where present.
- **Layers:** `ProjectRepository` (queries only), `ProjectService` (create, update, list scope, status transitions, timeline assembly), thin `ProjectController`.
- **Authorization:** `ProjectPolicy` for `view`, `update`, `delete`, `transitionStatus` (or equivalent); **fix IDOR** by authorizing `show` on route model binding.
- **Field engineer access:** user with role `field_engineer` may `view` a project if they have at least one report tied to that `project_id`.
- **Endpoints (Sanctum + RBAC):**
  - `GET /api/v1/projects` — authenticated; non-admin results filtered by participation (including field engineer rule above).
  - `POST /api/v1/projects` — customer, admin (unchanged route group).
  - `GET /api/v1/projects/{project}` — authorized viewers only.
  - `PUT /api/v1/projects/{project}` — customer owner, assigned contractor, admin.
  - `PUT /api/v1/projects/{project}/status` — customer owner, contractor, supervising architect, or admin; body includes target `status`; invalid graph → 422.
  - `GET /api/v1/projects/{project}/timeline` — same visibility as `show`; returns structured milestones from project + phases.
  - Existing nested `projects/{project}/phases` routes remain; behavior unchanged except optional new fields on phase payloads.

### Frontend

- Pages: `projects` index (table/cards, RTL), `projects/new` (create form), `projects/[id]` (overview: status badge, dates, budget summary, link to phases list or embedded table).
- Reuse `useApi` / patterns from `admin/categories.vue` and suppliers pages.
- Nuxt UI components; Arabic copy for labels; Geist / DESIGN.md spacing conventions.

### Non-goals (this stage)

- Full interactive Gantt chart component (timeline API only; minimal UI list is sufficient).
- Project documents module and deep “team” management beyond existing `contractor_id` / `supervising_architect_id` FKs.
- Replacing all legacy `name` / `budget` / `location` usages in every consumer (incremental adoption via API resource).

## Acceptance criteria

- All new and existing project endpoints enforce Sanctum + policy/RBAC; unauthorized users receive 403; non-visible projects return 404 or 403 per existing API error conventions.
- Status transitions rejected with validation errors when the transition is illegal.
- PHPUnit feature tests cover list/show/create/update/status/timeline for representative roles.
- `composer run lint` and `composer run test` pass; frontend `npm run lint`, `npm run typecheck`, `npm run test` pass.

## Clarifications

### Session 2026-04-12

- **Table naming:** Implementation uses existing `phases` table (not `project_phases`); stage narrative “project_phases” refers to this table.
- **Owner field:** `customer_id` remains the project owner foreign key (equivalent to “owner_id” in stage sketch).
- **Status strings:** API uses lowercase snake values as enumerated above; labels remain Arabic where returned by resources or UI.
- **Paid / legacy:** `UpdateProjectRequest` previously allowed impossible `paid` status for projects; status changes are consolidated on `PUT .../status` with the lifecycle enum only.
