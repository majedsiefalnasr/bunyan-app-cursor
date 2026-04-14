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

Job guardrails:

- Jobs must be idempotent (safe to rerun for the same bucket window)
- Prevent overlap via a distributed lock (Redis lock) per job run window

Lock specifics:

- Aggregation job lock key: `analytics:jobs:aggregate:lock`
- Cache refresh lock key: `analytics:jobs:refresh:lock`
- Lock TTL: 900 seconds (and renew/extend while running)
- On lock contention: skip this run (do not queue a second run)

Lock correctness:

- Locks must be tokenized; only extend/release if the current worker still owns the lock token.

### 6) API Layer

Routes:

- `GET /api/v1/analytics/overview`
- `GET /api/v1/analytics/metrics/{metric}`
- `GET /api/v1/analytics/trends`

Validation:

- Form Requests for date ranges, bucket enum, compare enum, and metric whitelist.
- Abuse-prevention caps:
  - Max date range: 180 days
  - Trends `keys[]` max length: 10
  - Cap returned points: 400 per series (adjust based on bucket)

Authorization:

- `auth:sanctum` on all routes.
- Policy check that allows **Admin + Supervising Architect** only.
- Add request audit logging for analytics reads (who/endpoint/range).
- Audit sink: structured application logs only (no DB audit table in this stage).
- Add throttling middleware to all analytics routes (in addition to caching).

Caching:

- Redis cache keys include: metric keys + bucket + from/to + compare
- TTL target: ~5 minutes for overview and trends
- Use stampede protection (single-flight) on cache misses via Redis lock.
- Endpoints must never compute heavy aggregations on-demand; they read rollups (and may return empty data if rollups not ready).

Stampede protection specifics:

- Lock key format: `analytics:cache:lock:{cache_key_hash}`
- Lock TTL: 30 seconds
- If lock not acquired:
  - Serve stale cache immediately if available (stale-while-revalidate)
  - Otherwise retry up to 3 times with jitter (50–150ms), max total wait 500ms
  - If still missing, return an empty series/overview payload (never fall back to live aggregation)

Bucketing semantics:

- Bucketing uses UTC dates (week starts Monday; ISO-8601).

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

Charting:

- Use `echarts` + `vue-echarts`, wrapped behind local components (to keep the library swappable and to control RTL/accessibility).

### 4) Access

- Frontend route middleware should only show page to allowed roles (UX gate); server remains authoritative.

## Testing Plan

Backend:

- Feature tests for each endpoint:
  - unauthenticated → 401
  - authenticated but wrong role → 403
  - allowed roles → 200
  - validation failures → 422 with error contract
- Feature tests: rate limiting (429) returns Bunyan error contract
- Unit tests for aggregation for at least one metric
- Unit/feature tests for:
  - job idempotency (rerun does not double-count)
  - late events included within recompute window
  - cache stampede lock behavior (single-flight)

Frontend:

- Component tests for KPI card rendering and filters (Vitest)
