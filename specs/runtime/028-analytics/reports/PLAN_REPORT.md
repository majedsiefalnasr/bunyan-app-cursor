# Plan Report — Analytics

> **Phase:** 06_REPORTING_AND_ANALYTICS > **Generated:** 2026-04-14T09:12:26Z

## Plan Summary

| Metric         | Value                                                                         |
| -------------- | ----------------------------------------------------------------------------- |
| New Tables     | 2 (`analytics_events`, `analytics_metric_rollups`)                            |
| New Endpoints  | 3 (`/analytics/overview`, `/analytics/metrics/{metric}`, `/analytics/trends`) |
| New Services   | 3 (tracking, aggregation, read/cache)                                         |
| New Pages      | 1 (admin analytics dashboard page)                                            |
| New Components | TBD (KPI cards + chart containers)                                            |

## Architecture Decisions

- Store raw events and compute time-bucketed rollups for fast reads
- Cache read endpoints in Redis with ~5 minute freshness target
- Enforce server-side RBAC (Admin + Supervising Architect) via Sanctum auth + policies
- Normalize decimal values to JSON numbers in API Resources

## Guardian Verdicts

| Guardian              | Verdict | Notes                                                |
| --------------------- | ------: | ---------------------------------------------------- |
| Architecture Guardian |    PASS | Layering, RBAC, contracts and data-model consistency |
| API Designer          |    PASS | Routes disambiguated; Bunyan error contract included |

## Risk Assessment

| Risk Level | Count | Details                                                                                     |
| ---------- | ----- | ------------------------------------------------------------------------------------------- |
| HIGH       | 0     | —                                                                                           |
| MEDIUM     | 3     | Data growth for raw events; correctness of aggregations; caching freshness/stampede control |
| LOW        | 2     | Frontend chart library integration; RTL layout tuning                                       |
