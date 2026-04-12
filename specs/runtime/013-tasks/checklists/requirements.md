# Requirements Checklist — Tasks

- [ ] RBAC middleware on all new task routes
- [ ] Form Request validation for create/update/assign/status/comment
- [ ] `TaskService` owns transitions and assignment rules
- [ ] `TaskRepository` encapsulates queries (no Eloquent in service beyond repository returns)
- [ ] `TaskPolicy` / `ProjectPolicy` enforced server-side for every endpoint
- [ ] Error contract (`success`, `data`, `message`, `errors`) preserved
- [ ] Arabic labels in validation messages where applicable
- [ ] Migrations forward-only with `down()`
- [ ] Legacy phase-nested task routes still pass existing tests after refactor
- [ ] Nuxt UI + RTL for task board/list
