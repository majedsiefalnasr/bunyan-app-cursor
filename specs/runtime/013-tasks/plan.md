# Technical Plan — Tasks (STAGE_13)

## Architecture

- **Migrations:** Add `project_id`, `title_ar`, `title_en`, `priority`, `due_date`, `estimated_hours`, `actual_hours`, `sort_order`, `created_by` to `tasks`; create `task_comments`, `task_dependencies`; remap legacy statuses; backfill `project_id` / `title_ar` from `phases` / `name`.
- **Enums:** `TaskStatus` → `todo`, `in_progress`, `in_review`, `done`, `blocked`. New `TaskPriority`: `low`, `medium`, `high`, `urgent`.
- **Models:** `Task` belongsTo `Project`, `Phase`, `creator` (User); hasMany `comments`, `outgoingDependencies`, `incomingDependencies`. `TaskComment`, `TaskDependency` models.
- **Repository:** Extend `TaskRepository` with `paginateForProject`, `findForProjectOrFail`.
- **Service:** `TaskService` — create/update for project routes, `assign`, `transitionStatus`, `addComment`, `addDependency` with validation and `Log::info` on transitions.
- **Controllers:** `ProjectTaskController` (index/store), `TaskWorkspaceController` (show/update/assign/status/comment store). Keep `TaskController` for legacy phase routes; delegate creates/updates to `TaskService` where practical.
- **Policies:** Extend `TaskPolicy::view` to use `$task->project` when loaded; ensure `create` accepts `Phase` for legacy; add `comment`/`transition` abilities mirroring `update`.
- **Routes:** Under same middleware bands as existing tasks; add `GET|POST projects/{project}/tasks`, `GET|PUT tasks/{task}`, `PUT tasks/{task}/assign`, `PUT tasks/{task}/status`, `POST tasks/{task}/comments`.
- **Frontend:** `pages/projects/[id]/tasks.vue` — fetch project tasks, Kanban columns by status, `UButton`/`UCard` RTL.

## Guardian pre-checks

- No ADR conflict: extends existing Laravel modular API.
- RBAC: reuse role groups from `routes/api.php` for read vs contractor write vs architect delete patterns.

## Testing

- Feature tests: `ProjectTaskApiTest` (new file) + update `TaskControllerTest` for enum/factory changes.
- `php artisan migrate --pretend` in CI validation.
