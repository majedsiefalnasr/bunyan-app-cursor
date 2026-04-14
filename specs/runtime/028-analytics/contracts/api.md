# Analytics — API Contract (Draft)

Base path: `/api/v1`

Auth: `auth:sanctum`

RBAC: Admin + Supervising Architect (server-side enforced via middleware + policy)

## Numeric Representation

- All numeric metric fields (`value`, `delta.value`, `delta.pct`, series `v`) are returned as **JSON numbers**.
- Backend implementation must normalize DB decimals to numbers in API Resources.
- Money metrics (e.g., GMV, avg order value) are rounded to **2 decimals** in API responses.
- For defined edge cases, some numeric fields may be `null`:
  - `delta.pct`: baseline 0 or missing
  - `delta.value`: baseline missing
  - ratio metric values: denominator 0
- If the requested range/bucket would exceed the max points cap, return **422** with a validation error advising a larger bucket or narrower range.

## Error Contract (canonical)

401 (unauthenticated):

```json
{
  "success": false,
  "data": null,
  "message": "Unauthenticated.",
  "errors": {}
}
```

403 (forbidden role):

```json
{
  "success": false,
  "data": null,
  "message": "Forbidden.",
  "errors": {}
}
```

422 (validation):

```json
{
  "success": false,
  "data": null,
  "message": "Validation error.",
  "errors": { "from": ["The from field must be a valid date."] }
}
```

429 (rate limited):

```json
{
  "success": false,
  "data": null,
  "message": "Too many requests.",
  "errors": {}
}
```

Notes:

- The exact 429 `message` string may vary by implementation, but the envelope shape MUST match this contract.

## `GET /analytics/overview`

Query:

- `from` (date, optional)
- `to` (date, optional)
- `bucket` (`day|week|month`, optional, default `day`)
- `compare` (`none|previous_period|previous_year`, optional, default `none`)

Defaults:

- If `from/to` are omitted, default to the **last 14 days ending today (UTC)**.

Constraints:

- `from/to` max range: 180 days
- Dates are interpreted as UTC dates for bucketing (week starts Monday; ISO-8601)
- Max points returned: 400 per series

Notes:

- Responses contain **aggregated metrics only** (no PII).
- Requests are **audit-logged** (who/endpoint/range).
- Range normalization: treat inputs as dates and query the half-open range \([from@00:00:00Z, (to+1 day)@00:00:00Z)\).

Response (success):

```json
{
  "success": true,
  "data": {
    "range": { "from": "2026-04-01", "to": "2026-04-14", "bucket": "day" },
    "compare": { "mode": "previous_period" },
    "kpis": [
      {
        "key": "platform.active_users",
        "label": "Active users",
        "value": 1234,
        "delta": { "value": 120, "pct": 0.108 }
      }
    ]
  },
  "message": "OK",
  "errors": {}
}
```

## `GET /analytics/metrics/{metric}`

Path:

- `metric`: metric key identifier (whitelisted enum)

Query:

- `from`, `to`, `bucket`

Defaults:

- If `from/to` are omitted, default to the **last 14 days ending today (UTC)**.

Constraints:

- `from/to` max range: 180 days
- Max points returned: 400 points

Response (success):

```json
{
  "success": true,
  "data": {
    "range": { "from": "2026-04-01", "to": "2026-04-14", "bucket": "day" },
    "key": "commerce.gmv",
    "bucket": "day",
    "series": [
      { "t": "2026-04-10", "v": 12000.5 },
      { "t": "2026-04-11", "v": 9000.0 }
    ]
  },
  "message": "OK",
  "errors": {}
}
```

## `GET /analytics/trends`

Query:

- `keys[]` (array of metric keys)
- `from`, `to`, `bucket`

Defaults:

- If `from/to` are omitted, default to the **last 14 days ending today (UTC)**.

Constraints:

- `keys[]` max length: 10
- `from/to` max range: 180 days
- Max points returned: 400 per series

Response (success):

```json
{
  "success": true,
  "data": {
    "range": { "from": "2026-04-01", "to": "2026-04-14", "bucket": "day" },
    "bucket": "day",
    "series": [
      {
        "key": "projects.new_projects",
        "points": [{ "t": "2026-04-10", "v": 3 }]
      }
    ]
  },
  "message": "OK",
  "errors": {}
}
```
