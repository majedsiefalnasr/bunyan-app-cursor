# Analytics — Technical Plan

## Architecture Fit

- Backend (Laravel): Routes → RBAC middleware + Policies → Controllers → Services → Repositories → Models
- Frontend (Nuxt 3): Admin dashboard page → composable API client → charts/components

## Backend Plan

### 1) Migrations

- Create `analytics_events` (raw event log)
- Create `analytics_metric_rollups` (bucketed metrics)

Migration discipline:

- Forward-only migrations (never edit existing migration files)
- Always include `down()` rollback methods

### 2) Models

- `AnalyticsEvent`
- `AnalyticsMetricRollup`

### 3) Repositories

- `AnalyticsEventRepository` (write path; query by event name/date range)
- `AnalyticsMetricRollupRepository` (read path for series/overview)

### 4) Services

- `AnalyticsTrackerService`: records raw events (used by other modules)
- `AnalyticsAggregationService`: computes rollups from raw events and domain tables
- `AnalyticsReadService`: loads/caches overview and series

### 5) Jobs / Scheduling

- Scheduled job every 5 minutes:
  - recompute recent buckets (e.g., last 2 hours) to handle late events
  - refresh cached overview payload

### 6) API Layer

Routes:

- `GET /api/v1/analytics/overview`
- `GET /api/v1/analytics/metrics/{metric}`
- `GET /api/v1/analytics/trends`

Validation:

- Form Requests for date ranges, bucket enum, compare enum, and metric whitelist.

Authorization:

- `auth:sanctum` on all routes.
- Policy check that allows **Admin + Supervising Architect** only.

Caching:

- Redis cache keys include: metric keys + bucket + from/to + compare
- TTL target: ~5 minutes for overview and trends

## Frontend Plan

### 1) Routing / Page

- Add admin analytics page under the admin/dashboard section (RTL-first).

### 2) Data fetching

- Composable `useAnalyticsApi()` wrapping calls to analytics endpoints.
- Handle loading/error states and display last refreshed time.

### 3) UI

- KPI cards grid (value + delta)
- Chart containers:
  - Line chart for trends
  - Bar chart for volume counts
  - Area chart for GMV (if appropriate)

### 4) Access

- Frontend route middleware should only show page to allowed roles (UX gate); server remains authoritative.

## Testing Plan

Backend:

- Feature tests for each endpoint:
  - unauthenticated → 401
  - authenticated but wrong role → 403
  - allowed roles → 200
  - validation failures → 422 with error contract
- Unit tests for aggregation for at least one metric

Frontend:

- Component tests for KPI card rendering and filters (Vitest)
