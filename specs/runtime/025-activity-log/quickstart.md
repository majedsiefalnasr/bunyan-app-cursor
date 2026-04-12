# Quickstart — Activity Log

1. `cd backend && php artisan migrate`
2. Seed or create a project as a stakeholder user; update project to generate `updated` activity.
3. `php artisan activity-log:prune --dry-run` (optional) to inspect prune selection.
4. Call `GET /api/v1/admin/activity-log` as admin bearer token.
5. Call `GET /api/v1/projects/{id}/activity` as stakeholder bearer token.
6. Open Nuxt `/{locale}/admin/activity-log` in browser as admin.
