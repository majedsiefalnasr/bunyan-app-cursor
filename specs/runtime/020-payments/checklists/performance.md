# Performance Checklist — Payments

- [x] History query uses indexes on `payments.user_id` / `paid_by` and `created_at`
- [x] Eager-load `attempts` only where needed
- [x] Pagination default 15
