# Security Checklist — Payments

- [ ] Webhook secret from env; timing-safe comparison
- [ ] Rate limit webhook and initiate routes
- [ ] No raw PAN/CVV in logs or `gateway_response`
- [ ] Sanctum on user routes; no role bypass
- [ ] Mass assignment guarded on models
- [ ] CSRF N/A for API JSON; webhook uses secret header
