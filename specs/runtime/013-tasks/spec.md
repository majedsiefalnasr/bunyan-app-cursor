# Specification — Tasks (STAGE_13)

**Phase:** 03_PROJECT_MANAGEMENT  
**Runtime:** `specs/runtime/013-tasks`  
**Authority:** `specs/phases/03_PROJECT_MANAGEMENT/STAGE_13_TASKS.md`

## Summary

Deliver project-scoped task management with bilingual titles, priorities, lifecycle statuses, comments, optional dependencies, REST API under `/api/v1/projects/{project}/tasks` and `/api/v1/tasks/{task}`, thin controllers, `TaskService` + `TaskRepository`, policies, Form Requests, Arabic-first responses, and a Nuxt dashboard surface (list + Kanban-style board) consistent with `DESIGN.md`.

## User stories

1. **US1 — List & filter** As a project participant, I can list all tasks for a project with filters (phase, status, priority, assignee) so I can track work across phases.
2. **US2 — Create** As a contractor or supervising architect, I can create a task against a project (selecting phase) with Arabic/English titles and priority.
3. **US3 — Detail & update** As an authorized user, I can read and update task fields (titles, description, dates, hours, budget).
4. **US4 — Assign** As a contractor or supervising architect, I can assign/reassign a task to a team user who belongs to the project context.
5. **US5 — Status transitions** As an authorized user, I can move a task through `todo → in_progress → in_review → done` and set `blocked` with validation rules.
6. **US6 — Comments** As an authorized user, I can append comments to a task thread.
7. **US7 — Dependencies (MVP)** As a supervising architect, I can register that task A depends on task B (same project), validated to prevent cycles in MVP via simple checks.

## Acceptance criteria

- All new routes are behind `auth:sanctum` and `role:` middleware consistent with existing project read/write split.
- Every mutating endpoint uses a dedicated Form Request; authorization uses `TaskPolicy` / `ProjectPolicy` as appropriate.
- Controllers delegate to `TaskService`; repositories own Eloquent queries.
- API success envelope: `{ success, data, message, errors }` via existing `BaseController`.
- `tasks` row stores `project_id` (denormalized for query performance) in addition to `phase_id`; `project_id` is always consistent with `phase.project_id`.
- Status values persisted as lowercase snake_case: `todo`, `in_progress`, `in_review`, `done`, `blocked`.
- Legacy nested routes `projects/{project}/phases/{phase}/tasks` remain supported; data model is unified.
- PHPUnit feature tests cover list/create/show/update/assign/status/comment happy paths and at least one forbidden role path per mutating route.
- Frontend: project detail area exposes a Tasks view with RTL-safe layout using Nuxt UI.

## Non-goals (deferred)

- Full DAG cycle detection beyond immediate duplicate / self-dependency prevention.
- Real-time collaboration / websockets for the board.
- Gantt chart view.

## Clarifications

### Session 2026-04-12

| #   | Topic                      | Resolution                                                                                                                                      |
| --- | -------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | Legacy phase-nested routes | Keep `projects/{project}/phases/{phase}/tasks` for backward compatibility; new project-level routes are additive.                               |
| 2   | Status enum vs DB          | Migrate existing `pending/completed/approved/rejected` to `todo/done/blocked` mapping; `in_progress` unchanged.                                 |
| 3   | `budget` field             | Retain `budget` column for compatibility; new UI may hide behind “estimated cost” later.                                                        |
| 4   | Field engineer task access | Read-only list/detail where `ProjectPolicy@view` already allows field engineers with reports on project.                                        |
| 5   | Assignee validation        | `assigned_to` must be a user who can `view` the parent project (customer, contractor, architect, admin, or field engineer with report linkage). |
