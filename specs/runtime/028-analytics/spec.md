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

Event emission sources (authoritative):

- `session.*`: emitted by backend auth/session middleware when a session starts/ends
- `user.registered`: emitted on successful user registration
- `order.placed`: emitted on successful order placement (after DB commit)
- `project.created` / `project.completed`: emitted on project lifecycle transitions
- `supplier.*`: emitted on supplier onboarding/verification and messaging events

### Event taxonomy (raw events)

Raw events MUST use an allowlisted set of event names (examples):

- `session.started`
- `session.ended`
- `user.registered`
- `order.placed`
- `project.created`
- `project.completed`
- `supplier.registered`
- `supplier.verified`
- `supplier.message.sent`
- `supplier.message.responded`

Raw `metadata` must be dimension-only (no PII) and constrained (see “Security”).

### Services

- Analytics event tracking service (internal events)
- KPI aggregation service (daily/weekly/monthly rollups)
- Cache layer (Redis) with clear TTL strategy per metric

### Storage

- Aggregated metrics tables (time-bucketed)
- Raw analytics events table (append-only) to support re-aggregation and new metrics.
  - Retention: raw events 90 days; rollups 2 years

### API Endpoints (v1)

- `GET /api/v1/analytics/overview`
- `GET /api/v1/analytics/metrics/{metric}`
- `GET /api/v1/analytics/trends`

Common query params:

- `from`, `to` (ISO date)
- `bucket` = `day|week|month` (default `day`)
- `compare` = `previous_period|previous_year|none` (default `none`) — overview only

Response contract must follow Bunyan error/success format.
Bucketing/timezone: UTC dates (week starts Monday; ISO-8601).
Range semantics: `from` inclusive at 00:00:00Z; `to` inclusive through 23:59:59Z.

Internal normalization:

- For bucketing/queries, treat the request as the half-open range \([from@00:00:00Z, (to+1 day)@00:00:00Z)\).

Migration discipline:

- Forward-only migrations (never edit existing migration files)
- Always include `down()` rollback methods

Security:

- Analytics endpoints MUST be rate limited (throttle) in addition to caching.
- Raw events MUST NOT store PII in `metadata` (enforce via allowlist + size caps).
- Analytics access must be auditable (log who queried what/when).
  - Sink decision: structured application logs only (no DB audit table in this stage).

Raw event metadata governance (authoritative):

- Allowed keys: `order_id`, `project_id`, `supplier_id`
- Max keys: 10
- Max value length: 64 chars (after string conversion)
- Max JSON bytes: 1024

Linkage keys policy:

- `session_id`, `thread_id`, `request_id` MUST be stored in the dedicated columns on `analytics_events` (not inside `metadata`).

## Frontend Scope

- Analytics page (RTL-ready) with:
  - KPI cards (current period + delta vs comparison)
  - Trend charts (line/bar/area depending on metric)
  - Period-over-period comparison toggle
  - “Real-time” snapshot (near-real-time from cache; target freshness: ~5 minutes)

Frontend dependency decision:

- Charting: `echarts` + `vue-echarts` (wrapped behind local components)

## Key Metrics (initial)

- Platform: active users, new registrations, session duration
- Commerce: GMV, order volume, conversion rate, avg order value
- Projects: new projects, completion rate, avg duration
- Suppliers: new suppliers, verification rate, avg response time

### Metric definitions (initial)

- **Platform active users**: distinct `user_id` with `session.started` in range.
- **New registrations**: count of `user.registered` in range.
- **Commerce GMV**: sum of completed order totals in range (money metrics rounded to 2 decimals in responses).
- **Order volume**: count of `order.placed` in range.
- **Conversion rate**: `order.placed / session.started` over range.
- **Avg order value**: `GMV / order volume` over range.
- **Projects new projects**: count of `project.created` in range.
- **Projects completion rate**: `project.completed / project.created` over range.
- **Projects avg duration**: average days from `project.created` to `project.completed` (only completed projects in range).
- **Suppliers new suppliers**: count of `supplier.registered` in range.
- **Suppliers verification rate**: `supplier.verified / supplier.registered` over range.
- **Session duration**: average seconds between `session.started` and `session.ended` (joined by `analytics_events.session_id`).
- **Suppliers avg response time**: average seconds between `supplier.message.sent` and `supplier.message.responded` (joined by `analytics_events.thread_id` or `analytics_events.request_id`).
  - Join rule: prefer `request_id` (if present); otherwise use `thread_id`.
  - Events MUST include exactly one of `request_id` or `thread_id` for this metric.

Delta edge cases:

- If baseline value is 0 or missing, `delta.pct` is `null`.
- If baseline value is missing, `delta.value` is `null`.
- For ratio metrics, if the denominator is 0, the metric value is `null`.

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
  - RBAC matrix (Admin + Supervising Architect allowed; all other roles forbidden)
  - Request validation
  - Aggregation correctness for at least one metric category

## Clarifications

### Session 2026-04-14

- **Access model**: Admin + Supervising Architect.
- **Data retention**: Store raw analytics events + aggregated rollups.
- **Freshness target**: Near-real-time is acceptable at ~5 minutes.
