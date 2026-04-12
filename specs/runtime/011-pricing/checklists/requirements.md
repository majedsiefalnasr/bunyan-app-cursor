# Requirements Checklist — Pricing

- [ ] RBAC: tier reads require `auth:sanctum` + `ProductPolicy::view`; tier writes `role:admin` + `ProductPolicy::update`
- [ ] Form Request validation on `PUT …/pricing` and `POST …/pricing/calculate`
- [ ] Service layer owns overlap validation, sync, and calculation; controllers thin
- [ ] Repositories own Eloquent persistence for `price_tiers` and `price_histories`
- [ ] Migrations forward-only with `down()` for `price_tiers` and `price_histories`
- [ ] `price_histories` recorded when product base `price` changes
- [ ] PHPUnit feature tests for pricing routes (success + failure paths)
- [ ] Nuxt admin pricing page + product detail tier display; RTL + Arabic strings
- [ ] Error contract (`success`, `data`, `message`, `errors`)
