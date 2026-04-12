# Notifications — Requirements Checklist

- [ ] Sanctum authentication on all notification and preference routes.
- [ ] Form Requests for preference updates; validation messages localized (AR/EN).
- [ ] Service layer owns read/mark-all logic and preference merge; repositories encapsulate Eloquent queries.
- [ ] Policies or explicit ownership checks so UUID `notifications.id` cannot be read across users.
- [ ] API success/error contract via `BaseController` / `ApiResponse`.
- [ ] Forward-only migrations with `down()` for `notifications` and `notification_preferences`.
- [ ] PHPUnit feature tests: list, mark read, mark all, unread count, preferences get/put, cross-user isolation.
- [ ] Nuxt UI + i18n for bell, dropdown, history, settings; RTL-safe layout.

## Security / Performance / A11y

- [ ] Throttle `GET`/`PUT` notification routes.
- [ ] Pagination defaults to prevent unbounded reads.
- [ ] Icon-only controls expose `aria-label` via i18n.
