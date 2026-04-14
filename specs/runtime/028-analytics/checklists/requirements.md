# Requirements Checklist — Analytics

- [x] Spec has no unresolved `[NEEDS CLARIFICATION]` items
- [ ] RBAC is enforced server-side for every analytics endpoint
- [ ] All inputs are validated via Form Requests
- [ ] Controllers are thin; all business logic lives in Services
- [ ] Services use Repositories (no direct Eloquent in Services)
- [ ] Repositories use Eloquent and eager loading to avoid N+1
- [ ] API responses follow the Bunyan success/error contract
- [ ] Caching strategy defined (Redis): keys, TTLs, invalidation/refresh flow
- [ ] Aggregations are computed via jobs/schedule (not computed ad-hoc in controllers)
- [ ] Frontend is Arabic-first and RTL-safe
- [ ] Frontend uses Nuxt UI components and Bunyan design language
- [ ] Test coverage:
  - [ ] Feature tests for endpoints (auth + RBAC + validation)
  - [ ] Unit tests for aggregation logic
