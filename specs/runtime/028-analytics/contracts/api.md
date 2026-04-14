# Analytics — API Contract (Draft)

Base path: `/api/v1`

Auth: `auth:sanctum`

RBAC: Admin + Supervising Architect (server-side enforced via middleware + policy)

## Numeric Representation

- All numeric metric fields (`value`, `delta.value`, `delta.pct`, series `v`) are returned as **JSON numbers**.
- Backend implementation must normalize DB decimals to numbers in API Resources.

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

## `GET /analytics/overview`

Query:

- `from` (date, optional)
- `to` (date, optional)
- `bucket` (`day|week|month`, optional, default `day`)
- `compare` (`none|previous_period|previous_year`, optional, default `none`)

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
