# STAGE_26 — Dashboard

> **Phase:** 06_REPORTING_AND_ANALYTICS
> **Status:** DRAFT
> **Scope:** Admin and user dashboards, KPI widgets
> **Risk Level:** MEDIUM

## Stage Status

Status: DRAFT
Step: tasks
Risk Level: LOW
Last Updated: 2026-04-13T21:00:00Z

Scope Planned: DashboardRepository, DashboardService, three GET endpoints, Nuxt composable + page, feature tests, config TTL

Tasks Generated: Total: 14 atomic tasks

Architecture Governance Compliance: Task set compliant — drift analysis required

Notes: Ready for analyze step.

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
