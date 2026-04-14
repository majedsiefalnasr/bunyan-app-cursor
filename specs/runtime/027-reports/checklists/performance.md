# Performance Checklist — STAGE_27

- [ ] Aggregates use indexed columns (`created_at`, `status`, FKs)
- [ ] Chunk or cap large exports; document limits in API docs
- [ ] Eager loads where relationships required
- [ ] Optional cache layer documented for hot reports (follow-up)
