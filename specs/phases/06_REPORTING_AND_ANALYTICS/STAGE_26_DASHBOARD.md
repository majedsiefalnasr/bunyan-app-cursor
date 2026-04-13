# STAGE_26 — Dashboard

> **Phase:** 06_REPORTING_AND_ANALYTICS
> **Status:** DRAFT
> **Scope:** Admin and user dashboards, KPI widgets
> **Risk Level:** MEDIUM

## Stage Status

Status: DRAFT
Step: specify
Risk Level: UNKNOWN
Last Updated: 2026-04-13T20:40:00Z

Scope Defined: Role-scoped dashboard APIs, aggregation service/repository, Nuxt dashboard KPI UI

Deferred Scope: Dedicated dashboard tables, advanced charts, WebSocket updates

Architecture Governance Compliance: Specification drafted — governance audit pending

Notes: Specification complete. Clarification step pending.

## Objective

Implement role-specific dashboards with KPI widgets, recent activity, and quick actions.

## Scope

### Backend

- Dashboard service (aggregate metrics per role)
- Dashboard widget data endpoints
- Caching strategy for dashboard queries (Redis)
- Metric calculations: revenue, orders, projects, pending approvals

### Frontend

- Admin dashboard (platform overview: users, orders, revenue, projects)
- Customer dashboard (my projects, my orders, pending quotes)
- Contractor/Supplier dashboard (products, orders received, revenue)
- Engineer dashboard (assigned projects, tasks, pending approvals)
- Dashboard widget components (stat card, chart, recent list, quick actions)
- Chart integration (Chart.js or ApexCharts)

### API Endpoints

| Method | Route                             | Description                      |
| ------ | --------------------------------- | -------------------------------- |
| GET    | /api/v1/dashboard                 | Get dashboard data (role-scoped) |
| GET    | /api/v1/dashboard/metrics         | Get KPI metrics                  |
| GET    | /api/v1/dashboard/recent-activity | Recent activity summary          |

## Dependencies

- **Upstream:** All feature modules (aggregates data)
- **Downstream:** None
