# Security Checklist — Payments

- [x] Webhook secret from env; timing-safe comparison
- [x] Rate limit webhook and initiate routes
- [x] No raw PAN/CVV in logs or `gateway_response`
- [x] Sanctum on user routes; no role bypass
- [x] Mass assignment guarded on models
- [x] CSRF N/A for API JSON; webhook uses secret header
