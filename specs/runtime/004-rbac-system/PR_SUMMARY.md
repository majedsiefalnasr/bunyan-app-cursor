# PR — RBAC System

## Summary

**Stage:** RBAC System  
**Phase:** 01_PLATFORM_FOUNDATION  
**Branch:** `spec/004-rbac-system` → `develop`  
**Tasks:** 34 / 34 completed

## What Changed

### Backend

- Role/permission middleware, admin role management API, dynamic Gates, `RbacException` handling where applicable.
- Route groups by role; admin product mutations under `/api/v1/admin/products`.
- Policies updated so contractor project updates, supervising-architect phase/task deletes, and routes stay consistent.

### Frontend

- Admin users management page, assign-role modal, permission composable, auth store permissions, navigation permission filters, role middleware toasts.

### Database

- Seeder updates for permissions and `role_user` pivot (no new migrations in this stage).

## Breaking Changes

- **API paths:** Product `POST`/`PUT`/`DELETE` for catalog management moved from `/api/v1/products` to `/api/v1/admin/products` (admin-only). Clients must update URLs.
- **Route matrix:** Phase create/update (contractor), phase delete (supervising architect), project update (contractor + policy), task delete (supervising architect) — callers must use the correct role.

## Testing

- [x] Unit tests pass (`php artisan test`)
- [x] Feature tests pass (`php artisan test`)
- [x] Frontend tests pass (`npm run test`)
- [x] Lint passes (`npm run lint`)
- [x] Type check passes (`npm run analyze` + `npm run typecheck`)

## Checklist

- [x] RBAC middleware applied on protected routes
- [x] Form Request validation on admin role assignment
- [x] Arabic/English strings for RBAC (`resources/lang/*/rbac.php`, errors)
- [x] Error contract followed for 403/401
- [x] Testing guide: `specs/runtime/004-rbac-system/guides/TESTING_GUIDE.md`

## Related

- Stage File: `specs/phases/01_PLATFORM_FOUNDATION/STAGE_04_RBAC_SYSTEM.md`
- Testing Guide: `specs/runtime/004-rbac-system/guides/TESTING_GUIDE.md`
