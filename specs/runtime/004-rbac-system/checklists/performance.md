# Performance Checklist — RBAC System

## Permission Resolution

- [ ] Permissions cached in Redis with key `user:{id}:permissions`
- [ ] Cache miss triggers single DB query (join roles + role_permissions + permissions)
- [ ] No N+1 queries on permission checks (entire set loaded at once)
- [ ] Cache invalidated explicitly on role change (no TTL-based expiration)
- [ ] Middleware overhead < 5ms per request (measured in feature tests)

## Gate Registration

- [ ] Gates registered once per application boot (not per request)
- [ ] Permission list for Gate registration cached at application level
- [ ] `Gate::before()` short-circuits for admin (no permission lookup needed)

## Admin Endpoints

- [ ] `GET /api/v1/admin/users` is paginated (default 15 per page)
- [ ] `GET /api/v1/admin/roles` includes user counts via single aggregate query (not N+1)
- [ ] Eager loading on user-role relationships where applicable

## Frontend

- [ ] Permissions loaded once on login/profile fetch (no separate API call)
- [ ] Navigation filtering computed once (not on every render)
- [ ] Role management table uses server-side pagination
