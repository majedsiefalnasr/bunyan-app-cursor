# Requirements Checklist — Activity Log (STAGE_25)

- [ ] `activity_logs` migration matches schema and includes rollback.
- [ ] Admin list endpoint is protected by Sanctum + `role:admin`.
- [ ] Subject activity endpoint resolves allowlisted entities and enforces `view` policy.
- [ ] Service/repository layering respected (no business logic in controller).
- [ ] Form Request validation for all query parameters.
- [ ] API responses follow Bunyan success/error contract.
- [ ] Automatic logging does not persist secrets or oversized payloads.
- [ ] Nuxt admin page is admin-only in UI middleware and uses `useApi`.
- [ ] RTL/i18n coverage for new UI strings.
- [ ] PHPUnit feature tests cover RBAC and basic list behavior.
