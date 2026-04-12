# Requirements Checklist — Products

- [x] RBAC: public catalog reads require authentication; admin routes under `admin` + `role:admin`
- [x] Policies invoked for create/update/delete
- [x] Form Request validation on all mutating endpoints (products, variants, media)
- [x] Service layer holds SKU and persistence rules; controller thin
- [x] Repository composes queries; eager-load relations on detail
- [x] Migrations forward-only with rollback
- [x] PHPUnit feature tests for new endpoints and filters
- [x] Nuxt pages wired to `useApi()` with auth middleware
- [x] RTL + i18n keys for new UI strings
- [x] Error contract (`success`, `data`, `message`, `errors`)
