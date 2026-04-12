# Testing Guide — Document Management

> **Phase:** 03_PROJECT_MANAGEMENT > **Generated:** 2026-04-12T21:00:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
# Configure MySQL (or sqlite) in .env, then:
php artisan migrate:fresh --seed

cd ../frontend && npm install
```

## Automated tests

```bash
cd backend && php artisan test --filter=DocumentApiTest
cd ../frontend && npm run lint && npm run typecheck && npm run test
```

## Manual — API (Sanctum token)

1. Register/login as a **customer** and capture the Bearer token from `/api/v1/auth/login`.
2. Create a project (customer) via `POST /api/v1/projects` or use a seeded project id (e.g. `1` if seeder creates one).
3. `POST /api/v1/projects/{id}/documents` with multipart body:
   - `file`: small JPEG or PDF under 25MB
   - `title`: `اختبار رفع`
   - `category`: `photo` (or another enum: `blueprint`, `contract`, …)
4. `GET /api/v1/projects/{id}/documents` — expect the new row in `data`.
5. `GET /api/v1/documents/{documentId}` — metadata success.
6. `GET /api/v1/documents/{documentId}/download` — file downloads.
7. Repeat `POST` with same `file` plus `document_id={documentId}` — expect `data.version` incremented.
8. `GET /api/v1/documents/{documentId}/versions` — two version rows.
9. As unrelated **contractor** token, `GET /api/v1/projects/{id}/documents` — expect **403** if not on the project.

## Manual — Nuxt UI

1. `npm run dev` in `frontend/`, log in as a user who can open a project (customer on own project, contractor assigned, etc.).
2. Open `/projects/{id}` — click **المستندات** / **Documents**.
3. Enter a title, pick a category, upload an image — list refreshes with the new document.
4. Open **الإصدارات** — modal lists versions after a second upload with same logical document (optional second POST with `document_id` via API or future UI control).
5. Click **تنزيل** — browser receives a file.
6. Click **حذف** — row disappears from list; API marks soft-deleted.

## Migration check

```bash
cd backend && php artisan migrate --pretend
```

If this fails in CI or locally, verify database credentials in `.env` (see `VALIDATION_REPORT.md`).
