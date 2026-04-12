# Testing Guide — Projects

> **Phase:** 03_PROJECT_MANAGEMENT > **Generated:** 2026-04-12T12:55:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed

cd ../frontend && npm install
```

Set `NUXT_PUBLIC_API_BASE_URL` (or your `runtimeConfig`) to the Laravel API base, for example `http://localhost:8000/api`.

## Automated checks

```bash
cd backend && composer run lint && php artisan test
cd ../frontend && npm run lint && npm run typecheck && npm run test
```

## Manual API scenarios (Sanctum)

1. **Login as customer** — `POST /api/v1/auth/login` with a seeded customer email/password from `DatabaseSeeder` / factories.
2. **List projects** — `GET /api/v1/projects` with `Authorization: Bearer <token>`. Expect only projects where the user is customer, contractor, supervising architect, or (for field engineers) projects with their reports.
3. **Create project** — `POST /api/v1/projects` with JSON:
   ```json
   {
     "name": "فيلا الرياض",
     "budget": 250000,
     "location": "الرياض"
   }
   ```
   Expect `201`, `data.status` = `draft`.
4. **Transition status** — `PUT /api/v1/projects/{id}/status` with `{ "status": "planning" }` while still `draft`. Expect `200` and `data.status` = `planning`.
5. **Invalid transition** — from `draft` send `{ "status": "closed" }`. Expect `422`.
6. **Timeline** — `GET /api/v1/projects/{id}/timeline`. Expect `data.project` and `data.phases` array.
7. **IDOR guard** — log in as a different user with no relation to the project; `GET /api/v1/projects/{id}` should return **403**.

## Manual UI scenarios

1. Log in through `/auth/login` as a customer.
2. Open `/projects` — list should load without console errors.
3. Open `/projects/new`, submit name `اختبار واجهة`, budget `50000`, location `جدة` — should redirect to `/projects/{id}`.
4. On the project detail page, confirm status badge and timeline section render.

## Field engineer visibility

1. Create a project and a field-engineer user with a `reports` row (`project_id`, `created_by` = engineer).
2. Act as the engineer: `GET /api/v1/projects/{id}` should return **200**; without the report row, the same call should return **403**.
