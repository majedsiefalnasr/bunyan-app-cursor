# Technical Plan — RBAC System

> **Phase:** 01_PLATFORM_FOUNDATION > **Stage File:** `specs/phases/01_PLATFORM_FOUNDATION/STAGE_04_RBAC_SYSTEM.md` > **Branch:** `spec/004-rbac-system`

## Implementation Phases

### Phase A — Backend Foundation (Middleware + Gates + Service Layer)

**Objective:** Create the core RBAC infrastructure — middleware, Gate registration, service layer, and repositories.

#### A1. Create RBAC Middleware

**Files:**

- `backend/app/Http/Middleware/CheckRole.php`
- `backend/app/Http/Middleware/CheckPermission.php`

**CheckRole Middleware:**

```php
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user || !in_array($user->role->value, $roles)) {
            throw new AuthorizationException(__('errors.codes.RBAC_ROLE_DENIED.message'));
        }
        return $next($request);
    }
}
```

**CheckPermission Middleware:**

```php
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException();
        }
        foreach ($permissions as $permission) {
            if (!Gate::forUser($user)->check($permission)) {
                throw new AuthorizationException(__('errors.codes.RBAC_PERMISSION_DENIED.message'));
            }
        }
        return $next($request);
    }
}
```

**Registration in `bootstrap/app.php`:**

```php
$middleware->alias([
    'role' => \App\Http\Middleware\CheckRole::class,
    'permission' => \App\Http\Middleware\CheckPermission::class,
]);
```

#### A2. Register Gates and Admin Bypass

**File:** `backend/app/Providers/AppServiceProvider.php`

```php
public function boot(): void
{
    $this->registerPermissionGates();
}

private function registerPermissionGates(): void
{
    Gate::before(function (User $user, string $ability) {
        if ($user->role === UserRole::Admin) {
            return true;
        }
    });

    $permissions = Cache::rememberForever('permissions:all', function () {
        return Permission::pluck('name')->toArray();
    });

    foreach ($permissions as $permission) {
        Gate::define($permission, function (User $user) use ($permission) {
            $userPermissions = app(RoleService::class)->getUserPermissions($user);
            return in_array($permission, $userPermissions);
        });
    }
}
```

#### A3. Create Service and Repository Layer

**Files:**

- `backend/app/Services/RoleService.php`
- `backend/app/Repositories/RoleRepository.php`
- `backend/app/Repositories/PermissionRepository.php`

**RoleService methods:**

| Method                                                        | Description                                                                 |
| ------------------------------------------------------------- | --------------------------------------------------------------------------- |
| `getAllRoles()`                                               | Returns all roles with user counts                                          |
| `getRolePermissions(Role $role)`                              | Returns permissions for a role                                              |
| `getUserPermissions(User $user)`                              | Returns cached permission names for user                                    |
| `assignRole(User $user, string $roleName, ?User $assignedBy)` | Updates `users.role`, syncs `role_user` pivot, revokes tokens, clears cache |
| `removeRole(User $user, ?User $assignedBy)`                   | Resets to `customer`, syncs pivot, revokes tokens, clears cache             |
| `syncUserRolePivot(User $user, ?User $assignedBy)`            | Syncs `role_user` pivot with current `users.role`                           |
| `clearPermissionCache(User $user)`                            | Removes user permission cache from Redis                                    |

**RoleRepository:**

- `findByName(string $name): ?Role`
- `allWithUserCounts(): Collection`
- `getPermissionsForRole(Role $role): Collection`

**PermissionRepository:**

- `findByName(string $name): ?Permission`
- `getForRole(Role $role): Collection`

### Phase B — Admin Endpoints

**Objective:** Create admin-only endpoints for role and user management.

#### B1. Create Admin Controller

**File:** `backend/app/Http/Controllers/Api/V1/Admin/RoleController.php`

```php
class RoleController extends BaseController
{
    public function __construct(private RoleService $roleService) {}

    public function index(): JsonResponse { /* List all roles with counts */ }
    public function permissions(Role $role): JsonResponse { /* List role permissions */ }
    public function users(Request $request): JsonResponse { /* List users, optional role filter */ }
    public function assignRole(AssignRoleRequest $request, User $user): JsonResponse { /* Assign role */ }
    public function removeRole(User $user): JsonResponse { /* Reset to customer */ }
}
```

#### B2. Create Form Request

**File:** `backend/app/Http/Requests/Admin/AssignRoleRequest.php`

```php
class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::Admin;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(UserRole::values())],
        ];
    }
}
```

#### B3. Create API Resources

**Files:**

- `backend/app/Http/Resources/Api/V1/RoleResource.php`
- `backend/app/Http/Resources/Api/V1/PermissionResource.php`
- `backend/app/Http/Resources/Api/V1/UserAdminResource.php`

#### B4. Update UserResource

Add `permissions` key to `UserResource::toArray()` — populated from `RoleService::getUserPermissions()`.

### Phase C — Route Restructuring

**Objective:** Apply role middleware to all existing routes and add admin route group.

#### C1. Restructure `routes/api.php`

Transform from single authenticated group to role-based groups:

```php
Route::prefix('v1')->group(function () {
    // Public auth routes (unchanged)
    // ...

    Route::middleware('auth:sanctum')->group(function () {
        // User profile routes (all authenticated users)
        // ...

        // Routes accessible to all authenticated roles
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show']);

        // Projects (all roles can view)
        Route::get('projects', [ProjectController::class, 'index']);
        Route::get('projects/{project}', [ProjectController::class, 'show']);

        // Customer routes
        Route::middleware('role:customer,admin')->group(function () {
            Route::post('projects', [ProjectController::class, 'store']);
            Route::apiResource('orders', OrderController::class);
        });

        // Contractor routes
        Route::middleware('role:contractor,admin')->group(function () {
            Route::put('projects/{project}', [ProjectController::class, 'update']);
            Route::post('projects/{project}/phases', [PhaseController::class, 'store']);
            Route::apiResource('projects.phases.tasks', TaskController::class)->only(['store', 'update']);
        });

        // ... more role-specific groups

        // Admin routes
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('roles', [RoleController::class, 'index']);
            Route::get('roles/{role}/permissions', [RoleController::class, 'permissions']);
            Route::get('users', [RoleController::class, 'users']);
            Route::post('users/{user}/role', [RoleController::class, 'assignRole']);
            Route::delete('users/{user}/role', [RoleController::class, 'removeRole']);

            // Admin CRUD on products
            Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);
        });
    });
});
```

#### C2. Add Error Code

**File:** `backend/app/Enums/ErrorCode.php`

Add `RBAC_PERMISSION_DENIED` error code alongside existing `RBAC_ROLE_DENIED`.

#### C3. Update Translation Files

**Files:**

- `backend/resources/lang/ar/errors.php`
- `backend/resources/lang/en/errors.php`

Add translations for `RBAC_PERMISSION_DENIED` and admin role management messages.

### Phase D — Seeder Updates

**Objective:** Add missing permissions and ensure seeders are complete.

#### D1. Update PermissionSeeder

Add new permissions: `user.view`, `user.update`, `role.assign`

#### D2. Update RolePermissionSeeder

Assign new permissions to admin role (admin already gets all via `sync()`).

#### D3. Update UserSeeder

Sync `role_user` pivot for seeded users so audit trail is populated from the start.

### Phase E — Backend Testing

**Objective:** Comprehensive test coverage for RBAC system.

#### E1. Unit Tests — `RoleServiceTest`

- `test_get_all_roles_returns_with_user_counts`
- `test_get_role_permissions_returns_correct_permissions`
- `test_get_user_permissions_returns_cached_permissions`
- `test_assign_role_updates_user_and_pivot`
- `test_assign_role_revokes_tokens`
- `test_assign_role_clears_permission_cache`
- `test_remove_role_resets_to_customer`
- `test_cannot_remove_last_admin`

#### E2. Feature Tests — Admin Endpoints

- RBAC matrix: admin can access, non-admin gets 403, unauthenticated gets 401
- Assign role: valid role, invalid role, non-existent user, last admin protection
- List roles: returns counts, correct format
- List users: pagination, role filter

#### E3. Feature Tests — Middleware

- `CheckRole`: authorized role passes, unauthorized role gets 403
- `CheckPermission`: authorized permission passes, unauthorized gets 403
- Combined middleware stack: `auth:sanctum` + `role:admin` + `permission:user.view`

### Phase F — Frontend Implementation

**Objective:** Permission composable, navigation updates, admin page, middleware wiring.

#### F1. Create `usePermission` Composable

**File:** `frontend/composables/usePermission.ts`

```typescript
export function usePermission() {
  const auth = useAuthStore();
  const permissions = computed(() => auth.permissions);

  function hasPermission(name: string): boolean {
    return permissions.value.includes(name);
  }

  function hasAnyPermission(names: string[]): boolean {
    return names.some((n) => permissions.value.includes(n));
  }

  function hasAllPermissions(names: string[]): boolean {
    return names.every((n) => permissions.value.includes(n));
  }

  return { permissions, hasPermission, hasAnyPermission, hasAllPermissions };
}
```

#### F2. Update Auth Store

**File:** `frontend/stores/auth.ts`

- Add `permissions: string[]` to state
- Populate from `UserResource.permissions` on login, register, and fetchUser
- Clear on logout

#### F3. Update Navigation

**File:** `frontend/config/navigation.ts`

- Add optional `permissions?: string[]` to `NavItem` interface
- Filter logic: show item if `roles` match AND `permissions` match (or are empty)

#### F4. Create Admin User Management Page

**File:** `frontend/pages/admin/users.vue`

- `definePageMeta({ middleware: ['auth', 'role'], roles: ['admin'] })`
- `UTable` with columns: name, email, role (Arabic label), status, joined date
- Role filter `USelect` dropdown
- "Assign Role" button opens `AssignRoleModal`
- Pagination

#### F5. Create AssignRoleModal Component

**File:** `frontend/components/admin/AssignRoleModal.vue`

- `UModal` with `USelect` for role selection
- Confirm button calls `POST /api/v1/admin/users/{user}/role`
- Toast notification on success/error

#### F6. Wire `definePageMeta` on All Pages

Update all existing page files to include appropriate middleware declarations.

#### F7. Update Role Middleware

**File:** `frontend/middleware/role.ts`

- Add toast notification when redirecting unauthorized users
- Import `useToast` from Nuxt UI

### Phase G — Frontend Testing

#### G1. Vitest Tests

- `usePermission` composable: `hasPermission`, `hasAnyPermission`, `hasAllPermissions`
- Role middleware: authorized access, unauthorized redirect, toast notification
- Auth store: permissions populated on login, cleared on logout

## Dependency Graph

```
A1 (Middleware) ─────────────┐
A2 (Gates) ──────────────────┤
A3 (Service/Repository) ─────┤
                              ├── C1 (Route Restructuring)
B1 (Admin Controller) ───────┤
B2 (Form Request) ───────────┤
B3 (API Resources) ──────────┤
B4 (UserResource Update) ────┤
                              ├── D1-D3 (Seeder Updates) ── E1-E3 (Backend Tests)
C2 (Error Code) ─────────────┘
C3 (Translations) ───────────────────────────────────────── E2-E3 (Backend Tests)

F1 (usePermission) ──────────┐
F2 (Auth Store Update) ──────┤
F3 (Navigation Update) ──────┤
                              ├── F4 (Admin Page) ── F5 (Modal) ── G1 (Frontend Tests)
F6 (Page Meta Wiring) ───────┤
F7 (Role Middleware Update) ──┘
```

## File Change Summary

### New Files (Backend)

| File                                                   | Purpose                     |
| ------------------------------------------------------ | --------------------------- |
| `app/Http/Middleware/CheckRole.php`                    | Role middleware             |
| `app/Http/Middleware/CheckPermission.php`              | Permission middleware       |
| `app/Services/RoleService.php`                         | RBAC business logic         |
| `app/Repositories/RoleRepository.php`                  | Role data access            |
| `app/Repositories/PermissionRepository.php`            | Permission data access      |
| `app/Http/Controllers/Api/V1/Admin/RoleController.php` | Admin role management       |
| `app/Http/Requests/Admin/AssignRoleRequest.php`        | Role assignment validation  |
| `app/Http/Resources/Api/V1/RoleResource.php`           | Role API resource           |
| `app/Http/Resources/Api/V1/PermissionResource.php`     | Permission API resource     |
| `app/Http/Resources/Api/V1/UserAdminResource.php`      | Admin user view resource    |
| `tests/Unit/Services/RoleServiceTest.php`              | Service unit tests          |
| `tests/Feature/Admin/RoleEndpointTest.php`             | Admin endpoint tests        |
| `tests/Feature/Middleware/CheckRoleTest.php`           | Role middleware tests       |
| `tests/Feature/Middleware/CheckPermissionTest.php`     | Permission middleware tests |

### Modified Files (Backend)

| File                                         | Change                                        |
| -------------------------------------------- | --------------------------------------------- |
| `bootstrap/app.php`                          | Register middleware aliases                   |
| `app/Providers/AppServiceProvider.php`       | Gate registration                             |
| `routes/api.php`                             | Route restructuring with role middleware      |
| `app/Enums/ErrorCode.php`                    | Add `RBAC_PERMISSION_DENIED`                  |
| `app/Http/Resources/Api/V1/UserResource.php` | Add `permissions` array                       |
| `database/seeders/PermissionSeeder.php`      | Add `user.view`, `user.update`, `role.assign` |
| `database/seeders/UserSeeder.php`            | Sync `role_user` pivot                        |
| `resources/lang/ar/errors.php`               | Add RBAC error translations                   |
| `resources/lang/en/errors.php`               | Add RBAC error translations                   |

### New Files (Frontend)

| File                                   | Purpose                        |
| -------------------------------------- | ------------------------------ |
| `composables/usePermission.ts`         | Permission checking composable |
| `pages/admin/users.vue`                | Admin user management page     |
| `components/admin/AssignRoleModal.vue` | Role assignment modal          |

### Modified Files (Frontend)

| File                      | Change                                       |
| ------------------------- | -------------------------------------------- |
| `stores/auth.ts`          | Add `permissions` array to state             |
| `composables/useAuth.ts`  | Expose permission helpers                    |
| `config/navigation.ts`    | Add `permissions` field, filtering logic     |
| `middleware/role.ts`      | Add toast notification                       |
| Multiple `pages/**/*.vue` | Add `definePageMeta` middleware declarations |
