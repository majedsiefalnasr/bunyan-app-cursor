# Requirements Checklist — RBAC System

## Backend — Middleware

- [ ] `CheckRole` middleware created at `app/Http/Middleware/CheckRole.php`
- [ ] `CheckPermission` middleware created at `app/Http/Middleware/CheckPermission.php`
- [ ] Both middleware registered as aliases in `bootstrap/app.php`
- [ ] `CheckRole` validates `$request->user()->role` against parameter list
- [ ] `CheckPermission` validates via Gate check or cached permission lookup
- [ ] 403 response with `RBAC_ROLE_DENIED` on role mismatch
- [ ] 403 response with `RBAC_PERMISSION_DENIED` on permission mismatch
- [ ] Middleware accepts comma-separated parameters: `role:admin,contractor`

## Backend — Service Layer

- [ ] `RoleService` created at `app/Services/RoleService.php`
- [ ] `RoleService::getAllRoles()` returns all roles with user counts
- [ ] `RoleService::getRolePermissions(Role)` returns permissions for a role
- [ ] `RoleService::assignRole(User, string)` updates `users.role` and `role_user` pivot
- [ ] `RoleService::removeRole(User)` resets to customer role
- [ ] `RoleService::getUserPermissions(User)` returns permission names for user's role
- [ ] `RoleService::syncUserRolePivot(User)` syncs pivot with `users.role`
- [ ] Safety check: cannot remove last admin from admin role

## Backend — Repository Layer

- [ ] `RoleRepository` created extending `BaseRepository`
- [ ] `PermissionRepository` created extending `BaseRepository`
- [ ] `RoleRepository::findByName()` implemented
- [ ] `RoleRepository::allWithUserCounts()` implemented
- [ ] `PermissionRepository::getForRole(Role)` implemented

## Backend — Controllers & Routes

- [ ] `Admin\RoleController` created with thin controller pattern
- [ ] `GET /api/v1/admin/roles` endpoint
- [ ] `GET /api/v1/admin/roles/{role}/permissions` endpoint
- [ ] `GET /api/v1/admin/users` endpoint (with role filter, pagination)
- [ ] `POST /api/v1/admin/users/{user}/role` endpoint
- [ ] `DELETE /api/v1/admin/users/{user}/role` endpoint
- [ ] Admin route group with `auth:sanctum` + `role:admin` middleware
- [ ] Existing resource routes updated with role middleware

## Backend — Authorization

- [ ] Gates registered dynamically from `permissions` table in `AppServiceProvider::boot()`
- [ ] `Gate::before()` admin superuser bypass
- [ ] Permission set cached per request
- [ ] Policies updated to use `can()` checks where applicable
- [ ] `UserPolicy` updated for admin-only user management

## Backend — API Resources & Form Requests

- [ ] `RoleResource` created
- [ ] `PermissionResource` created
- [ ] `UserAdminResource` created (includes role, permissions, status)
- [ ] `UserResource` updated to include `permissions` array
- [ ] `AssignRoleRequest` form request with `UserRole` enum validation

## Backend — Logging & Audit

- [ ] Role assignment logged with structured context
- [ ] Role removal logged with structured context
- [ ] `role_user` pivot updated on every role change

## Backend — Testing

- [ ] Unit tests for `RoleService` (all methods)
- [ ] Feature tests for admin role endpoints (happy path)
- [ ] Feature tests for RBAC matrix (admin vs non-admin vs unauthenticated)
- [ ] Feature tests for `CheckRole` middleware
- [ ] Feature tests for `CheckPermission` middleware

## Frontend — Composables & Store

- [ ] `usePermission` composable created
- [ ] `hasPermission(name)` method works
- [ ] `hasAnyPermission(names)` method works
- [ ] Auth store updated with `permissions: string[]`
- [ ] Permissions populated from API on login/profile fetch
- [ ] `useAuth` exports permission helpers

## Frontend — Pages & Components

- [ ] Admin user management page at `pages/admin/users.vue`
- [ ] `AssignRoleModal` component created
- [ ] User table with role filter and pagination
- [ ] Role assignment via modal with success/error toasts
- [ ] Arabic labels for all roles
- [ ] RTL layout verified

## Frontend — Middleware & Navigation

- [ ] All dashboard pages have `definePageMeta({ middleware: ['auth'] })`
- [ ] Admin pages have `definePageMeta({ middleware: ['auth', 'role'], roles: ['admin'] })`
- [ ] Navigation config updated with permission filtering
- [ ] Sidebar/drawer filter items by permissions
- [ ] Role middleware shows toast on unauthorized redirect

## Frontend — Testing

- [ ] Vitest for `usePermission` composable
- [ ] Vitest for role middleware behavior
- [ ] Vitest for auth store permission integration
