# Performance Checklist — Team Management

- [x] Team list eager-loads `user` for members and `invitedByUser` for invitations
- [x] Indexes on `project_members(project_id)` and `project_invitations(project_id, email)`
