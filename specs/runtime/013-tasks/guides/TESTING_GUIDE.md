# Testing Guide — Tasks

> **Phase:** 03_PROJECT_MANAGEMENT > **Generated:** 2026-04-12T16:00:00Z

## Prerequisites

```bash
cd backend && composer install
cp .env.example .env && php artisan key:generate
# Use sqlite for local smoke, or valid MySQL credentials
php artisan migrate
php artisan db:seed   # optional if seeders exist
```

```bash
cd frontend && npm install
```

## Automated checks

```bash
cd backend && composer run lint && composer run analyze && php artisan test
```

```bash
cd frontend && npm run lint && npm run typecheck && npm run test
```

```bash
cd backend && DB_CONNECTION=sqlite DB_DATABASE=":memory:" php artisan migrate --pretend --no-interaction
```

## Manual API scenarios

Use a Sanctum token from `POST /api/v1/auth/login` for a user who is `contractor` or `supervising_architect` on a seeded project.

### List project tasks

```http
GET /api/v1/projects/1/tasks?per_page=20
Authorization: Bearer <token>
```

Expect `200`, `success: true`, paginated `data` with `title_ar`, `status`, `priority`.

### Create task

```http
POST /api/v1/projects/1/tasks
Authorization: Bearer <token>
Content-Type: application/json

{"phase_id": 1, "title_ar": "اختبار يدوي", "budget": 500}
```

Expect `201` and `data.status` = `todo`.

### Transition status

```http
PUT /api/v1/tasks/1/status
Authorization: Bearer <token>
Content-Type: application/json

{"status": "in_progress"}
```

Expect `200`; repeat with `in_review` then `done` following the allowed graph.

### Comment

```http
POST /api/v1/tasks/1/comments
Authorization: Bearer <token>
Content-Type: application/json

{"body": "ملاحظة من الميدان"}
```

Expect `201` and comment `body` echoed.

### RBAC negative

As `customer` (not assignee), `POST /api/v1/projects/1/tasks` must return `403`.

## Frontend

1. Log in as contractor (or other role with project access).
2. Open `/projects/{id}` — confirm **المهام** button appears.
3. Navigate to `/projects/{id}/tasks` — Kanban columns render; tasks appear under correct status column.
