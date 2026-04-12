# Security Checklist — Pricing

- [ ] No tier mutation without `role:admin` and `ProductPolicy::update`
- [ ] Calculate endpoint cannot access inactive products for non-admin (respect product visibility)
- [ ] No mass assignment of `changed_by`; set from authenticated user id server-side
- [ ] Rate-limit sensitive `POST /pricing/calculate` if abuse risk (reuse global throttle group where applicable)
