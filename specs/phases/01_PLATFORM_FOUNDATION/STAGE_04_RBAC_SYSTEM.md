# STAGE_04 — RBAC System

> **Phase:** 01_PLATFORM_FOUNDATION
> **Status:** PRODUCTION READY
> **Scope:** Role-based access control, permissions, middleware
> **Risk Level:** HIGH

## Stage Status

Status: PRODUCTION READY  
Step: stage_production_ready  
Risk Level: HIGH  
Closure Date: 2026-04-12

Scope Closed: RBAC middleware on API routes, admin role management API, dynamic permission gates, policy alignment with route matrix, Nuxt admin users UI and permission-aware navigation, PHPUnit and Vitest coverage. Tasks 34 / 34 completed.

Deferred Scope: None

Architecture Governance Compliance:

- ADR alignment verified (no new ADR required for this delta)
- RBAC enforcement confirmed on protected routes
- Service layer architecture maintained for role assignment flows
- Error contract compliance verified for authorization failures

Notes: Stage closed via orchestrator autopilot resume. Local `migrate --pretend` against agent `.env` may fail if DB unreachable; PHPUnit migration smoke tests passed.

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
