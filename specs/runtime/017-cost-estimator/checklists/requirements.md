# Requirements Checklist — Cost Estimator (STAGE_17)

## Specification completeness

- [x] User stories cover list, CRUD, items, calculate, approve/reject, compare, export, templates, UI
- [x] API routes match stage table plus compare, approve/reject, CSV export, admin templates
- [x] Database tables documented (`estimates`, `estimate_items`, `boq_templates`)
- [x] Non-goals explicit (PDF binary, template apply to lines)
- [x] Acceptance criteria include tests and migrations pretend

## Architecture alignment

- [x] Controllers thin; service + repository layers
- [x] Form Requests for validation
- [x] Policies for estimate access derived from project access
- [x] Standard API success/error envelope

## Localization

- [x] Arabic-first API validation messages where applicable
- [x] Frontend strings via i18n keys
