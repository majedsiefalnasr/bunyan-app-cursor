# Requirements Checklist — Tasks

- [x] RBAC middleware on all new task routes
- [x] Form Request validation for create/update/assign/status/comment
- [x] `TaskService` owns transitions and assignment rules
- [x] `TaskRepository` encapsulates queries (no Eloquent in service beyond repository returns)
- [x] `TaskPolicy` / `ProjectPolicy` enforced server-side for every endpoint
- [x] Error contract (`success`, `data`, `message`, `errors`) preserved
- [x] Arabic labels in validation messages where applicable
- [x] Migrations forward-only with `down()`
- [x] Legacy phase-nested task routes still pass existing tests after refactor
- [x] Nuxt UI + RTL for task board/list
