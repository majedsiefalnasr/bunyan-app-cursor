# Security Checklist — Tasks

- [x] No task mutation without `auth:sanctum`
- [x] Role groups match read vs write matrix for projects
- [x] Task comments attributed to authenticated user only (`user_id` from auth)
- [x] Assign/status endpoints reject cross-project task IDs (404)
