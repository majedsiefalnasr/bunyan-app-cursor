# Requirements Checklist — Products

- [ ] RBAC: public catalog reads require authentication; admin routes under `admin` + `role:admin`
- [ ] Policies invoked for create/update/delete
- [ ] Form Request validation on all mutating endpoints (products, variants, media)
- [ ] Service layer holds SKU and persistence rules; controller thin
- [ ] Repository composes queries; eager-load relations on detail
- [ ] Migrations forward-only with rollback
- [ ] PHPUnit feature tests for new endpoints and filters
- [ ] Nuxt pages wired to `useApi()` with auth middleware
- [ ] RTL + i18n keys for new UI strings
- [ ] Error contract (`success`, `data`, `message`, `errors`)
