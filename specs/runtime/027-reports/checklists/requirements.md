# Requirements Checklist — STAGE_27 Reports

- [x] Scope distinguishes field `Report` CRUD vs admin analytics reports
- [x] API prefix avoids collision with `/api/v1/reports/{id}`
- [x] RBAC: admin-only for analytics MVP
- [x] Form Request validation for filters and export format
- [x] Service + repository layering for aggregates
- [x] Standard success/error JSON contract
- [x] Arabic-first / RTL frontend considerations
- [x] PDF via DomPDF; XLSX via Maatwebsite Excel
- [x] Logging context for generate/export actions
- [x] Feature tests for happy path and 403 for non-admin
