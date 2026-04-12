# Tasks — Implementation checklist

- [x] T001 [P] [US1] Add migration `backend/database/migrations/2026_04_12_150000_enhance_tasks_stage_13.php` for columns, tables, status remap, backfill
- [x] T002 [P] [US5] Replace `backend/app/Enums/TaskStatus.php` with todo/in_progress/in_review/done/blocked + labels
- [x] T003 [P] [US2] Add `backend/app/Enums/TaskPriority.php`
- [x] T004 [US2] Extend `backend/app/Models/Task.php` with project, titles, priority, comments, dependencies, casts
- [x] T005 [P] [US6] Add `TaskComment`, `TaskDependency` models under `backend/app/Models/`
- [x] T006 [US1] Extend `backend/app/Repositories/TaskRepository.php` with project-scoped queries
- [x] T007 [US1-US6] Implement `backend/app/Services/TaskService.php` (CRUD slice, assign, transition, comment)
- [x] T008 [US1] Add Form Requests under `backend/app/Http/Requests/Api/V1/` for project tasks and workspace actions
- [x] T009 [US1] Add `ProjectTaskController` + `TaskWorkspaceController` under `backend/app/Http/Controllers/Api/V1/`
- [x] T010 [US1] Register routes in `backend/routes/api.php` with correct role middleware
- [x] T011 [US1] Update `TaskResource`, `TaskPolicy`, refactor `TaskController` to use `TaskService` for store/update
- [x] T012 [US1] Add `backend/tests/Feature/Api/V1/ProjectTaskApiTest.php` and update `TaskControllerTest` / `TaskStatusTest` / factories
- [x] T013 [US1] Add `frontend/pages/projects/[id]/tasks.vue` Kanban + list fetch
- [x] T014 [US1] Wire API client/composable if needed under `frontend/composables/` or extend existing project API usage
