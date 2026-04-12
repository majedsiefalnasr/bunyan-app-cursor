# Notifications — Security / Performance / Accessibility

- [ ] Strict ownership checks on every `notifications` row (user id match).
- [ ] Preference upserts scoped by `user_id` from auth context only.
- [ ] Throttle read/write notification endpoints (60/min).
- [ ] Paginate `GET /notifications` with sane defaults and caps.
- [ ] `aria-label` on bell and mark-read controls via i18n.
- [ ] No secrets or tokens logged when dispatching notifications.
