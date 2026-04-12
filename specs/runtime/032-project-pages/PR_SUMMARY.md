# PR — Project Pages

## Summary

**Stage:** Project Pages  
**Phase:** 07_FRONTEND_APPLICATION  
**Branch:** `spec/032-project-pages` → `develop`  
**Tasks:** 16 / 16 completed

## What Changed

### Backend

- None.

### Frontend

- Nested `pages/projects/[id].vue` shell with `<NuxtPage />`, injectable project summary, and sub-pages: overview, tasks, documents, team, workflow, estimates.
- `/projects/create` multi-step wizard with `data-testid` hooks; `/projects/new` redirects to create.
- Composables: `useProjectShellContext`, `useProjectStatus`, `useTaskBoard`, `useProjectWorkflow`.
- i18n keys (ar/en) for navigation, wizard, workflow, estimates, localized Kanban column titles.
- Vitest `project-pages.spec.ts`; Playwright `projects.spec.ts`.
- `requiresAuth: true` on all project routes touched.

### Database

- None.

## Breaking Changes

- None. `/projects/new` now redirects to `/projects/create`.

## Testing

- [x] `composer run test` (PHPUnit) — pass in session run
- [x] `npm run test` (Vitest) — pass
- [x] `npm run lint` / `npm run typecheck` — pass
- [x] `composer run lint` / `composer run analyze` — pass
- [x] Playwright `tests/e2e/projects.spec.ts` (chromium) — pass

## Checklist

- [x] Arabic/RTL support (keys + existing layout)
- [x] Error handling via `useApi` / `UAlert` patterns
- [x] No new backend RBAC surface

## Related

- Stage File: `specs/phases/07_FRONTEND_APPLICATION/STAGE_32_PROJECT_PAGES.md`
- Testing Guide: `specs/runtime/032-project-pages/guides/TESTING_GUIDE.md`
