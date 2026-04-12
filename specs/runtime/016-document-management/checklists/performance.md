# Performance Checklist — Document Management

- [ ] Document listings paginated; indexes on morph + category + `deleted_at`
- [ ] Eager-load `uploadedBy` where needed to avoid N+1
- [ ] Version history queries ordered with limit/pagination if lists grow large
