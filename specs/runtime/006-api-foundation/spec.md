# STAGE_06 — API Foundation — Specification

**Phase:** 01_PLATFORM_FOUNDATION  
**Stage File:** `specs/phases/01_PLATFORM_FOUNDATION/STAGE_06_API_FOUNDATION.md`  
**Generated:** 2026-04-12  
**Status:** DRAFT

## Executive Summary

This stage formalizes the Bunyan REST API foundation: **versioned routing** under `/api/v1/*`, a **consistent success/error envelope**, **middleware ordering** (CORS, throttling, Sanctum, RBAC, correlation ID, structured API logging), **rate limiting** for sensitive public routes, **CORS** configuration for the Nuxt SPA, a **machine-readable health** endpoint for clients and probes, and a **baseline OpenAPI** document aligned with the error contract from STAGE_05.

The backend already implements large portions of this foundation (`BaseController`, `ApiResponse`, `InjectCorrelationId`, `LogApiActivity`, `ApiExceptionRenderer`, versioned `routes/api.php`). This specification locks **behavior and extension rules** for all future endpoints.

## User Stories

### US1 — Versioned API surface

**As** a client developer  
**I want** all JSON endpoints under `/api/v1/`  
**So that** breaking changes can be introduced in future versions without breaking existing apps.

**Acceptance criteria**

- Public and authenticated routes live under the Laravel `api` route file with `v1` prefix (full path `/api/v1/...`).
- No unversioned JSON business routes are added for new work.

### US2 — Stable response envelope

**As** a frontend engineer  
**I want** success and error payloads to follow the platform contract  
**So that** interceptors and UI can handle responses predictably.

**Acceptance criteria**

- Success responses use `success`, `data`, `message`, `errors`, `error` keys as implemented in `App\Http\Controllers\Api\ApiResponse`.
- Errors for `api/*` requests are rendered via `ApiExceptionRenderer` and `ApiErrorResponse` (STAGE_05 alignment).

### US3 — Observability on every API call

**As** platform operations  
**I want** correlation IDs and structured request logs  
**So that** incidents can be traced across services.

**Acceptance criteria**

- `InjectCorrelationId` runs globally; accepts or generates `X-Correlation-ID`.
- `LogApiActivity` records method, path, status, duration, user, role, correlation ID on the structured log channel.

### US4 — Health and readiness

**As** deployment automation  
**I want** a JSON health endpoint on the API prefix  
**So that** orchestrators can verify the API layer without HTML `/up` semantics.

**Acceptance criteria**

- `GET /api/v1/health` returns 200 with JSON including application name and environment-safe build metadata (no secrets).
- Existing Laravel `/up` and `GET /__ci_ready` (CI/local) remain unchanged.

### US5 — API documentation baseline

**As** an integrator  
**I want** an OpenAPI 3 description of core surface and health  
**So that** tools can generate clients and smoke tests.

**Acceptance criteria**

- Repository contains `backend/docs/openapi/openapi.yaml` describing at minimum the health route and global security schemes (Bearer / Sanctum).
- Document is valid OpenAPI 3.0.x and maintained when foundational routes change.

## Technical Requirements

### Routing

- `routes/api.php` registers the `v1` group; nested groups use `auth:sanctum` and `role:` middleware per ADR/RBAC rules.
- Named routes follow `*.index`, `*.show` conventions where applicable.

### Middleware stack (target order for API)

Conceptual order (Laravel may combine global + group middleware):

1. CORS (framework)
2. Substitute bindings / encryption (framework)
3. Correlation ID injection (global prepend)
4. Throttling where declared per route or group
5. `auth:sanctum` for protected JSON
6. `role:` / `permission:` aliases for RBAC
7. Controller
8. `LogApiActivity` + `ErrorDetailFiltering` on `api` middleware group (append)

### Rate limiting

- Public authentication and password flows use strict `throttle` limits already defined in `api.php`.
- Default API throttle uses Laravel defaults; new sensitive public endpoints must declare explicit limits.

### CORS

- `config/cors.php` is present and paths include `api/*`, `sanctum/csrf-cookie` as required for SPA + Sanctum.

### Base controller and resources

- `App\Http\Controllers\Api\V1\BaseController` remains the extension point for V1 controllers; uses `ApiResponse` trait.
- Domain `JsonResource` classes live under `App\Http\Resources\Api\V1` and return field arrays suitable for wrapping by `sendSuccess`.

## Non-Goals

- Implementing domain features beyond health/OpenAPI/CORS hardening for this stage.
- Replacing all controllers with services in this stage (covered by domain stages).

## Dependencies

- **STAGE_04** RBAC middleware (`CheckRole`, `CheckPermission`) and route usage.
- **STAGE_05** Error contract, `ApiExceptionRenderer`, structured logging channels.

## Clarifications

### Session 2026-04-12

1. **Health vs `/up`:** `/up` remains the framework default; API consumers use `/api/v1/health` for JSON status.
2. **OpenAPI scope:** Baseline file in-repo; interactive Swagger UI is optional and not required for stage closure.
3. **CORS:** Standard Laravel published configuration; environment-driven `FRONTEND_URL` / allowed origins from env where applicable.
