# Requirements Checklist — Inventory Management

- [x] RBAC: inventory routes behind `auth:sanctum` and `role:admin,contractor` with policy scoping
- [x] Form Request on `PUT …/adjust`
- [x] Service owns transactional adjust + movement insert; controller thin
- [x] Repositories own queries; eager-load product on list endpoints
- [x] Migrations forward-only with `down()`
- [x] PHPUnit feature tests for list, adjust, movements, low-stock, forbidden paths
- [x] Nuxt admin page uses `useApi()` + `role:admin` middleware (contractor uses API only or future page)
- [x] Error contract compliance
- [x] Structured logging on adjust and low-stock job
