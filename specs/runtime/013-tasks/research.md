# Research — Tasks

## Laravel 11 / Sanctum

- Route model binding for `Task` scoped via `TaskService::ensureBelongsToProject` to prevent IDOR.
- Policy `before` not used; admin checks remain explicit per policy methods.

## Nuxt UI v4

- Use `UCard`, `UButton`, layout with CSS grid for Kanban columns; `dir=\"rtl\"` inherited from app shell.

## References

- Existing `BaseController::sendSuccess`, `TaskResource`, `ProjectController` patterns.
