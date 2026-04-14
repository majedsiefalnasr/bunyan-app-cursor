# Analytics — Spec

## Summary

Build an analytics module that exposes platform usage metrics and business KPIs with trend and period-over-period comparison views, optimized with caching.

## Goals

- Provide an **overview** dashboard of key metrics (platform, commerce, projects, suppliers).
- Provide **metric drill-down** time series per metric with configurable granularity (daily/weekly/monthly).
- Provide **trends** endpoints and a frontend page with interactive charts.
- Ensure the analytics data path is **fast** (Redis caching) and safe (RBAC enforced server-side).

## Non-Goals

- End-user (customer/contractor) personalization of analytics dashboards (admin-only by default).
- Third-party analytics vendor integration (e.g., Segment/GA) unless explicitly added later.

## Users & RBAC

- **Admin**: full access to analytics dashboard and endpoints.
- **Supervising Architect**: access to analytics dashboard and endpoints.
  - Notes: No customer/contractor analytics access in this stage.

## Backend Scope

### Data sources

- Platform activity: logins/sessions, registrations
- Commerce: orders and payments
- Projects: projects and phases/tasks lifecycle
- Suppliers: supplier onboarding and responsiveness

### Services

- Analytics event tracking service (internal events)
- KPI aggregation service (daily/weekly/monthly rollups)
- Cache layer (Redis) with clear TTL strategy per metric

### Storage

- Aggregated metrics tables (time-bucketed)
- Raw analytics events table (append-only) to support re-aggregation and new metrics.

### API Endpoints (v1)

- `GET /api/v1/analytics/overview`
- `GET /api/v1/analytics/{metric}`
- `GET /api/v1/analytics/trends`

Common query params:

- `from`, `to` (ISO date)
- `bucket` = `day|week|month` (default `day`)
- `compare` = `previous_period|previous_year|none` (default `none`)

Response contract must follow Bunyan error/success format.

## Frontend Scope

- Analytics page (RTL-ready) with:
  - KPI cards (current period + delta vs comparison)
  - Trend charts (line/bar/area depending on metric)
  - Period-over-period comparison toggle
  - “Real-time” snapshot (near-real-time from cache; target freshness: ~5 minutes)

## Key Metrics (initial)

- Platform: active users, new registrations, session duration
- Commerce: GMV, order volume, conversion rate, avg order value
- Projects: new projects, completion rate, avg duration
- Suppliers: new suppliers, verification rate, avg response time

## Performance

- Avoid N+1 queries (eager load where applicable).
- Compute heavy aggregations via scheduled jobs; endpoints should read pre-aggregated data.
- Cache read endpoints (Redis) with stampede protection pattern if needed.

## Observability

- Structured logs for analytics refresh/aggregation jobs.
- Include correlation IDs for API requests (where available in existing stack).

## Acceptance Criteria

- Admin can load Analytics dashboard without errors.
- Overview returns all initial metrics with current period value and optional comparison delta.
- Metric endpoint returns time series for requested bucket and date range.
- Trends endpoint returns multiple series suitable for charts.
- Validation errors return standard error contract.
- Tests cover:
  - RBAC (admin allowed; others forbidden unless clarified otherwise)
  - Request validation
  - Aggregation correctness for at least one metric category

## Clarifications

### Session 2026-04-14

- **Access model**: Admin + Supervising Architect.
- **Data retention**: Store raw analytics events + aggregated rollups.
- **Freshness target**: Near-real-time is acceptable at ~5 minutes.
