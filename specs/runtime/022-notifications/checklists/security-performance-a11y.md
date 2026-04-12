# Notifications — Security / Performance / Accessibility

- [x] Strict ownership checks on every `notifications` row (user id match).
- [x] Preference upserts scoped by `user_id` from auth context only.
- [x] Throttle read/write notification endpoints (60/min).
- [x] Paginate `GET /notifications` with sane defaults and caps.
- [x] `aria-label` on bell and mark-read controls via i18n.
- [x] No secrets or tokens logged when dispatching notifications.
