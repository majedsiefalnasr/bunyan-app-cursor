# Tasks Report — Analytics

> **Phase:** 06_REPORTING_AND_ANALYTICS > **Generated:** 2026-04-14T09:13:54Z

## Task Summary

| Metric         | Value |
| -------------- | ----- |
| Total Tasks    | 22    |
| Parallelizable | 10    |
| Sequential     | 12    |
| HIGH Risk      | 6     |
| MEDIUM Risk    | 10    |
| LOW Risk       | 6     |

## Risk-Ranked Task View

### 🔴 HIGH Risk Tasks

| ID   | Description                                  | Risk Factor                             |
| ---- | -------------------------------------------- | --------------------------------------- |
| T001 | Migrations for raw + rollups tables          | Schema risk + forward-only constraint   |
| T008 | Routes + server-side auth/policy enforcement | RBAC hard rule                          |
| T012 | Redis caching strategy                       | Correctness + freshness + stampede risk |
| T013 | Aggregation job + schedule                   | Data correctness + performance          |
| T016 | RBAC feature tests                           | Prevents role bypass regressions        |
| T015 | Decimal → JSON number normalization          | Contract compliance                     |

### 🟡 MEDIUM Risk Tasks

| ID   | Description                                 | Risk Factor               |
| ---- | ------------------------------------------- | ------------------------- |
| T004 | Services layering (no Eloquent in services) | Architecture drift risk   |
| T006 | Form Requests for all endpoints             | Validation completeness   |
| T009 | Overview endpoint shape + comparison deltas | API correctness           |
| T010 | Metric series endpoint                      | Time-bucket correctness   |
| T011 | Trends batch endpoint                       | Payload/shape correctness |
| T014 | Retention cleanup job                       | Data growth control       |
| T017 | Validation error contract tests             | Contract compliance       |
| T018 | Aggregation unit tests                      | Logic correctness         |
| T019 | Admin analytics page                        | RTL + UX complexity       |
| T021 | KPI cards + charts UI                       | UI library integration    |

### 🟢 LOW Risk Tasks

| ID   | Description                    | Risk Factor                  |
| ---- | ------------------------------ | ---------------------------- |
| T002 | Add Eloquent models            | Straightforward              |
| T003 | Add repositories               | Straightforward              |
| T005 | Metric key registry/enum       | Localized change             |
| T007 | Policy ability                 | Simple authorization mapping |
| T020 | `useAnalyticsApi()` composable | Simple client wrapper        |
| T022 | Frontend unit tests            | Low-risk coverage            |

## External Dependencies

| Task ID | Package/Library        | Version | Purpose                    |
| ------- | ---------------------- | ------- | -------------------------- |
| T021    | (TBD) charting library | (TBD)   | Interactive charts in Nuxt |

## High-Downstream-Impact Tasks

| Task ID | Description        | Downstream Impact                 |
| ------- | ------------------ | --------------------------------- |
| T004    | Analytics services | Used by multiple endpoints + jobs |
| T013    | Aggregation job    | Feeds all KPIs and charts         |
| T012    | Cache layer        | Impacts all analytics API latency |
