# Data Model — RBAC System

## Existing Tables (No Migration Needed)

### `users`

| Column            | Type                 | Notes                                             |
| ----------------- | -------------------- | ------------------------------------------------- |
| id                | bigint unsigned PK   | Auto-increment                                    |
| name              | varchar(255)         | Required                                          |
| email             | varchar(255) UNIQUE  | Required                                          |
| password          | varchar(255)         | Hashed                                            |
| **role**          | varchar(255)         | **Primary role source** — cast to `UserRole` enum |
| phone             | varchar(20) NULL     | Optional                                          |
| active            | boolean DEFAULT true | Account status                                    |
| email_verified_at | timestamp NULL       | Verification status                               |
| remember_token    | varchar(100) NULL    | Laravel default                                   |
| created_at        | timestamp            | Auto                                              |
| updated_at        | timestamp            | Auto                                              |
| deleted_at        | timestamp NULL       | Soft delete                                       |

### `roles`

| Column      | Type                | Notes                                            |
| ----------- | ------------------- | ------------------------------------------------ |
| id          | bigint unsigned PK  | Auto-increment                                   |
| name        | varchar(255) UNIQUE | Role identifier (matches `UserRole` enum values) |
| description | varchar(255) NULL   | Human-readable description                       |
| created_at  | timestamp           | Auto                                             |
| updated_at  | timestamp           | Auto                                             |

**Seeded values:** `customer`, `contractor`, `supervising_architect`, `field_engineer`, `admin`

### `permissions`

| Column      | Type                | Notes                                   |
| ----------- | ------------------- | --------------------------------------- |
| id          | bigint unsigned PK  | Auto-increment                          |
| name        | varchar(255) UNIQUE | Permission identifier (`entity.action`) |
| description | varchar(255) NULL   | Human-readable description              |
| created_at  | timestamp           | Auto                                    |
| updated_at  | timestamp           | Auto                                    |

**Seeded values (26 permissions):**

```
project.view, project.create, project.update, project.delete, project.approve
phase.view, phase.create, phase.update, phase.delete
task.view, task.create, task.update, task.delete
report.view, report.create, report.update, report.delete
transaction.view, transaction.create, transaction.update
product.view, product.create, product.update
order.view, order.create, order.update, order.delete
```

### `role_permissions` (pivot)

| Column        | Type                                | Notes             |
| ------------- | ----------------------------------- | ----------------- |
| id            | bigint unsigned PK                  | Auto-increment    |
| role_id       | bigint unsigned FK → roles.id       | CASCADE on delete |
| permission_id | bigint unsigned FK → permissions.id | CASCADE on delete |
| created_at    | timestamp                           | Auto              |
| updated_at    | timestamp                           | Auto              |

### `role_user` (pivot — audit trail)

| Column      | Type                               | Notes                      |
| ----------- | ---------------------------------- | -------------------------- |
| id          | bigint unsigned PK                 | Auto-increment             |
| user_id     | bigint unsigned FK → users.id      | CASCADE on delete          |
| role_id     | bigint unsigned FK → roles.id      | CASCADE on delete          |
| assigned_by | bigint unsigned FK → users.id NULL | Who assigned the role      |
| assigned_at | timestamp NULL                     | When the role was assigned |
| created_at  | timestamp                          | Auto                       |
| updated_at  | timestamp                          | Auto                       |

## New Permissions to Add

The existing `PermissionSeeder` is missing some permissions referenced in the spec:

| Permission    | Description           | Notes      |
| ------------- | --------------------- | ---------- |
| `user.view`   | View user list        | Admin only |
| `user.update` | Update user profiles  | Admin only |
| `role.assign` | Assign roles to users | Admin only |

**Action:** Update `PermissionSeeder` to include these 3 new permissions. Update `RolePermissionSeeder` to assign them to admin role.

## Relationships (Eloquent)

```
User
├── role (UserRole enum column) ← PRIMARY SOURCE
├── roles() → belongsToMany(Role::class, 'role_user') ← AUDIT TRAIL
└── (permissions resolved via role → role_permissions → permissions)

Role
├── permissions() → belongsToMany(Permission::class, 'role_permissions')
└── users() → belongsToMany(User::class, 'role_user')

Permission
└── roles() → belongsToMany(Role::class, 'role_permissions')
```

## Permission Resolution Flow

```
1. User authenticates via Sanctum token
2. Request hits CheckRole middleware → checks users.role column
3. Request hits CheckPermission middleware (if applied)
   a. Check Redis cache: user:{id}:permissions
   b. On miss: query DB via role → role_permissions → permissions
   c. Store result in Redis (no TTL)
   d. Check if requested permission is in the set
4. Gate::before() → admin gets immediate PASS
5. Gate::define() → checks permission set from cache/DB
```

## Cache Schema

| Key Pattern             | Value Type                         | Invalidation                |
| ----------------------- | ---------------------------------- | --------------------------- |
| `user:{id}:permissions` | JSON array of permission strings   | Explicit on role change     |
| `permissions`           | Collection of all permission names | Application-level (forever) |
