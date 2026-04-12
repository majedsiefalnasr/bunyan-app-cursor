# Requirements Checklist — Team Management (STAGE_15)

- [x] Migrations for `project_members` and `project_invitations` with rollback
- [x] Owner backfill for existing projects
- [x] `ProjectRole` enum and Eloquent models with relationships
- [x] Repositories + `ProjectTeamService` business rules (owner safeguards, invitation hashing)
- [x] Form Requests for team store/update and invitation accept
- [x] API Resources and routes under `/api/v1` with RBAC middleware
- [x] `ProjectPolicy` extensions for members and team management
- [x] Feature tests for team and invitation flows
- [x] Nuxt project detail team panel + i18n strings
- [x] Activity logging on member / invitation lifecycle
