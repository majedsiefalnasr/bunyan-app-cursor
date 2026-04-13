# STAGE_27 — Reports

> **Phase:** 06_REPORTING_AND_ANALYTICS
> **Status:** PRODUCTION READY
> **Scope:** Business reports, export (PDF, Excel)
> **Risk Level:** MEDIUM

## Stage Status

Status: PRODUCTION READY
Step: stage_production_ready
Risk Level: MEDIUM
Closure Date: 2026-04-13

Scope Closed: Admin analytics API (six report types), PDF/XLSX export, repository/service layering, admin Nuxt reports hub, feature tests, i18n — 12/12 tasks

Deferred Scope: Scheduled delivery, supplier-facing analytics, advanced charting

Architecture Governance Compliance:

- ADR alignment verified (no conflicting ADR changes)
- RBAC enforcement confirmed (`role:admin` on analytics routes)
- Service layer architecture maintained
- Error contract compliance verified on JSON endpoints

Notes: Stage is production ready. Further changes should follow a new stage or amendment protocol.

## Objective

Implement business reporting system with configurable reports and export capabilities.

## Scope

### Backend

- Report service (generate, cache, export)
- Report types: Sales, Orders, Inventory, Projects, Financial
- Report filter builder (date range, category, status, supplier)
- Export service (PDF via DomPDF, Excel via Maatwebsite/Excel)
- Scheduled report generation (weekly/monthly via worker)

### Frontend

- Reports listing page
- Report viewer with filters
- Report charts and data tables
- Export buttons (PDF, Excel)
- Date range picker
- Report print layout

### Report Types

| Report               | Audience        | Key Metrics                                 |
| -------------------- | --------------- | ------------------------------------------- |
| Sales Summary        | Admin           | Total revenue, order count, avg order value |
| Inventory Report     | Admin, Supplier | Stock levels, low stock, movement           |
| Project Status       | Admin, Engineer | Active projects, completion %, overdue      |
| Supplier Performance | Admin           | Delivery time, ratings, order volume        |
| Financial Summary    | Admin           | Revenue, expenses, profit, VAT              |

### API Endpoints

| Method | Route                         | Description     |
| ------ | ----------------------------- | --------------- |
| GET    | /api/v1/reports/{type}        | Generate report |
| GET    | /api/v1/reports/{type}/export | Export report   |

## Dependencies

- **Upstream:** All feature modules
- **Downstream:** None
