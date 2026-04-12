# STAGE_04 — RBAC System

> **Phase:** 01_PLATFORM_FOUNDATION
> **Status:** NOT STARTED
> **Scope:** Role-based access control, permissions, middleware
> **Risk Level:** HIGH

## Stage Status

Status: DRAFT
Step: tasks
Risk Level: HIGH
Last Updated: 2026-04-12T00:00:00Z
Tasks Generated: Total: 34 atomic tasks

Scope Planned:

- 2 RBAC middleware (CheckRole, CheckPermission)
- Gate registration from DB permissions (Redis cached)
- RoleService + RoleRepository + PermissionRepository
- 5 admin endpoints (roles, permissions, users, assign, remove)
- Route restructuring with role-based groups
- Frontend usePermission composable + admin page

Architecture Governance Compliance:

- Task set compliant — drift analysis required

Notes:
34 atomic tasks generated. Drift analysis pending before implementation.

## Objective

Implement a comprehensive RBAC system supporting five user roles with granular permissions. RBAC middleware must be applied on all protected routes.

## User Roles

| Role                  | Arabic       | Description                               |
| --------------------- | ------------ | ----------------------------------------- |
| Customer              | عميل         | End user requesting construction services |
| Contractor            | مقاول        | Service provider executing work           |
| Supervising Architect | مهندس مشرف   | Architect overseeing project compliance   |
| Field Engineer        | مهندس ميداني | On-site engineer managing execution       |
| Admin                 | مدير النظام  | Platform administrator                    |

## Scope

### Backend

- RBAC middleware for route protection
- Permission-based authorization (can/cannot)
- Role assignment and management service
- Admin role management endpoints
- Permission seeder with default role-permission mappings
- Gate definitions for complex authorization rules

### Frontend

- Role-based navigation rendering
- Permission-based UI element visibility
- Role management page (Admin only)

## Dependencies

- **Upstream:** STAGE_03_AUTHENTICATION
- **Downstream:** All protected features
