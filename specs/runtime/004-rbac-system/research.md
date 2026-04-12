# Research — RBAC System

## Existing Codebase Analysis

### Current Authorization Architecture

The codebase has a **dual-role system** that is partially wired:

1. **`users.role` column** (string, cast to `UserRole` enum) — the primary role source used by all existing Policies and controllers
2. **`roles` + `permissions` + `role_permissions` + `role_user` tables** — fully migrated and seeded, but NOT connected to any runtime authorization

### Existing Components to Leverage

| Component                     | Status | Notes                                               |
| ----------------------------- | ------ | --------------------------------------------------- |
| `UserRole` enum               | Ready  | 5 roles with Arabic labels                          |
| `Role` model                  | Ready  | `name`, `description`, `permissions()` relationship |
| `Permission` model            | Ready  | `name`, `description`, `roles()` relationship       |
| `RolePermissionSeeder`        | Ready  | Full permission matrix seeded                       |
| `PermissionSeeder`            | Ready  | 26 permissions across 7 entities                    |
| `BaseRepository`              | Ready  | Abstract base with CRUD, pagination                 |
| `BaseController`              | Ready  | `ApiResponse` trait, `forbidden()` method           |
| `ErrorCode::RBAC_ROLE_DENIED` | Ready  | Error code with i18n messages                       |
| `ApiExceptionRenderer`        | Ready  | Maps `AuthorizationException` to 403                |
| Frontend `useAuth`            | Ready  | `hasRole()` method                                  |
| Frontend `role.ts` middleware | Ready  | Checks `meta.roles`                                 |
| Frontend `navigation.ts`      | Ready  | `roles: UserRole[]` per nav item                    |

### Gaps to Fill

| Gap                                          | Resolution                                         |
| -------------------------------------------- | -------------------------------------------------- |
| No `CheckRole` middleware                    | Create in `app/Http/Middleware/`                   |
| No `CheckPermission` middleware              | Create in `app/Http/Middleware/`                   |
| No middleware aliases in `bootstrap/app.php` | Register `role` and `permission` aliases           |
| No Gate definitions                          | Register in `AppServiceProvider::boot()`           |
| No `RoleService`                             | Create with role assignment, permission resolution |
| No `RoleRepository` / `PermissionRepository` | Create extending `BaseRepository`                  |
| No admin role management endpoints           | Create `Admin\RoleController`                      |
| `UserResource` missing `permissions`         | Add permissions array                              |
| No `usePermission` composable                | Create for frontend permission checks              |
| No admin user management page                | Create `pages/admin/users.vue`                     |
| Route middleware not applied                 | Restructure `routes/api.php`                       |
| `role_user` pivot not populated for users    | Sync via `RoleService`                             |

## Laravel Authorization Patterns

### Gate Registration Strategy

```php
// AppServiceProvider::boot()
Gate::before(function (User $user, string $ability) {
    if ($user->role === UserRole::Admin) {
        return true; // Admin superuser bypass
    }
});

// Dynamically register gates from permissions table
$permissions = Cache::rememberForever('permissions', fn () => Permission::pluck('name'));
foreach ($permissions as $permission) {
    Gate::define($permission, function (User $user) use ($permission) {
        return in_array($permission, $this->getUserPermissions($user));
    });
}
```

### Per-Request Permission Caching

```php
// RoleService::getUserPermissions()
return Cache::remember(
    "user:{$user->id}:permissions",
    null, // No TTL — invalidated explicitly
    fn () => Permission::query()
        ->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
        ->join('roles', 'roles.id', '=', 'role_permissions.role_id')
        ->where('roles.name', $user->role->value)
        ->pluck('permissions.name')
        ->toArray()
);
```

## Route Middleware Strategy

### Role-Based Route Grouping

Current: All authenticated routes in single `auth:sanctum` group.
Target: Nested groups with explicit role middleware.

```
auth:sanctum (all authenticated)
├── Public resources (products index/show) — no role restriction
├── role:customer,contractor,supervising_architect,field_engineer,admin
│   ├── projects (read)
│   ├── reports (read)
│   └── transactions (read)
├── role:customer
│   ├── orders (full CRUD)
│   └── projects (create)
├── role:contractor
│   ├── phases (create, update)
│   └── tasks (create, update)
├── role:field_engineer
│   └── reports (create, update)
└── role:admin (admin prefix)
    ├── roles management
    └── users management
```

## Redis Permission Cache Design

- **Key format:** `user:{id}:permissions`
- **Value:** JSON array of permission name strings
- **Invalidation:** Explicit `Cache::forget()` on role change
- **Warming:** First permission check after cache miss triggers DB query
- **Admin bypass:** `Gate::before()` short-circuits before cache lookup

## Frontend Permission Integration

### API Response Enhancement

```json
{
  "id": 1,
  "name": "أحمد",
  "email": "ahmed@example.com",
  "role": "customer",
  "permissions": [
    "project.view",
    "project.create",
    "order.view",
    "order.create",
    "transaction.view",
    "report.view"
  ],
  "phone": "+966...",
  "active": true
}
```

### Permission Composable Design

```typescript
// composables/usePermission.ts
export function usePermission() {
  const auth = useAuthStore();
  const permissions = computed(() => auth.permissions);

  function hasPermission(name: string): boolean {
    return permissions.value.includes(name);
  }

  function hasAnyPermission(names: string[]): boolean {
    return names.some((n) => permissions.value.includes(n));
  }

  return { permissions, hasPermission, hasAnyPermission };
}
```
