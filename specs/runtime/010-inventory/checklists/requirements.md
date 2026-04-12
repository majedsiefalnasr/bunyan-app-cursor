# Requirements Checklist — Inventory Management

- [ ] RBAC: inventory routes behind `auth:sanctum` and `role:admin,contractor` with policy scoping
- [ ] Form Request on `PUT …/adjust`
- [ ] Service owns transactional adjust + movement insert; controller thin
- [ ] Repositories own queries; eager-load product on list endpoints
- [ ] Migrations forward-only with `down()`
- [ ] PHPUnit feature tests for list, adjust, movements, low-stock, forbidden paths
- [ ] Nuxt admin page uses `useApi()` + `role:admin` middleware (contractor uses API only or future page)
- [ ] Error contract compliance
- [ ] Structured logging on adjust and low-stock job
