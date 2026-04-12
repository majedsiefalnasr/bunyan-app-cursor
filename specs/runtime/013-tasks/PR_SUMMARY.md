# PR — Tasks

## Summary

**Stage:** Tasks  
**Phase:** 03_PROJECT_MANAGEMENT  
**Branch:** `spec/013-tasks` → `develop`  
**Tasks:** 14 / 14 completed

## What Changed

### Backend

- Migration enhancing `tasks`, adding `task_comments` and `task_dependencies`, remapping legacy statuses.
- `TaskStatus` / `TaskPriority` enums; `TaskService` for create/update/assign/transition/comment.
- `ProjectTaskController`, `TaskWorkspaceController`; new Form Requests; expanded `TaskResource` and `TaskPolicy`.
- Feature tests `ProjectTaskApiTest`; policy and enum tests updated.

### Frontend

- `pages/projects/[id]/tasks.vue` Kanban board; link from project detail; i18n keys `projects.open_tasks`, `projects.tasks_title`, `projects.tasks_subtitle`.

### Database

- `2026_04_12_150000_enhance_tasks_stage_13.php`

## Breaking Changes

- Task status string values changed (`pending` → `todo`, `completed`/`approved` → `done`, `rejected` → `blocked`). API consumers must use new values.

## Testing

- [x] Unit tests pass (`php artisan test --testsuite=Unit`)
- [x] Feature tests pass (`php artisan test --testsuite=Feature`)
- [x] Frontend tests pass (`npm run test`)
- [x] Lint passes (`composer run lint` / `npm run lint`)
- [x] Type check passes (`composer run analyze`, `npm run typecheck`)
- [x] Migration dry-run documented (`DB_CONNECTION=sqlite` pretend)

## Checklist

- [x] RBAC middleware applied on all new routes
- [x] Form Request validation on all new endpoints
- [x] Arabic/RTL support verified (labels + existing RTL shell)
- [x] Error contract followed
- [x] Eager loads on list/detail for tasks
- [x] Migration tested (`migrate --pretend` with sqlite)

## Related

- Stage File: `specs/phases/03_PROJECT_MANAGEMENT/STAGE_13_TASKS.md`
- Testing Guide: `specs/runtime/013-tasks/guides/TESTING_GUIDE.md`
