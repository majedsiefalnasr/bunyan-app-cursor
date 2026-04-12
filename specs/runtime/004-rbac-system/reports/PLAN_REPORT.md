# Plan Report — RBAC System

> **Phase:** 01_PLATFORM_FOUNDATION > **Generated:** 2026-04-12T00:00:00Z

## Plan Summary

| Metric                | Value                                                                   |
| --------------------- | ----------------------------------------------------------------------- |
| New Tables            | 0 (leveraging existing roles, permissions, role_permissions, role_user) |
| New Endpoints         | 5 admin endpoints                                                       |
| New Services          | 1 (RoleService)                                                         |
| New Repositories      | 2 (RoleRepository, PermissionRepository)                                |
| New Middleware        | 2 (CheckRole, CheckPermission)                                          |
| New Pages             | 1 (Admin user management)                                               |
| New Components        | 1 (AssignRoleModal)                                                     |
| New Composables       | 1 (usePermission)                                                       |
| Modified Files        | ~15 backend, ~8 frontend                                                |
| Implementation Phases | 7 (A through G)                                                         |

## Architecture Decisions

1. **Dual authorization layers:** Role middleware (route-level) + Policies (controller-level) for defense-in-depth
2. **Gates from DB:** Permissions registered dynamically from `permissions` table, cached application-wide
3. **Redis per-user cache:** User permissions cached in Redis, invalidated on role change
4. **Admin superuser bypass:** `Gate::before()` grants admin full access without permission checks
5. **Token revocation on role change:** Forces re-authentication to ensure fresh permissions
6. **`users.role` remains primary:** DB pivot `role_user` is audit trail only, not primary role source
7. **Dedicated admin namespace:** `Admin\RoleController` separated from public controllers

## Guardian Verdicts

| Guardian              | Verdict | Notes                                                                               |
| --------------------- | ------- | ----------------------------------------------------------------------------------- |
| Architecture Guardian | PASS    | Follows service/repository pattern, thin controllers, RBAC middleware on all routes |
| API Designer          | PASS    | RESTful admin endpoints, Bunyan error contract, pagination, proper auth middleware  |

## Risk Assessment

| Risk Level | Count | Details                                                                                                                            |
| ---------- | ----- | ---------------------------------------------------------------------------------------------------------------------------------- |
| HIGH       | 1     | Route restructuring — changing middleware on all existing routes requires careful testing to avoid breaking existing functionality |
| MEDIUM     | 2     | Redis dependency for permission cache; dual `users.role` + `role_user` sync atomicity                                              |
| LOW        | 2     | Frontend navigation filtering; seeder updates                                                                                      |
