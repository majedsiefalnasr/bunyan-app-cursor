# Security Checklist — Team Management

- [x] All team routes behind `auth:sanctum` and role middleware where required
- [x] `ProjectPolicy` prevents IDOR on project and team user targets
- [x] Invitation tokens stored hashed; raw token never logged or persisted
- [x] Accept invitation validates authenticated user email matches invitation email
- [x] Rate limiting on invitation accept and team mutation routes where appropriate
