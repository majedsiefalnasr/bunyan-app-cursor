# Testing Guide — Notifications (STAGE_22)

## Preconditions

- Backend `.env` with working `DB_*` and queue driver (`sync` is fine for local smoke).
- Frontend `NUXT_PUBLIC_API_BASE_URL` pointing at the Laravel API root including `/api` (example: `http://127.0.0.1:8000/api`).

## Automated

```bash
cd backend && php artisan migrate --no-interaction && php artisan test --filter=NotificationFlowTest
cd ../frontend && npm run typecheck
```

## Manual — API

1. `POST http://127.0.0.1:8000/api/v1/auth/login` with JSON `{ "email": "customer@example.com", "password": "password" }` (or any seeded user) → copy `data.token`.
2. `GET /api/v1/notifications` with header `Authorization: Bearer <token>` → expect `200`, `success: true`, array payload in `data`.
3. `GET /api/v1/notifications/unread-count` with the same header → expect `{ "data": { "count": <n> } }`.
4. Pick a notification `id` from step 2; `PUT /api/v1/notifications/<id>/read` → expect `200`; repeat unread-count and confirm count decrements.
5. `PUT /api/v1/notifications/read-all` → expect `200`; unread-count should return `0` if no new notifications arrive.
6. `GET /api/v1/notification-preferences` → expect four rows (`general`, `orders`, `projects`, `approvals`).
7. `PUT /api/v1/notification-preferences` with body:
   `{ "preferences": [ { "type": "general", "email_enabled": false, "sms_enabled": true, "push_enabled": false } ] }`
   → expect `200`; verify row in `notification_preferences` for that user.

## Manual — UI

1. Sign in through the Nuxt app; confirm the bell appears in the shell header beside the language switcher.
2. Open the bell dropdown — latest notifications (up to five) show as read-only rows; links reach `/notifications` and `/notifications/settings`.
3. Visit `/notifications` — list renders; use **Mark all read** when unread items exist.
4. Visit `/notifications/settings` — toggle checkboxes, hit **Save preferences**, confirm toast success and persisted values after refresh.

## Negative checks

- Call any notification endpoint without `Authorization` → expect `401`.
- Attempt `PUT /api/v1/notifications/{uuid}/read` with another user’s UUID while authenticated as a different user → expect `404`.
