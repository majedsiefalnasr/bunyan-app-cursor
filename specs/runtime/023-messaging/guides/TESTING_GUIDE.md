# Testing Guide — Messaging

> **Phase:** 05_COMMUNICATION_AND_MEDIA > **Generated:** 2026-04-12T14:32:00Z

## Prerequisites

```bash
# From repo root — backend
cd backend && composer install && cp .env.example .env  # if missing
php artisan key:generate
php artisan migrate

# Frontend
cd ../frontend && npm install
```

Ensure `APP_URL` and `public` disk (`php artisan storage:link`) are set if testing attachment URLs in a browser.

## Automated tests

### Backend (messaging only)

```bash
cd backend
php artisan test --filter=MessagingTest
```

### Backend (full)

```bash
cd backend
composer run lint && composer run analyze && composer run test
```

### Frontend

```bash
cd frontend
npm run lint && npm run typecheck && npm run test
```

### Migration dry-run (sqlite)

```bash
cd backend
DB_CONNECTION=sqlite DB_DATABASE=":memory:" php artisan migrate --pretend --no-interaction
```

## Manual scenarios (API)

Use two users (IDs `1` and `2` after seeding, or create via `POST /api/v1/auth/register`). Authenticate with Sanctum (`actingAs` in tests, or token from login in HTTP client).

1. **Create direct conversation** — `POST /api/v1/conversations` with JSON `{ "type": "direct", "participant_ids": [<other_user_id>] }`. Expect `201` and `data.id`.
2. **List conversations** — `GET /api/v1/conversations` as either participant. Expect the new row.
3. **Send message** — `POST /api/v1/conversations/{id}/messages` with `{ "body": "مرحباً" }`. Expect `201`.
4. **List messages** — `GET /api/v1/conversations/{id}/messages` as the other user. Expect non-empty `data`.
5. **Mark read** — `PUT /api/v1/conversations/{id}/read` as participant. Expect `200`.
6. **Negative** — As a third user, `GET /api/v1/conversations/{id}/messages`. Expect `404` (non-participant).

## Manual scenarios (UI)

1. Log in as user A. Open `/messages` (Arabic shell). Start a direct chat using user B’s numeric ID (from admin users list or DB).
2. Open the conversation card → thread page. Send a short message; refresh and confirm ordering.
3. Log in as user B in another session/browser profile; confirm the thread shows A’s message.

## Environment

- PHPUnit sets `BROADCAST_CONNECTION=log` (see `backend/phpunit.xml`).
- For real-time clients in staging, configure `BROADCAST_CONNECTION` (e.g. `pusher` / `reverb`) and frontend Echo using `backend/resources/js/echo.js` as a starting point.
