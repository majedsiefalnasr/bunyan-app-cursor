# Security Checklist — Invoicing

- [ ] All invoice routes behind `auth:sanctum` and role middleware
- [ ] Policy denies cross-customer and cross-supplier access
- [ ] PDF and send endpoints rate-limited
- [ ] No VAT secrets or PII in client logs
