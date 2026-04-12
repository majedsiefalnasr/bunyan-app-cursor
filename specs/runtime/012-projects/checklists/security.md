# Security Checklist — Projects

- [x] `show` and `timeline` authorize via `ProjectPolicy::view` (no IDOR)
- [x] Status transitions use dedicated Form Request + service graph (no arbitrary status via mass assignment)
- [x] List queries remain role-scoped; field engineers only see projects they reported on
- [x] Sanctum on all `/api/v1/projects*` mutations and sensitive reads
