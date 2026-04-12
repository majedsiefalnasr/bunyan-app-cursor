# Security Checklist — Tasks

- [ ] No task mutation without `auth:sanctum`
- [ ] Role groups match read vs write matrix for projects
- [ ] Task comments attributed to authenticated user only (`user_id` from auth)
- [ ] Assign/status endpoints reject cross-project task IDs (404)
