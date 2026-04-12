# Requirements Checklist — Activity Log (STAGE_25)

- [x] `activity_logs` migration matches schema and includes rollback.
- [x] Admin list endpoint is protected by Sanctum + `role:admin`.
- [x] Subject activity endpoint resolves allowlisted entities and enforces `view` policy.
- [x] Service/repository layering respected (no business logic in controller).
- [x] Form Request validation for all query parameters.
- [x] API responses follow Bunyan success/error contract.
- [x] Automatic logging does not persist secrets or oversized payloads.
- [x] Nuxt admin page is admin-only in UI middleware and uses `useApi`.
- [x] RTL/i18n coverage for new UI strings.
- [x] PHPUnit feature tests cover RBAC and basic list behavior.
