# Security Checklist — Invoicing

- [x] All invoice routes behind `auth:sanctum` and role middleware
- [x] Policy denies cross-customer and cross-supplier access
- [x] PDF and send endpoints rate-limited
- [x] No VAT secrets or PII in client logs
