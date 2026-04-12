# Security Checklist — Team Management

- [ ] All team routes behind `auth:sanctum` and role middleware where required
- [ ] `ProjectPolicy` prevents IDOR on project and team user targets
- [ ] Invitation tokens stored hashed; raw token never logged or persisted
- [ ] Accept invitation validates authenticated user email matches invitation email
- [ ] Rate limiting on invitation accept and team mutation routes where appropriate
