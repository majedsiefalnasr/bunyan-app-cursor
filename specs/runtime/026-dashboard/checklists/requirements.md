# Requirements Checklist — Dashboard

## Specification

- [x] Scope covers backend endpoints, service/repository layering, and frontend dashboard page.
- [x] RBAC matrix documented per endpoint.
- [x] Out of scope explicitly lists charts and new DB tables.

## Backend

- [x] `auth:sanctum` + role middleware on all dashboard routes.
- [x] Form Request validates `per_page` on recent-activity endpoint.
- [x] Controller delegates to `DashboardService` only.
- [x] Repository contains Eloquent queries only.
- [x] API envelope matches project error contract.
- [x] Feature tests: 401 guest; 200 each role; activity scoping.

## Frontend

- [x] Dashboard page uses `useApi` / composable (no raw `$fetch` outside composable).
- [x] Nuxt UI cards with shadow-as-border pattern.
- [x] i18n keys for new strings (ar + en).

## Security & Privacy

- [x] No cross-user project/order leakage in non-admin responses.
- [x] Admin global activity does not expose sensitive properties beyond existing ActivityLog serialization.
