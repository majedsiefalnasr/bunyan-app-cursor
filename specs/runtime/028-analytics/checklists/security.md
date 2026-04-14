# Security Checklist — Analytics

- [ ] All analytics routes are protected by `auth:sanctum`
- [ ] Role checks enforced server-side (Admin + Supervising Architect only)
- [ ] Authorization uses Policies (no controller-only checks)
- [ ] Rate limit analytics endpoints appropriately
- [ ] Validate all query params (date range, bucket, metric enum)
- [ ] Do not expose PII in metrics responses (only aggregates)
