# Requirements Checklist — Team Management (STAGE_15)

- [ ] Migrations for `project_members` and `project_invitations` with rollback
- [ ] Owner backfill for existing projects
- [ ] `ProjectRole` enum and Eloquent models with relationships
- [ ] Repositories + `ProjectTeamService` business rules (owner safeguards, invitation hashing)
- [ ] Form Requests for team store/update and invitation accept
- [ ] API Resources and routes under `/api/v1` with RBAC middleware
- [ ] `ProjectPolicy` extensions for members and team management
- [ ] Feature tests for team and invitation flows
- [ ] Nuxt project detail team panel + i18n strings
- [ ] Activity logging on member / invitation lifecycle
