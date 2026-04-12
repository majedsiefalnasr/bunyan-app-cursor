# RBAC System

> **Phase:** 01_PLATFORM_FOUNDATION > **Stage File:** `specs/phases/01_PLATFORM_FOUNDATION/STAGE_04_RBAC_SYSTEM.md` > **Branch:** `spec/004-rbac-system` > **Created:** 2026-04-12T00:00:00Z

## Objective

Implement a production-grade Role-Based Access Control (RBAC) system for the Bunyan platform. The system wires existing database-seeded roles and permissions to a functional authorization layer, creates RBAC middleware for route-level protection, builds permission-checking Gates, adds admin endpoints for role/user management, and delivers a frontend role management interface. This stage bridges the gap between the existing `users.role` enum column (used in Policies) and the seeded `roles`/`permissions`/`role_permissions` tables that currently have no runtime enforcement.

## Scope

### In Scope

- **Backend:** `CheckRole` middleware — validates `users.role` against allowed roles per route group
- **Backend:** `CheckPermission` middleware — validates user permissions via `role_permissions` lookup
- **Backend:** Wire `roles`/`permissions` tables to authorization layer via Laravel Gates
- **Backend:** `RoleService` — business logic for role assignment, permission checks, role listing
- **Backend:** `RoleRepository` and `PermissionRepository` — data access for roles/permissions
- **Backend:** Admin endpoints: list roles, list permissions, list users by role, assign role to user, remove role from user
- **Backend:** `UserPolicy` updates — admin-only access for user/role management
- **Backend:** Sync `users.role` column with `role_user` pivot on role assignment (single source of truth)
- **Backend:** `RoleResource` and `PermissionResource` API resources
- **Backend:** Form Requests: `AssignRoleRequest`, `RemoveRoleRequest`
- **Backend:** Update all existing route groups with explicit role middleware
- **Backend:** Unit tests for `RoleService`, feature tests for RBAC endpoints, RBAC matrix tests
- **Frontend:** Permission-aware navigation — hide/show menu items based on user permissions
- **Frontend:** `usePermission` composable — check permissions fetched from API
- **Frontend:** Role management page (Admin only) — list users, assign/remove roles
- **Frontend:** Update auth store to include permissions array from API
- **Frontend:** Wire `definePageMeta({ middleware: ['auth', 'role'], roles: [...] })` on all protected pages
- **Frontend:** Vitest tests for permission composable and role middleware

### Out of Scope

- Custom role creation (roles are predefined and seeded — no dynamic role creation)
- Permission CRUD by admin (permissions are code-defined and seeded)
- Multi-role per user (each user has exactly one role; `role_user` pivot maintained for audit trail only)
- OAuth/social login role mapping (future stage)
- Per-project role overrides (e.g., a user as contractor on one project and customer on another — future workflow stage)

## User Stories

### US1 — Route-Level RBAC Protection

**As a** platform administrator, **I want** all API routes protected by role-based middleware, **so that** users can only access endpoints authorized for their role.

**Acceptance Criteria:**

- [ ] `CheckRole` middleware registered as `role` alias in `bootstrap/app.php`
- [ ] `CheckPermission` middleware registered as `permission` alias in `bootstrap/app.php`
- [ ] Admin-only routes (`/api/v1/admin/*`) require `role:admin` middleware
- [ ] All resource routes have appropriate role middleware applied
- [ ] Unauthorized role access returns 403 with error code `RBAC_ROLE_DENIED`
- [ ] Unauthorized permission access returns 403 with error code `RBAC_PERMISSION_DENIED`
- [ ] Middleware parameters accept comma-separated roles/permissions: `role:admin,contractor`
- [ ] All middleware checks validated with feature tests (authorized, unauthorized, unauthenticated)

### US2 — Permission-Based Authorization

**As a** developer, **I want** a Gate-based permission system tied to the seeded `role_permissions` table, **so that** I can enforce granular access control beyond role checks.

**Acceptance Criteria:**

- [ ] Gates defined in `AuthServiceProvider` for all seeded permissions
- [ ] `Gate::before()` grants admin full access (superuser pattern)
- [ ] Permission check via `$user->can('project.create')` uses DB-backed permissions
- [ ] Policies updated to use `can()` checks where granular control is needed
- [ ] Permission results cached per request (no N+1 on repeated checks)
- [ ] Missing permission returns 403 with structured error response

### US3 — Admin Role Management

**As an** admin, **I want** to view all users grouped by role and assign/remove roles, **so that** I can manage platform access.

**Acceptance Criteria:**

- [ ] `GET /api/v1/admin/roles` — list all roles with user counts
- [ ] `GET /api/v1/admin/roles/{role}/permissions` — list permissions for a role
- [ ] `GET /api/v1/admin/users?role={role}` — list users filtered by role (paginated)
- [ ] `POST /api/v1/admin/users/{user}/role` — assign role to user (updates both `users.role` and `role_user` pivot)
- [ ] `DELETE /api/v1/admin/users/{user}/role` — reset user to `customer` role (default)
- [ ] All admin endpoints require `role:admin` middleware
- [ ] Role changes logged with structured audit context (who, what, when)
- [ ] Cannot remove the last admin user from admin role (safety check)
- [ ] All endpoints return responses following Bunyan error contract

### US4 — Frontend Permission-Aware Navigation

**As a** logged-in user, **I want** the navigation menu to show only items I have permission to access, **so that** the UI is clean and role-appropriate.

**Acceptance Criteria:**

- [ ] Auth store fetches user permissions on login/profile refresh
- [ ] `usePermission` composable provides `hasPermission(name)` and `hasAnyPermission(names)`
- [ ] Navigation config uses permissions to filter menu items
- [ ] Sidebar, mobile drawer, and header menu respect role/permission visibility
- [ ] Hidden items are truly absent from the DOM (not just `display:none`)

### US5 — Frontend Role Management Page

**As an** admin, **I want** a role management page in the admin dashboard, **so that** I can view and manage user roles.

**Acceptance Criteria:**

- [ ] Page at `/ar/admin/users` (admin only, gated by `definePageMeta`)
- [ ] Table displays all users with: name, email, current role (Arabic label), status, joined date
- [ ] Filter by role dropdown
- [ ] Assign role action via modal with role selection dropdown
- [ ] Success/error toast notifications in Arabic
- [ ] Responsive table (Nuxt UI `UTable`)
- [ ] Pagination with Nuxt UI pagination component

### US6 — Protected Page Middleware Wiring

**As a** frontend developer, **I want** all protected pages to declare their auth and role requirements via `definePageMeta`, **so that** unauthorized navigation is prevented client-side.

**Acceptance Criteria:**

- [ ] All dashboard pages use `definePageMeta({ middleware: ['auth'] })`
- [ ] Admin pages add `definePageMeta({ middleware: ['auth', 'role'], roles: ['admin'] })`
- [ ] Role-specific pages declare allowed roles in meta
- [ ] Unauthorized access redirects to `/ar/dashboard` with toast message
- [ ] Guest pages (login, register) redirect authenticated users away

## Technical Requirements

### Backend (Laravel)

- [ ] Create `app/Http/Middleware/CheckRole.php` — accepts role parameters, validates `$request->user()->role` against allowed roles
- [ ] Create `app/Http/Middleware/CheckPermission.php` — accepts permission parameters, validates via `Gate::check()` or cached permission lookup
- [ ] Register both middleware as aliases in `bootstrap/app.php`: `'role' => CheckRole::class`, `'permission' => CheckPermission::class`
- [ ] Create `app/Services/RoleService.php` — methods: `getAllRoles()`, `getRolePermissions(Role)`, `assignRole(User, string)`, `removeRole(User)`, `getUserPermissions(User)`, `syncUserRolePivot(User)`
- [ ] Create `app/Repositories/RoleRepository.php` extending `BaseRepository` — `findByName()`, `allWithUserCounts()`, `getPermissionsForRole()`
- [ ] Create `app/Repositories/PermissionRepository.php` extending `BaseRepository` — `findByName()`, `getForRole(Role)`
- [ ] Create `app/Http/Controllers/Api/V1/Admin/RoleController.php` — thin controller delegating to `RoleService`
- [ ] Create `app/Http/Resources/RoleResource.php` and `PermissionResource.php`
- [ ] Create `app/Http/Requests/Admin/AssignRoleRequest.php` — validates `role` field against `UserRole` enum values
- [ ] Register Gates in `app/Providers/AppServiceProvider.php` `boot()` — dynamically from `permissions` table (cached)
- [ ] `Gate::before()` — admin superuser bypass
- [ ] Add admin route group in `routes/api.php`: `Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(...)`
- [ ] Apply role middleware to existing resource routes based on the permission matrix
- [ ] `users.role` column remains the single source of truth for primary role; `role_user` pivot updated on every role change for audit
- [ ] Create `app/Http/Resources/UserAdminResource.php` — includes role, permissions, status for admin views
- [ ] Update `UserResource.php` to include `permissions` array (list of permission name strings)
- [ ] Structured logging for all role changes: `Log::channel('api')->info('role.assigned', [...])`
- [ ] Unit tests: `RoleServiceTest` (all methods, edge cases)
- [ ] Feature tests: all admin endpoints (happy path, validation, RBAC matrix — admin/non-admin/unauthenticated)
- [ ] Feature tests: middleware enforcement on protected routes (authorized role, unauthorized role, no auth)

### Frontend (Nuxt.js)

- [ ] Create `composables/usePermission.ts` — exposes `hasPermission(name)`, `hasAnyPermission(names)`, `hasAllPermissions(names)`
- [ ] Update `stores/auth.ts` — add `permissions: string[]` to state, populated from `UserResource.permissions` on login/profile fetch
- [ ] Update `composables/useAuth.ts` — expose `permissions`, `hasPermission()` from store
- [ ] Update `config/navigation.ts` — add `permissions?: string[]` to nav item type, filter items in sidebar
- [ ] Create `pages/admin/users.vue` — admin user management page with Nuxt UI `UTable`, role filter, role assignment modal
- [ ] Create `components/admin/AssignRoleModal.vue` — role selection modal with Nuxt UI `UModal`, `USelect`
- [ ] Wire `definePageMeta` on all existing pages: dashboard pages get `auth` middleware, admin pages get `auth` + `role` middleware
- [ ] Update `middleware/role.ts` — add toast notification on unauthorized redirect
- [ ] Update sidebar/drawer components to filter navigation items by permissions
- [ ] Vitest: `usePermission` composable tests, role middleware tests, auth store permission integration

## Permission Matrix

| Permission         | Customer    | Contractor       | Sup. Architect     | Field Engineer   | Admin |
| ------------------ | ----------- | ---------------- | ------------------ | ---------------- | ----- |
| `project.view`     | Own         | Assigned         | Supervised         | Assigned         | All   |
| `project.create`   | Yes         | No               | No                 | No               | Yes   |
| `project.update`   | Own         | Assigned         | No                 | No               | Yes   |
| `project.delete`   | No          | No               | No                 | No               | Yes   |
| `phase.view`       | Own project | Assigned project | Supervised project | Assigned project | All   |
| `phase.create`     | No          | Assigned project | No                 | No               | Yes   |
| `phase.update`     | No          | Assigned project | Supervised project | No               | Yes   |
| `task.view`        | Own project | Assigned project | Supervised project | Assigned tasks   | All   |
| `task.create`      | No          | Yes              | No                 | No               | Yes   |
| `task.update`      | No          | Yes              | Yes                | Assigned         | Yes   |
| `report.view`      | Own project | Own project      | Supervised project | Own              | All   |
| `report.create`    | No          | No               | No                 | Yes              | Yes   |
| `report.update`    | No          | No               | No                 | Own              | Yes   |
| `product.view`     | All         | All              | All                | All              | All   |
| `product.create`   | No          | No               | No                 | No               | Yes   |
| `product.update`   | No          | No               | No                 | No               | Yes   |
| `product.delete`   | No          | No               | No                 | No               | Yes   |
| `order.view`       | Own         | No               | No                 | No               | All   |
| `order.create`     | Yes         | No               | No                 | No               | Yes   |
| `transaction.view` | Own         | Own              | No                 | No               | All   |
| `user.view`        | No          | No               | No                 | No               | Yes   |
| `user.update`      | No          | No               | No                 | No               | Yes   |
| `role.assign`      | No          | No               | No                 | No               | Yes   |

## Dependencies

- **Upstream:** STAGE_03_AUTHENTICATION (Sanctum auth, User model, auth middleware, auth store)
- **Upstream:** STAGE_02_DATABASE_SCHEMA (roles, permissions, role_permissions, role_user tables + seeders)
- **Downstream:** All protected features (projects, phases, tasks, reports, e-commerce, admin dashboard)

## Non-Functional Requirements

- [ ] Middleware overhead < 5ms per request (permission lookup cached per request)
- [ ] Admin endpoints paginated (default 15 per page)
- [ ] All Arabic labels for roles and permissions in UI
- [ ] RTL layout on role management page
- [ ] Error contract compliance on all new endpoints
- [ ] Permission cache invalidated on role change
- [ ] No permission data leak to unauthorized users (permissions array only in authenticated user's own resource)
- [ ] All role changes produce audit log entries

## Architecture Notes

### Role Source of Truth

The `users.role` column (string enum) is the **primary role identifier** used across the application. The `role_user` pivot table serves as an **audit trail** for role assignments (who assigned, when). When a role is assigned:

1. `users.role` is updated (primary)
2. `role_user` pivot row is inserted/updated (audit)
3. Permission cache is cleared for the user

### Permission Resolution Flow

```
Request → auth:sanctum → CheckRole middleware (users.role) → CheckPermission middleware (Gate::check)
                                                                    ↓
                                                        Gate::before (admin bypass)
                                                                    ↓
                                                        Gate::define (DB permissions via role_permissions)
                                                                    ↓
                                                        Cached per-request permission set
```

### Middleware Application Strategy

```
Route::prefix('v1')->group(function () {
    // Public routes (no middleware)
    Route::prefix('auth')->group(...);

    // Authenticated routes (all roles)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::apiResource('products', ProductController::class)->only(['index', 'show']);
        // ...
    });

    // Admin-only routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('roles/{role}/permissions', [RoleController::class, 'permissions']);
        Route::get('users', [RoleController::class, 'users']);
        Route::post('users/{user}/role', [RoleController::class, 'assignRole']);
        Route::delete('users/{user}/role', [RoleController::class, 'removeRole']);
    });
});
```

## Open Questions

- None. All clarifications resolved in session below.
