# Requirements Checklist — Pricing

- [x] RBAC: tier reads require `auth:sanctum` + `ProductPolicy::view`; tier writes `role:admin` + `ProductPolicy::update`
- [x] Form Request validation on `PUT …/pricing` and `POST …/pricing/calculate`
- [x] Service layer owns overlap validation, sync, and calculation; controllers thin
- [x] Repositories own Eloquent persistence for `price_tiers` and `price_histories`
- [x] Migrations forward-only with `down()` for `price_tiers` and `price_histories`
- [x] `price_histories` recorded when product base `price` changes
- [x] PHPUnit feature tests for pricing routes (success + failure paths)
- [x] Nuxt admin pricing page + product detail tier display; RTL + Arabic strings
- [x] Error contract (`success`, `data`, `message`, `errors`)
