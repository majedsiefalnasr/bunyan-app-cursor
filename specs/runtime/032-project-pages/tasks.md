# Tasks — Project Pages

- [ ] T001 [US3] `frontend/pages/projects/[id].vue` — project shell with nav and `<NuxtPage />`, provide `projectDetail`
- [ ] T002 [US3] `frontend/pages/projects/[id]/index.vue` — overview (status, timeline, activity)
- [ ] T003 [US3] `frontend/pages/projects/[id]/team.vue` — team list, invites, RBAC-aware form
- [ ] T004 [US5] `frontend/pages/projects/[id]/workflow.vue` — workflow start action + messaging
- [ ] T005 [US6] `frontend/pages/projects/[id]/estimates.vue` — client BOQ worksheet + sessionStorage persistence
- [ ] T006 [US2] `frontend/pages/projects/create.vue` — multi-step wizard with stage testids
- [ ] T007 [US2] `frontend/pages/projects/new.vue` — redirect to `/projects/create`
- [ ] T008 [US1] `frontend/pages/projects/index.vue` — CTA to create + status badges via composable
- [ ] T009 [US4] `frontend/pages/projects/[id]/tasks.vue` — use `useTaskBoard` + localized columns
- [ ] T010 [US7] `frontend/composables/useProjectStatus.ts` — badge color mapping
- [ ] T011 [US7] `frontend/composables/useTaskBoard.ts` — columns + payload normalization
- [ ] T012 [US7] `frontend/composables/useProjectWorkflow.ts` — POST workflow start helper
- [ ] T013 [US7] `frontend/tests/unit/project-pages.spec.ts` — Vitest for composables
- [ ] T014 [US7] `frontend/tests/e2e/projects.spec.ts` — Playwright smoke for wizard testids / auth gate
- [ ] T015 `frontend/locales/en.json` + `frontend/locales/ar.json` — new keys for wizard, nav, workflow, estimates, task columns
- [ ] T016 `frontend/pages/projects/[id]/documents.vue` — align layout with shell (remove duplicate back row if any)
