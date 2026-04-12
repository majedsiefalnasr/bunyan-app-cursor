# Tasks — RBAC System

> **Phase:** 01_PLATFORM_FOUNDATION > **Branch:** `spec/004-rbac-system` > **Generated:** 2026-04-12T00:00:00Z

## Phase A — Backend Foundation

- [x] T001 [US1] Create `backend/app/Http/Middleware/CheckRole.php` — validates `$request->user()->role` against allowed roles, throws `AuthorizationException` on mismatch
- [x] T002 [US1] Create `backend/app/Http/Middleware/CheckPermission.php` — validates user permissions via `Gate::forUser()->check()`, throws `AuthorizationException` on mismatch
- [x] T003 [US1] Register middleware aliases in `backend/bootstrap/app.php` — add `'role' => CheckRole::class`, `'permission' => CheckPermission::class`
- [x] T004 [US2] Add `RBAC_PERMISSION_DENIED` to `backend/app/Enums/ErrorCode.php` with HTTP 403 status
- [x] T005 [US2] Add translation entries for `RBAC_PERMISSION_DENIED` in `backend/resources/lang/ar/errors.php` and `backend/resources/lang/en/errors.php`
- [x] T006 [US2] Create `backend/app/Repositories/RoleRepository.php` extending `BaseRepository` — methods: `findByName()`, `allWithUserCounts()`
- [x] T007 [US2] Create `backend/app/Repositories/PermissionRepository.php` extending `BaseRepository` — methods: `findByName()`, `getForRole()`
- [x] T008 [US2] Create `backend/app/Services/RoleService.php` — methods: `getAllRoles()`, `getRolePermissions()`, `getUserPermissions()`, `assignRole()`, `removeRole()`, `syncUserRolePivot()`, `clearPermissionCache()`
- [x] T009 [US2] Register Gates dynamically in `backend/app/Providers/AppServiceProvider.php` `boot()` — `Gate::before()` admin bypass + permission gates from DB
- [x] T010 [US2] Add admin role management translations in `backend/resources/lang/ar/errors.php` and `backend/resources/lang/en/errors.php` — `RBAC_LAST_ADMIN`, role assignment success/reset messages

## Phase B — Admin Endpoints

- [x] T011 [US3] Create `backend/app/Http/Requests/Admin/AssignRoleRequest.php` — validates `role` against `UserRole` enum values
- [x] T012 [US3] Create `backend/app/Http/Resources/Api/V1/RoleResource.php` — includes `id`, `name`, `description`, `label`, `users_count`, `permissions_count`
- [x] T013 [US3] Create `backend/app/Http/Resources/Api/V1/PermissionResource.php` — includes `id`, `name`, `description`
- [x] T014 [US3] Create `backend/app/Http/Resources/Api/V1/UserAdminResource.php` — includes role label, permissions, status for admin views
- [x] T015 [US3] Create `backend/app/Http/Controllers/Api/V1/Admin/RoleController.php` — thin controller with methods: `index()`, `permissions()`, `users()`, `assignRole()`, `removeRole()`
- [x] T016 [US1][US3] Update `backend/app/Http/Resources/Api/V1/UserResource.php` — add `permissions` array from `RoleService::getUserPermissions()`

## Phase C — Route Restructuring

- [x] T017 [US1] Add admin route group in `backend/routes/api.php` — `Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])` with all 5 admin endpoints
- [x] T018 [US1] Apply role middleware to existing resource routes in `backend/routes/api.php` — restructure into role-based groups per permission matrix

## Phase D — Seeder Updates

- [x] T019 Update `backend/database/seeders/PermissionSeeder.php` — add `user.view`, `user.update`, `role.assign` permissions
- [x] T020 Update `backend/database/seeders/UserSeeder.php` — sync `role_user` pivot for all seeded users

## Phase E — Backend Testing

- [x] T021 [P] Create `backend/tests/Unit/Services/RoleServiceTest.php` — tests for `getAllRoles()`, `getRolePermissions()`, `getUserPermissions()`, `assignRole()`, `removeRole()`, `clearPermissionCache()`, last-admin safety check
- [x] T022 [P] Create `backend/tests/Feature/Admin/RoleEndpointTest.php` — tests for all 5 admin endpoints: happy path, validation, RBAC matrix (admin/non-admin/unauthenticated)
- [x] T023 [P] Create `backend/tests/Feature/Middleware/CheckRoleTest.php` — tests for authorized role passes, unauthorized role 403, unauthenticated 401
- [x] T024 [P] Create `backend/tests/Feature/Middleware/CheckPermissionTest.php` — tests for authorized permission passes, unauthorized permission 403, admin bypass

## Phase F — Frontend Implementation

- [x] T025 [US4] Create `frontend/composables/usePermission.ts` — exposes `hasPermission()`, `hasAnyPermission()`, `hasAllPermissions()`
- [x] T026 [US4] Update `frontend/stores/auth.ts` — add `permissions: string[]` to state, populate from API response on login/profile
- [x] T027 [US4] Update `frontend/composables/useAuth.ts` — expose `permissions`, `hasPermission()` from store
- [x] T028 [US4] Update `frontend/config/navigation.ts` — add optional `permissions?: string[]` to `NavItem`, filter navigation items by permissions
- [x] T029 [US6] Update `frontend/middleware/role.ts` — add toast notification on unauthorized redirect using Nuxt UI `useToast`
- [x] T030 [US5] Create `frontend/pages/admin/users.vue` — admin user management page with `UTable`, role filter `USelect`, pagination, `definePageMeta({ middleware: ['auth', 'role'], roles: ['admin'] })`
- [x] T031 [US5] Create `frontend/components/admin/AssignRoleModal.vue` — `UModal` with `USelect` for role selection, API call, toast notifications
- [x] T032 [US6] Wire `definePageMeta` on all existing protected pages — add `middleware: ['auth']` and role-specific middleware where applicable

## Phase G — Frontend Testing

- [x] T033 [P] Create `frontend/tests/unit/composables/usePermission.spec.ts` — tests for `hasPermission()`, `hasAnyPermission()`, `hasAllPermissions()`
- [x] T034 [P] Create `frontend/tests/unit/middleware/role.spec.ts` — tests for authorized access, unauthorized redirect, toast notification

## Summary

| Metric         | Value                                          |
| -------------- | ---------------------------------------------- |
| Total Tasks    | 34                                             |
| Backend Tasks  | 24 (T001–T024)                                 |
| Frontend Tasks | 10 (T025–T034)                                 |
| Parallel Tasks | 6 (T021–T024, T033–T034)                       |
| High Risk      | T017, T018 (route restructuring)               |
| Medium Risk    | T008, T009 (service + gates — core RBAC logic) |
