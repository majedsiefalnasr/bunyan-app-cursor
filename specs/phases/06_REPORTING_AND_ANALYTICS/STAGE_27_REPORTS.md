# STAGE_27 — Reports

> **Phase:** 06_REPORTING_AND_ANALYTICS
> **Status:** DRAFT
> **Scope:** Business reports, export (PDF, Excel)
> **Risk Level:** MEDIUM

## Stage Status

Status: DRAFT
Step: tasks
Risk Level: MEDIUM
Last Updated: 2026-04-13T22:25:00Z

Tasks Generated: Total: 12 atomic tasks

Architecture Governance Compliance: Task set compliant — drift analysis required

Notes: Ready for analyze gate.

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
