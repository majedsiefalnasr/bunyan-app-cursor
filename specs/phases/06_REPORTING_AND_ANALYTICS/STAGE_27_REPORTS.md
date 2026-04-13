# STAGE_27 — Reports

> **Phase:** 06_REPORTING_AND_ANALYTICS
> **Status:** DRAFT
> **Scope:** Business reports, export (PDF, Excel)
> **Risk Level:** MEDIUM

## Stage Status

Status: DRAFT
Step: clarify
Risk Level: MEDIUM
Last Updated: 2026-04-13T22:10:00Z

Scope Defined: Admin analytics reports (JSON + PDF/XLSX), dedicated `/admin/analytics/reports` API, Nuxt admin hub MVP, row cap + export throttle

Deferred Scope: Scheduled delivery, supplier views, advanced charts

Architecture Governance Compliance: Clarifications resolved — planning authorized

Notes: All specification ambiguities resolved. Ready for technical planning.

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
