# Notifications — Runtime Specification

**Stage:** Notifications (STAGE_22)  
**Phase:** 05_COMMUNICATION_AND_MEDIA

## Summary

Deliver in-app notifications stored in MySQL (`notifications` table compatible with Laravel’s database channel), user-scoped REST APIs for listing, read state, unread counts, and per-type channel preferences (`notification_preferences`). Queue outbound email/SMS/push through Laravel queues where applicable; Nuxt shell surfaces a notification bell with a dropdown, a full history page, and a preferences page. All routes require Sanctum authentication; authorization ensures users only access their own rows.

## User Stories

1. **US1 — In-app feed:** As a signed-in user, I can list my notifications with pagination and see unread state.
2. **US2 — Read state:** As a signed-in user, I can mark one notification or all notifications as read.
3. **US3 — Unread badge:** As a signed-in user, I can fetch my unread count for the shell badge.
4. **US4 — Preferences:** As a signed-in user, I can read and update per-notification-type channel toggles (email, SMS, push) for supported types.
5. **US5 — Shell UX:** As a signed-in user, I see a header bell with a dropdown preview and links to history and settings.

## Acceptance Criteria

- `GET /api/v1/notifications` requires Sanctum; returns paginated notifications for the authenticated user only; supports `per_page` (default 20, max 100).
- `PUT /api/v1/notifications/{id}/read` marks a single row read when it belongs to the current user; 404 otherwise.
- `PUT /api/v1/notifications/read-all` marks all unread for the current user.
- `GET /api/v1/notifications/unread-count` returns `{ "count": <int> }` inside the standard success envelope.
- `GET /api/v1/notification-preferences` returns merged defaults + persisted rows for known notification types.
- `PUT /api/v1/notification-preferences` validates `preferences` array items: `type`, `email_enabled`, `sms_enabled`, `push_enabled` booleans; upserts rows per user+type.
- Database: `notifications` includes optional `channel` column (nullable) for future multi-channel metadata; `notification_preferences` enforces unique `(user_id, type)`.
- Notifications dispatched via Laravel `Notification` classes use `ShouldQueue` where non-database channels are added later; database channel writes to `notifications`.
- Frontend: authenticated header shows `NotificationBell`; `/notifications` lists history; `/notifications/settings` edits preferences; strings via i18n (`ar`/`en`).

## Non-Functional

- RBAC: all endpoints under `auth:sanctum` (any active role).
- Throttle list/read endpoints at 60/min per user IP.
- Error contract: existing `BaseController` / `ApiResponse` envelope.
- Arabic-first copy for API `message` fields where applicable.

## Out of Scope (Deferred)

- Mobile push provider integration (FCM/APNs) beyond preference flags.
- Production SMS provider wiring and template management.
- Rich admin broadcast console.
