# Requirements Checklist — Dashboard

## Specification

- [x] Scope covers backend endpoints, service/repository layering, and frontend dashboard page.
- [x] RBAC matrix documented per endpoint.
- [x] Out of scope explicitly lists charts and new DB tables.

## Backend

- [ ] `auth:sanctum` + role middleware on all dashboard routes.
- [ ] Form Request validates `per_page` on recent-activity endpoint.
- [ ] Controller delegates to `DashboardService` only.
- [ ] Repository contains Eloquent queries only.
- [ ] API envelope matches project error contract.
- [ ] Feature tests: 401 guest; 200 each role; activity scoping.

## Frontend

- [ ] Dashboard page uses `useApi` / composable (no raw `$fetch` outside composable).
- [ ] Nuxt UI cards with shadow-as-border pattern.
- [ ] i18n keys for new strings (ar + en).

## Security & Privacy

- [ ] No cross-user project/order leakage in non-admin responses.
- [ ] Admin global activity does not expose sensitive properties beyond existing ActivityLog serialization.
