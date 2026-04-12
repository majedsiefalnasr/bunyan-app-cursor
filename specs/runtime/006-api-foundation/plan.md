# Technical Plan — API Foundation

## Overview

Finalize API layer conventions: confirm versioned routing, document middleware stack, publish CORS, add JSON `GET /api/v1/health`, ship baseline OpenAPI, and verify observability middleware. Align all artifacts with existing `BaseController` / `ApiResponse` / `ApiExceptionRenderer`.

## Implementation Outline

1. **Health endpoint** — `HealthController` (invokable or single action) extending `BaseController`, registered in `routes/api.php` inside `v1` prefix without `auth:sanctum`.
2. **CORS** — Run `php artisan config:publish cors` if missing; tune `paths` and `allowed_origins` for SPA + Sanctum.
3. **OpenAPI** — Add `backend/docs/openapi/openapi.yaml` with info, servers, components (securitySchemes bearerAuth), `/api/v1/health` path.
4. **Tests** — Feature test: unauthenticated `GET /api/v1/health` returns 200 and expected JSON keys.
5. **Documentation** — Update runtime `quickstart.md` with curl examples.

## Middleware (reference)

Global prepend: `InjectCorrelationId`. API group append: `LogApiActivity`, `ErrorDetailFiltering`. Route-level: `throttle`, `auth:sanctum`, `role:`.

## Risks

- CORS misconfiguration blocks browser clients — validate with config comments and default safe local origins.

## Guardian alignment

- No RBAC bypass on new route (public health only).
- No business logic in new controller beyond assembling metadata.
