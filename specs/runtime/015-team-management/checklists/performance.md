# Performance Checklist — Team Management

- [ ] Team list eager-loads `user` for members and `invitedByUser` for invitations
- [ ] Indexes on `project_members(project_id)` and `project_invitations(project_id, email)`
