# Contract — GET /api/v1/health

## Request

- Method: `GET`
- Path: `/api/v1/health`
- Auth: none

## Response 200

Body follows `ApiResponse::sendSuccess`:

```json
{
  "success": true,
  "data": {
    "service": "bunyan-api",
    "version": "v1",
    "time": "2026-04-12T12:00:00+00:00",
    "app": "Bunyan"
  },
  "message": null,
  "errors": [],
  "error": null
}
```

Field notes:

- `time` is ISO-8601 timestamp from server.
- `app` is human-readable application name from config (no secrets).
