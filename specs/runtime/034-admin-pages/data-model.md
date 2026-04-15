# Data Model — Admin Pages

> **Generated (UTC):** 2026-04-14T14:24:38Z

This stage is **frontend-only** and does not introduce new database tables.

## Entities surfaced in Admin UI

- **User**
  - Attributes used: `id`, `name`, `email`, `role`, `role_label`, `phone`, `active`, `email_verified_at`, `created_at`
- **Role / Permission**
  - Display: role list; permission matrix per role (exact permission keys depend on backend contract)
- **Category**
  - Tree/hierarchy: parent/children; reorder operations
- **SupplierProfile**
  - Attributes used: `id`, `company_name_ar`, `verification_status`, related `user` (optional)
- **ActivityLog**
  - Attributes used: `id`, `action`, `subject_type`, `subject_id`, `created_at`, `actor` (optional)
- **Analytics / Reports**
  - Report types metadata + report response: `{ meta, summary, rows }`
