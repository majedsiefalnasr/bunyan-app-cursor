# Specify Report — RBAC System

> **Phase:** 01_PLATFORM_FOUNDATION > **Generated:** 2026-04-12T00:00:00Z

## Specification Summary

| Metric                 | Value                                                 |
| ---------------------- | ----------------------------------------------------- |
| User Stories           | 6                                                     |
| Acceptance Criteria    | 38                                                    |
| Technical Requirements | 31                                                    |
| Dependencies           | 2 upstream (Auth, DB Schema), all downstream features |
| Open Questions         | 0                                                     |

## Scope Defined

- RBAC middleware (`CheckRole`, `CheckPermission`) for all API routes
- Gate-based permission system wired to seeded `role_permissions` table
- Admin endpoints for role listing, permission listing, user role assignment/removal
- `RoleService` + `RoleRepository` + `PermissionRepository` (service/repository pattern)
- Frontend `usePermission` composable and permission-aware navigation
- Admin role management page with user table, role filter, and role assignment modal
- `definePageMeta` middleware wiring on all protected frontend pages
- Full test coverage: unit, feature, RBAC matrix, middleware, frontend composables

## Deferred Scope

- Custom role creation (roles are predefined/seeded)
- Permission CRUD by admin (permissions are code-defined)
- Multi-role per user (single role per user, pivot for audit only)
- OAuth role mapping
- Per-project role overrides

## Risk Assessment

- **HIGH:** RBAC middleware must be applied consistently across all existing routes — missing a route is a security gap
- **MEDIUM:** Permission cache invalidation timing — stale cache could allow unauthorized access briefly
- **MEDIUM:** `users.role` + `role_user` pivot sync — dual writes must be atomic
- **LOW:** Frontend navigation filtering — cosmetic issue only, server enforces access

## Checklist Status

- Requirements checklist: Created at `checklists/requirements.md`
