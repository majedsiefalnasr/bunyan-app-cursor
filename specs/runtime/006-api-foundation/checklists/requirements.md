# Requirements Checklist — API Foundation

## Functional

- [x] All new JSON endpoints are registered under `/api/v1/` unless exempted by ADR.
- [x] `GET /api/v1/health` returns 200 JSON with safe metadata in `testing` and `local`.
- [x] OpenAPI baseline documents health and security schemes.

## Non-functional

- [x] Correlation ID present on API requests (incoming header or generated).
- [x] API requests logged with duration and status on structured channel.
- [x] CORS configuration published and includes `api/*`.

## Security & RBAC

- [x] No RBAC bypass: protected routes remain behind `auth:sanctum` and role middleware.
- [x] Rate limits remain on public auth routes.

## Contract & i18n

- [x] Success responses follow `ApiResponse` shape.
- [x] API errors follow STAGE_05 contract for `api/*` JSON requests.

## Testing

- [x] Feature test covers `GET /api/v1/health` success path.
- [x] PHPUnit suite passes with new routes.
