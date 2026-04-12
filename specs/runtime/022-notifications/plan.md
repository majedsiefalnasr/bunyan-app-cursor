# Notifications — Technical Plan

## Architecture

- **Storage:** Laravel `notifications` table (UUID PK) plus optional `channel` string; `notification_preferences` with unique `(user_id, type)`.
- **Models:** `App\Models\PlatformDatabaseNotification` extends `Illuminate\Notifications\DatabaseNotification`; `NotificationPreference` for preferences.
- **User:** Override `notifications()` morphMany to the platform notification model; add `notificationPreferences()` hasMany.
- **Dispatch:** `App\Notifications\GenericDatabaseNotification` (implements `ShouldQueue`) writes database payloads with bilingual title/body keys.
- **Layers:** `NotificationRepository`, `NotificationPreferenceRepository`, `NotificationService`, `NotificationPreferenceService`, thin `NotificationController` + `NotificationPreferenceController`, Form Requests for preference updates, API Resources for JSON shaping.

## Routes (`routes/api.php`)

Inside `auth:sanctum` + `throttle:60,1`:

| Method | Path                               | Controller action                         |
| ------ | ---------------------------------- | ----------------------------------------- |
| GET    | /api/v1/notifications              | `NotificationController@index`            |
| PUT    | /api/v1/notifications/{id}/read    | `NotificationController@markRead`         |
| PUT    | /api/v1/notifications/read-all     | `NotificationController@markAllRead`      |
| GET    | /api/v1/notifications/unread-count | `NotificationController@unreadCount`      |
| GET    | /api/v1/notification-preferences   | `NotificationPreferenceController@show`   |
| PUT    | /api/v1/notification-preferences   | `NotificationPreferenceController@update` |

## Frontend

- `components/notifications/NotificationBell.vue` — `UPopover` + unread badge, preview list, links.
- Pages `pages/notifications/index.vue` (history) and `pages/notifications/settings.vue` (preferences form).
- `AppHeader.vue` includes bell when `auth.isAuthenticated`.
- i18n keys under `notifications.*` and `shell.notifications.*`.

## Validation & Testing

- `UpdateNotificationPreferencesRequest` validates `preferences.*.type` against allowed enum values and boolean flags.
- Feature tests in `tests/Feature/Api/V1/NotificationControllerTest.php` and `NotificationPreferenceControllerTest.php` (or combined file).
- `php artisan migrate --pretend` in CI validation.

## Guardian Verdicts (Plan Gate)

- **architecture_checker:** PASS — adheres to controllers → services → repositories.
- **api_designer:** PASS — Sanctum + standard envelope + plural REST paths.
