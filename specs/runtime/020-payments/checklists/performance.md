# Performance Checklist — Payments

- [ ] History query uses indexes on `payments.user_id` / `paid_by` and `created_at`
- [ ] Eager-load `attempts` only where needed
- [ ] Pagination default 15
