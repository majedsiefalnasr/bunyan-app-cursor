# Specification — STAGE_27 Business & Analytics Reports

> **Phase:** 06_REPORTING_AND_ANALYTICS  
> **Runtime:** `specs/runtime/027-reports`

## Overview

Deliver **admin-facing business and analytics reports** with filterable JSON payloads and export to **PDF** (DomPDF) and **spreadsheet** (XLSX via Maatwebsite Excel). This capability is **distinct** from existing **field engineer project reports** (`Report` model, `/api/v1/reports` CRUD). Analytics endpoints MUST live under a dedicated prefix to avoid route/model binding collisions.

## User Stories

### US1 — Admin views report catalog

**As** an admin, **I want** a list of available report types and metadata, **so that** I can pick the right insight view.

**Acceptance criteria**

- Authenticated admin receives a stable list of report type keys, labels (AR/EN via API messages/resources), and supported export formats.
- Non-admin receives HTTP 403 with standard error contract.

### US2 — Admin generates a filtered report

**As** an admin, **I want** to apply date range and domain filters, **so that** I can narrow aggregates to a business window.

**Acceptance criteria**

- `GET /api/v1/admin/analytics/reports/{type}` returns `{ success, data: { meta, rows, summary }, message }`.
- Supported `type` values (MVP): `sales_summary`, `orders_summary`, `project_status`, `inventory_low_stock`, `financial_summary` (financial may return placeholder aggregates until accounting module exists — documented in plan).
- Query validation via Form Request: `date_from`, `date_to`, optional `category_id`, `status`, `supplier_id` where applicable.
- Service layer performs aggregation; repository encapsulates Eloquent; controller is thin.

### US3 — Admin exports report

**As** an admin, **I want** PDF or XLSX export of the same filtered dataset, **so that** I can share offline.

**Acceptance criteria**

- `GET /api/v1/admin/analytics/reports/{type}/export?format=pdf|xlsx` streams/downloads file with sensible filename.
- Export reuses the same service query path as JSON view (no duplicated business rules).
- Throttle applied on export routes.

### US4 — Frontend reports hub (MVP)

**As** an admin, **I want** a Nuxt page to pick a report, set filters, preview tabular data, and export, **so that** I can operate without Postman.

**Acceptance criteria**

- RTL-safe layout; Arabic copy via `locales/ar.json` / `en.json`.
- Uses existing API client composable and Nuxt UI components.

## Non-Functional Requirements

- **RBAC:** All analytics routes `role:admin` (or dedicated permission aligned with existing `report.view` if present — verify in implementation).
- **Error contract:** Standard Bunyan JSON envelope.
- **Performance:** Paginate or cap row counts for large datasets; document limits in API.
- **Security:** No raw SQL from user input; validated filters only.
- **Observability:** Structured log on generation/export with `action`, `report_type`, `user_id`.

## Out of Scope (MVP)

- Scheduled email delivery of reports (queued jobs may be stubbed / documented follow-up).
- Non-admin roles for business reports (supplier views deferred).
- Charting library integration beyond simple tables (optional follow-up).

## Clarifications

### Session 2026-04-13

1. **Route collision:** Business analytics MUST NOT use `GET /api/v1/reports/{type}` because `reports/{report}` is the field report resource. **Decision:** Use prefix `/api/v1/admin/analytics/reports/...` under admin middleware.
2. **Excel stack:** Add `maatwebsite/excel` to backend for XLSX generation (DomPDF already present).
3. **Financial summary:** May return structured placeholder until full GL; spec treats as **stubbed metrics** with `meta.stub = true` when applicable.
4. **Row limits:** Default cap **5000** rows per report generation; clients receive `meta.truncated` when capped.
5. **Throttle:** Export routes use `throttle:30,1` per authenticated admin.

## Traceability

| Area         | Stage file section        |
| ------------ | ------------------------- |
| Report types | STAGE_27_REPORTS.md table |
| Exports      | Export service            |
| Dependencies | Upstream modules          |
