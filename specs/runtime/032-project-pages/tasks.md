# Tasks — Project Pages

- [x] T001 [US3] `frontend/pages/projects/[id].vue` — project shell with nav and `<NuxtPage />`, provide `projectDetail`
- [x] T002 [US3] `frontend/pages/projects/[id]/index.vue` — overview (status, timeline, activity)
- [x] T003 [US3] `frontend/pages/projects/[id]/team.vue` — team list, invites, RBAC-aware form
- [x] T004 [US5] `frontend/pages/projects/[id]/workflow.vue` — workflow start action + messaging
- [x] T005 [US6] `frontend/pages/projects/[id]/estimates.vue` — client BOQ worksheet + sessionStorage persistence
- [x] T006 [US2] `frontend/pages/projects/create.vue` — multi-step wizard with stage testids
- [x] T007 [US2] `frontend/pages/projects/new.vue` — redirect to `/projects/create`
- [x] T008 [US1] `frontend/pages/projects/index.vue` — CTA to create + status badges via composable
- [x] T009 [US4] `frontend/pages/projects/[id]/tasks.vue` — use `useTaskBoard` + localized columns
- [x] T010 [US7] `frontend/composables/useProjectStatus.ts` — badge color mapping
- [x] T011 [US7] `frontend/composables/useTaskBoard.ts` — columns + payload normalization
- [x] T012 [US7] `frontend/composables/useProjectWorkflow.ts` — POST workflow start helper
- [x] T013 [US7] `frontend/tests/unit/project-pages.spec.ts` — Vitest for composables
- [x] T014 [US7] `frontend/tests/e2e/projects.spec.ts` — Playwright smoke for wizard testids / auth gate
- [x] T015 `frontend/locales/en.json` + `frontend/locales/ar.json` — new keys for wizard, nav, workflow, estimates, task columns
- [x] T016 `frontend/pages/projects/[id]/documents.vue` — align layout with shell (remove duplicate back row if any)
