# PR — Notifications

## Summary

**Stage:** Notifications  
**Phase:** 05_COMMUNICATION_AND_MEDIA  
**Branch:** `spec/022-notifications` → `develop`  
**Tasks:** 14 / 14 completed

## What Changed

### Backend

- Added UUID `notifications` table (optional `channel` column) plus `notification_preferences` with unique `(user_id, type)`.
- Introduced `NotificationType` enum, repositories, services, Sanctum-protected controllers, and `GenericDatabaseNotification` (queued) for database delivery.
- Added `NotificationFlowTest` covering isolation, read flows, preferences validation.

### Frontend

- Added `NotificationBell` with unread badge + dropdown preview in `AppHeader` for authenticated sessions.
- New pages `/notifications` (history) and `/notifications/settings` with i18n strings in `ar.json` / `en.json`.

### Database

- `2026_04_12_121900_create_notifications_table.php`
- `2026_04_12_121910_create_notification_preferences_table.php`

## Breaking Changes

- None.

## Testing

- [x] Feature tests targeted (`php artisan test --filter=NotificationFlowTest`)
- [x] Laravel Pint on changed PHP
- [x] PHPStan (pre-commit scope)
- [x] `npm run typecheck`
- [ ] Full `composer run test` + `npm run test` (run locally with configured `.env`)
- [ ] `php artisan migrate --pretend` (requires working DB credentials)

## Checklist

- [x] Sanctum + throttle on new routes
- [x] Form Request on preference updates
- [x] Arabic-first API messages + RTL-safe UI chrome
- [x] Error contract via `BaseController`
- [x] Ownership enforced for notification UUID operations

## Related

- Stage spec: `specs/phases/05_COMMUNICATION_AND_MEDIA/STAGE_22_NOTIFICATIONS.md`
