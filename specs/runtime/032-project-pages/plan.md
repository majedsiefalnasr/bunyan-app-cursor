# Technical Plan — Project Pages

## Architecture

- **Frontend only** — no new Laravel routes; consume documented v1 endpoints.
- **Nested routing** — `pages/projects/[id].vue` parent with `<NuxtPage />`; children under `pages/projects/[id]/`.
- **Provide/inject** — Parent loads `GET /v1/projects/{id}` once; provides `projectDetail` for children.

## File map

| Path                                         | Purpose                              |
| -------------------------------------------- | ------------------------------------ |
| `frontend/pages/projects/create.vue`         | Multi-step wizard                    |
| `frontend/pages/projects/new.vue`            | Redirect to create                   |
| `frontend/pages/projects/[id].vue`           | Shell + nav + NuxtPage               |
| `frontend/pages/projects/[id]/index.vue`     | Overview                             |
| `frontend/pages/projects/[id]/team.vue`      | Team management                      |
| `frontend/pages/projects/[id]/workflow.vue`  | Workflow start                       |
| `frontend/pages/projects/[id]/estimates.vue` | Client BOQ                           |
| `frontend/pages/projects/[id]/tasks.vue`     | Board (refactor)                     |
| `frontend/pages/projects/[id]/documents.vue` | Move under shell (content preserved) |
| `frontend/composables/useProjectStatus.ts`   | Badge colors                         |
| `frontend/composables/useTaskBoard.ts`       | Columns + normalize                  |
| `frontend/composables/useProjectWorkflow.ts` | POST start                           |
| `frontend/tests/unit/project-pages.spec.ts`  | Vitest                               |
| `frontend/tests/e2e/projects.spec.ts`        | Playwright                           |

## Middleware & auth

- All pages: `definePageMeta({ layout: 'default', middleware: 'auth' })`.

## Error handling

- Use `useApi` error shape; `UAlert` for recoverable load failures.

## Logging

- No new backend logging; frontend avoids console noise in production paths.

## i18n

- Extend `locales/en.json` and `locales/ar.json` with `projects.wizard_*`, `projects.nav_*`, `projects.workflow_*`, `projects.estimates_*`, `projects.tasks_col_*`.

## Testing

- Vitest: composable pure functions.
- Playwright: `/projects` redirect when logged out (if consistent with app), `/projects/create` testids presence (mocked session optional — follow existing playwright config).

## Rollout

- Single PR from `spec/032-project-pages`; no feature flags.
