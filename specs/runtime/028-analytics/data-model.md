# Analytics — Data Model

## Overview

This stage introduces **raw analytics events** plus **time-bucketed rollups** used by read APIs and the dashboard.

## Tables

### `analytics_events` (raw events)

- `id` (bigint, PK)
- `event_name` (varchar, indexed)
- `occurred_at` (datetime, indexed)
- `user_id` (bigint, nullable, indexed)
- `role` (varchar, nullable) — snapshot of user role at time of event
- `metadata` (json) — flexible dimensions (e.g., order_id, project_id)
- `created_at`, `updated_at`

Notes:

- Append-only semantics (updates discouraged).
- Avoid PII in metadata unless explicitly required.

### `analytics_metric_rollups` (aggregated metrics)

- `id` (bigint, PK)
- `metric_key` (varchar, indexed) — e.g. `platform.active_users`
- `bucket` (enum: `day|week|month`, indexed)
- `bucket_start` (date/datetime, indexed)
- `bucket_end` (date/datetime, indexed)
- `value` (decimal(18, 4))
- `dimensions` (json, nullable) — e.g. `{ "project_id": 123 }` when needed
- `computed_at` (datetime)
- `created_at`, `updated_at`

Indexes:

- `(metric_key, bucket, bucket_start)`

## Retention

- Raw events retention: 90 days
- Rollups retention: 2 years
