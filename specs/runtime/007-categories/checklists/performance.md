# Performance Checklist — Categories

- [ ] Tree endpoint uses bounded depth and eager structure (single query + in-memory nest or controlled eager loads)
- [ ] Indexes on `parent_id`, `slug`, `sort_order`
- [ ] Avoid N+1 in category detail when including ancestors (breadcrumb)
