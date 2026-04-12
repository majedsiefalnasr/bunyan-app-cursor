# PR — API Foundation

## Summary

**Stage:** API Foundation  
**Phase:** 01_PLATFORM_FOUNDATION  
**Branch:** `spec/006-api-foundation` → `develop`  
**Tasks:** 5 / 5 completed

## What Changed

### Backend

- Added public `GET /api/v1/health` (`HealthController`) returning the standard success envelope with service metadata and optional correlation ID echo.
- Published and tuned `config/cors.php` for SPA + Sanctum (`api/*`, `sanctum/csrf-cookie`, credentials, exposed `X-Correlation-ID`).
- Added baseline OpenAPI description at `backend/docs/openapi/openapi.yaml`.

### Frontend

- No code changes (CORS supports existing Nuxt consumers).

### Database

- No migrations.

## Breaking Changes

- None. New route is additive. CORS is stricter (explicit origins + credentials); local Nuxt defaults (`localhost` / `127.0.0.1` on port 3000) and `FRONTEND_URL` are allowed.

## Testing

- [x] Unit + feature tests pass (`cd backend && php artisan test`)
- [x] Frontend tests pass (`npm run test`)
- [x] Lint passes (`npm run lint`)
- [x] Type check passes (`npm run typecheck`)
- [x] PHPStan passes (`cd backend && vendor/bin/phpstan analyse`)
- [x] Migration pretend pass with sqlite in-memory env (see `audits/VALIDATION_REPORT.md`)

## Checklist

- [x] RBAC unchanged on protected routes; new route is public by design
- [x] No new write endpoints (no Form Requests added)
- [x] Arabic/RTL N/A for JSON-only addition
- [x] Error contract followed for existing stack; health uses `sendSuccess`
- [x] API documentation updated (OpenAPI baseline)

## Related

- Stage File: `specs/phases/01_PLATFORM_FOUNDATION/STAGE_06_API_FOUNDATION.md`
- Testing Guide: `specs/runtime/006-api-foundation/guides/TESTING_GUIDE.md`
