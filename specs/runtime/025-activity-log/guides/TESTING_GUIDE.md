# Testing Guide — Activity Log (STAGE_25)

> **Generated:** 2026-04-12T13:15:00Z

## Prerequisites

- Backend: `cd backend && composer install && cp .env.example .env` (configure `DB_*`), `php artisan key:generate`, `php artisan migrate`.
- Frontend: `cd frontend && npm install`.
- Seed or register at least one **admin** user and one **customer** + **contractor** linked to a project (see factories / existing seeders).

## Automated checks

```bash
cd backend && composer run lint && composer run analyze && php artisan test --filter=ActivityLogTest
cd frontend && npm run lint && npm run typecheck && npm run test
cd backend && php artisan migrate --pretend --no-interaction
```

## Manual — Admin activity list

1. Log in as admin in the Nuxt app and open `/admin/activity-log` (or locale-prefixed path if your router uses i18n prefixes).
2. Confirm the table loads without 403.
3. In browser devtools, confirm `GET /api/v1/admin/activity-log?page=1&per_page=15` returns `success: true` and paginated `data.data` / `data.meta`.

## Manual — Subject timeline (API)

1. Obtain a Sanctum bearer token for a **project stakeholder** (customer on the project, contractor assigned, etc.).
2. Note a valid `PROJECT_ID` the user may view.
3. Run:

```bash
curl -sS -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json" \
  "http://localhost:8000/api/v1/projects/<PROJECT_ID>/activity?per_page=10"
```

4. Expect `200` and `success: true`. Repeat with a user who is **not** on the project; expect `403`.

## Manual — Project update generates log

1. As contractor (assigned to project), `PUT /api/v1/projects/{id}` with a changed `name`.
2. Query `activity_logs` (or call subject activity endpoint) and confirm an `updated` row exists for that `subject_id`.

## Manual — Prune command

```bash
cd backend && php artisan activity-log:prune --dry-run
cd backend && php artisan activity-log:prune --days=365
```

Expect non-negative counts printed; with `--dry-run`, database row count must remain unchanged.
