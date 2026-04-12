# Testing Guide — Media Library

> **Phase:** 05_COMMUNICATION_AND_MEDIA > **Generated:** 2026-04-12T16:35:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
# Configure DB_* then:
php artisan migrate

cd ../frontend && npm install
```

Set `NUXT_PUBLIC_API_BASE_URL` (or project equivalent) to your Laravel API base, for example `http://127.0.0.1:8000/api`.

## Running Tests

### Backend feature tests (Media)

```bash
cd backend && php artisan test --filter=MediaApiTest
```

### Full backend suite

```bash
cd backend && composer run test
```

### Frontend

```bash
cd frontend && npm run test && npm run typecheck && npm run lint
```

## Manual Test Scenarios

### Scenario 1 — Upload and list (customer)

**Preconditions:**

- Seeded or registered user with role `customer` and a valid Sanctum session (SPA cookie or bearer token).

**Steps:**

1. `POST http://127.0.0.1:8000/api/v1/media/upload` with multipart field `file` (JPEG under 15MB) and optional `collection=manual-test`.
2. `GET http://127.0.0.1:8000/api/v1/media?per_page=10` with the same auth.

**Expected Result:**

- `201` on upload with `success: true` and `data.id` populated.
- `200` on index; response `data` includes the new item (paginated wrapper may nest items under `data`).

### Scenario 2 — Cross-user access denied

**Preconditions:**

- User A and User B both authenticated.

**Steps:**

1. As User A, upload any allowed image; capture `data.id`.
2. As User B, `GET /api/v1/media/{id}`.

**Expected Result:**

- `403` for User B (non-admin).

### Scenario 3 — Attach to project

**Preconditions:**

- Customer User C owns `project_id = 1` (replace with real ID from DB).
- Contractor User D does **not** have `ProjectPolicy::view` on that project.

**Steps:**

1. As D, `POST /api/v1/media/upload` with `file`, `mediable_type=App\\Models\\Project`, `mediable_id=1`.
2. As C, repeat the same request.

**Expected Result:**

- `403` for D.
- `201` for C with `data.mediable_id` equal to the project id.

### Scenario 4 — Prune temporary media

**Preconditions:**

- A `media` row with `is_temporary=true` and `created_at` older than 25 hours (adjust in DB for staging).

**Steps:**

1. `php artisan media:prune-temporary --hours=24`

**Expected Result:**

- Command prints `Pruned N temporary media item(s).` with `N >= 1`; row removed from `media` table.

### Scenario 5 — Nuxt media page

**Preconditions:**

- Frontend dev server running; user logged in through existing auth flow.

**Steps:**

1. Open `http://localhost:3000/media` (or your dev host).
2. Drag a small JPEG onto the drop zone (or use “Choose file”).

**Expected Result:**

- After upload, the new file appears in the grid; “Open file” navigates to the storage URL in a new tab.
