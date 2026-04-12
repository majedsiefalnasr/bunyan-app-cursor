# Suppliers — Requirements Checklist

- [x] RBAC: `role` middleware on mutating routes; admin-only verify and admin index.
- [x] Form Requests for store, update, verify; product requests extended for `supplier_id`.
- [x] Service layer owns verification rules and visibility; repository holds queries.
- [x] Policies: `SupplierProfilePolicy` for create, update, verify.
- [x] API error contract via existing `BaseController` / `ApiResponse`.
- [x] Migrations forward-only with `down()` for `supplier_profiles` and `products.supplier_id`.
- [x] Feature tests for catalog, RBAC, verify, products, admin list.
- [x] Arabic-first UI strings via i18n keys; RTL layout via default shell.

## Security / Performance / A11y (minimal)

- [x] Throttle on public supplier GET routes.
- [x] Eager-load `user` on supplier listings to reduce N+1.
- [x] Nuxt UI components for forms and tables where applicable.
