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
- `session_id` (varchar, nullable, indexed) — optional linkage for session metrics
- `thread_id` (varchar, nullable, indexed) — optional linkage for response-time metrics
- `request_id` (varchar, nullable, indexed) — optional linkage for response-time metrics
- `created_at`, `updated_at`

Notes:

- Append-only semantics (updates discouraged).
- Metadata governance:
  - Enforce an allowlist of dimension keys: `order_id`, `project_id`, `supplier_id`
  - Enforce caps: max keys 10, max value length 64 chars, max JSON bytes 1024
  - Forbid PII keys/values

Indexes (recommended additions):

- Composite: `(event_name, occurred_at)`
- Composite: `(session_id, occurred_at)`
- Composite: `(thread_id, occurred_at)`
- Composite: `(request_id, occurred_at)`
- Composite (recommended for linkage joins): `(session_id, event_name, occurred_at)`
- Composite (recommended for linkage joins): `(thread_id, event_name, occurred_at)`
- Composite (recommended for linkage joins): `(request_id, event_name, occurred_at)`

### `analytics_metric_rollups` (aggregated metrics)

- `id` (bigint, PK)
- `metric_key` (varchar, indexed) — e.g. `platform.active_users`
- `bucket` (enum: `day|week|month`, indexed)
- `bucket_start` (date/datetime, indexed)
- `bucket_end` (date/datetime, indexed)
- `value` (decimal(18, 4))
- `dimensions` (json, nullable) — e.g. `{ "project_id": 123 }` when needed
- `dimensions_hash` (char(64), indexed) — sha256 of canonicalized `dimensions` JSON
- `computed_at` (datetime)
- `created_at`, `updated_at`

Indexes:

- `(metric_key, bucket, bucket_start)`
- Uniqueness (recommended): unique `(metric_key, bucket, bucket_start, dimensions_hash)`

Canonicalization:

- Dimensions schema: **flat object only** (no arrays, no nesting)
  - Values allowed: string | number | boolean | null
  - Canonical JSON must preserve JSON types (do not coerce types)
- `dimensions` keys sorted ascending, no whitespace, empty dimensions treated as `{}`
- `dimensions_hash = sha256_hex(canonical_dimensions_json)` (lowercase hex digest)

## Retention

- Raw events retention: 90 days
- Rollups retention: 2 years

## Time buckets

- `bucket_start` and `bucket_end` are stored as **UTC dates** (`YYYY-MM-DD`)
- `bucket_start` is inclusive; `bucket_end` is exclusive

## Audit logging (analytics reads)

- No dedicated DB table in this stage.
- Audit events are emitted as structured application logs (include: user_id, role, endpoint, range, bucket, keys_count, response_size).
