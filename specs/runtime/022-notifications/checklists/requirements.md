# Notifications — Requirements Checklist

- [x] Sanctum authentication on all notification and preference routes.
- [x] Form Requests for preference updates; validation messages localized (AR/EN).
- [x] Service layer owns read/mark-all logic and preference merge; repositories encapsulate Eloquent queries.
- [x] Policies or explicit ownership checks so UUID `notifications.id` cannot be read across users.
- [x] API success/error contract via `BaseController` / `ApiResponse`.
- [x] Forward-only migrations with `down()` for `notifications` and `notification_preferences`.
- [x] PHPUnit feature tests: list, mark read, mark all, unread count, preferences get/put, cross-user isolation.
- [x] Nuxt UI + i18n for bell, dropdown, history, settings; RTL-safe layout.

## Security / Performance / A11y

- [x] Throttle `GET`/`PUT` notification routes.
- [x] Pagination defaults to prevent unbounded reads.
- [x] Icon-only controls expose `aria-label` via i18n.
- [x] No secrets or tokens logged when dispatching notifications.
