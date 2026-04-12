# Feature Specification — Project Pages (صفحات المشاريع)

> **Stage:** Project Pages  
> **Phase:** 07_FRONTEND_APPLICATION  
> **Authority:** `specs/phases/07_FRONTEND_APPLICATION/STAGE_32_PROJECT_PAGES.md`

## Summary

Deliver Arabic-first, RTL project management UX on Nuxt 3 using Nuxt UI against existing Laravel v1 project APIs (`/v1/projects`, nested team, timeline, tasks, documents, workflow start). Introduce `/projects/create` as a multi-step creation wizard (canonical entry), nested project shell with tab navigation for overview, tasks, documents, team, workflow, and estimates. Add composables for status badge mapping, task board columns, and workflow start; Vitest unit tests and Playwright smoke aligned with stage acceptance examples.

## User stories

1. **US1 — Project list** — As an authenticated user with project access, I can browse `/projects` and open a project with role-appropriate badges.
2. **US2 — Creation wizard** — As a customer or admin, I can complete `/projects/create` (info → optional team invite copy → optional phase names → confirm) and land on project detail after `POST /v1/projects`.
3. **US3 — Project shell** — As a project member, I see a consistent header and sub-navigation for overview, tasks, documents, team, workflow, and estimates under `/projects/:id/*`.
4. **US4 — Tasks board** — As a project member, I can view tasks grouped by status on `/projects/:id/tasks` with localized column labels and resilient loading against paginated API payloads.
5. **US5 — Workflow** — As an authorized user, I can start a workflow instance from `/projects/:id/workflow` when permitted (`POST /v1/projects/{id}/workflow/start`).
6. **US6 — Estimates (MVP)** — As a user, I can use `/projects/:id/estimates` as a client-side BOQ worksheet (line items + totals) persisted in `sessionStorage` until a dedicated BOQ API exists.
7. **US7 — Quality** — Vitest covers `useProjectStatus`, `useTaskBoard`, and `useProjectWorkflow` helpers; Playwright covers auth gate and wizard testids.

## Functional requirements

### Routing (Nuxt)

- `pages/projects/index.vue` — list; primary CTA targets `/projects/create`.
- `pages/projects/new.vue` — redirects to `/projects/create` (backward compatibility).
- `pages/projects/create.vue` — wizard with `data-testid` hooks per stage file examples.
- `pages/projects/[id].vue` — parent layout: back link, project title, horizontal nav, `<NuxtPage />`.
- `pages/projects/[id]/index.vue` — overview: status, timeline phases, activity feed.
- `pages/projects/[id]/team.vue` — team table, invitations, invite form (same RBAC as today).
- `pages/projects/[id]/tasks.vue` — enhanced board using shared composable.
- `pages/projects/[id]/documents.vue` — unchanged behavior, nested under shell.
- `pages/projects/[id]/workflow.vue` — status display + start workflow action.
- `pages/projects/[id]/estimates.vue` — BOQ worksheet (client-only).

### API alignment

- Create project: `POST /v1/projects` with `CreateProjectRequest` fields (`name`, `budget`, `location` required; omit `start_date` unless future-dated to satisfy `after:today`).
- Tasks: `GET /v1/projects/{project}/tasks?per_page=100` with array or paginated `data` shape.
- Team: existing `GET/POST /v1/projects/{project}/team`.
- Workflow: `POST /v1/projects/{project}/workflow/start` only (no new backend endpoints).

### Non-goals

- Backend BOQ persistence, Gantt third-party integration, drag-and-drop task reorder (may follow when `USortable` is adopted).
- Changing Laravel policies beyond what existing endpoints already enforce.

## Acceptance criteria

- All touched pages use `useApi()`, `auth` middleware, RTL-safe layout, and i18n keys in `ar.json` / `en.json`.
- Nested routes render inside the project shell without duplicate headers.
- `npm run lint`, `npm run typecheck`, `npm run test` pass for frontend; `composer run test` passes where backend tests are touched.

## Technical constraints

- Nuxt UI 2.x components already in repo; prefer `UCard`, `UButton`, `UBadge`, `UFormField`, `UInput`, `UAlert`.
- No raw `$fetch` outside `useApi`.

## Clarifications

(Added in Step 2 — see `reports/CLARIFY_REPORT.md`.)
