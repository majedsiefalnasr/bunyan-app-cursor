# STAGE_02 Testing Guide — Database Schema Foundation

**Status:** COMPLETE
**Date:** 2026-04-11
**Scope:** Backend only (PHP Enums, BaseModel, BaseRepository, Migrations, Seeders)

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Running All Tests](#running-all-tests)
3. [Unit Tests](#unit-tests)
4. [Feature Tests](#feature-tests)
5. [Static Analysis](#static-analysis)
6. [Code Style](#code-style)
7. [Manual Verification](#manual-verification)
8. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Ensure Stage 01 is merged into `develop` and your environment is configured:

```bash
# From project root
cp backend/.env.testing.example backend/.env.testing  # if not already present

# Install backend dependencies
cd backend && composer install

# Run migrations on test database
php artisan migrate --env=testing
```

`.env.testing` must configure SQLite in-memory or a dedicated test MySQL DB:

```ini
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

---

## Running All Tests

```bash
cd backend

# Full test suite
php artisan test

# With verbose output
php artisan test --verbose

# Specific test suite only
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

Expected result: **31 tests, 278 assertions, 0 failures**

---

## Unit Tests

### Enum Tests (`tests/Unit/Enums/`)

Covers all 10 PHP enums: case existence, backing values, Arabic labels, `values()`, `from()`, `tryFrom()`.

```bash
php artisan test --filter "UserRoleTest|ProjectStatusTest|PhaseStatusTest|TaskStatusTest|OtherEnumsTest"
```

| Test file | Cases tested |
|---|---|
| `UserRoleTest` | 5 cases, Arabic labels, values(), from(), tryFrom() |
| `ProjectStatusTest` | 5 cases, Arabic labels, values(), from(), tryFrom() |
| `PhaseStatusTest` | 5 cases, Arabic labels, values(), from(), tryFrom() |
| `TaskStatusTest` | 5 cases, Arabic labels, values(), from(), tryFrom() |
| `OtherEnumsTest` | OrderStatus, TransactionType, TransactionStatus, WorkflowType, ApprovalStatus, ReportType |

**Expected:** 31 unit tests pass

### Repository Tests (`tests/Unit/Repositories/`)

Tests `BaseRepository` generic CRUD via `UserRepository` as a concrete implementation.

```bash
php artisan test --filter "BaseRepositoryTest"
```

| Test | Assertion |
|---|---|
| `test_find_by_id_returns_model_when_found` | Returns correct model |
| `test_find_by_id_returns_null_when_not_found` | Returns null for missing ID |
| `test_find_by_id_or_fail_throws_when_not_found` | Throws `ModelNotFoundException` |
| `test_create_persists_model` | Record exists in DB |
| `test_update_persists_changes` | DB record updated |
| `test_delete_soft_deletes_model` | `deleted_at` set |
| `test_restore_recovers_soft_deleted_model` | `deleted_at` null after restore |
| `test_all_returns_paginated_results` | Correct `perPage` count |

---

## Feature Tests

### Database Schema (`tests/Feature/Database/`)

```bash
php artisan test --filter "DatabaseSchemaTest|EnumCastTest|SeederTest|SoftDeleteTest|MigrationRollbackTest"
```

#### DatabaseSchemaTest

Verifies all expected tables and columns exist after `migrate`:

```bash
php artisan test tests/Feature/Database/DatabaseSchemaTest.php
```

Tables checked: `users`, `roles`, `permissions`, `role_permissions`, `role_user`, `projects`, `phases`, `tasks`, `reports`, `orders`, `order_items`, `products`, `transactions`, `workflow_configurations`, `approval_rules`

#### EnumCastTest

Verifies Eloquent enum casting round-trips correctly:

```bash
php artisan test tests/Feature/Database/EnumCastTest.php
```

- `User.role` casts to `UserRole` enum
- `Project.status` casts to `ProjectStatus` enum
- `Phase.status` casts to `PhaseStatus` enum
- `Task.status` casts to `TaskStatus` enum
- Factory states return correct enum values

#### SeederTest

```bash
php artisan test tests/Feature/Database/SeederTest.php
```

- 5 roles seeded (`customer`, `contractor`, `supervising_architect`, `field_engineer`, `admin`)
- Permissions seeded
- Admin role has all permissions
- Customer has correct limited permissions
- Field engineer has limited permissions

#### SoftDeleteTest

```bash
php artisan test tests/Feature/Database/SoftDeleteTest.php
```

- User, Project, Phase, Task all soft-delete correctly
- Records excluded from default queries after soft delete
- `withTrashed()` finds soft-deleted records
- Restore clears `deleted_at`

#### MigrationRollbackTest

```bash
php artisan test tests/Feature/Database/MigrationRollbackTest.php
```

- All tables exist after `migrate`
- `migrate --pretend` runs without error

---

## Static Analysis

### PHPStan (Level 5 with Larastan)

```bash
cd backend
vendor/bin/phpstan analyse --memory-limit=512M
```

**Expected:** `[OK] No errors`

If errors appear:
- Check `phpstan.neon` for existing `ignoreErrors` patterns
- Eloquent scope methods on `Builder` are suppressed by pattern — this is intentional
- `@phpstan-ignore-next-line` is used on `$model->restore()` in `BaseRepository`

---

## Code Style

### Laravel Pint

```bash
cd backend

# Check only (no changes)
vendor/bin/pint --test

# Auto-fix
vendor/bin/pint
```

### PHP CS Fixer

```bash
cd backend

# Dry run (what would change)
vendor/bin/php-cs-fixer fix --dry-run --diff

# Apply fixes
vendor/bin/php-cs-fixer fix
```

**Key style rules enforced by this stage:**

| Rule | Applies to |
|---|---|
| `ordered_imports` | All PHP files — alphabetical by full namespace |
| `class_attributes_separation` | Blank line required between `use Trait;` and first property |
| `new_with_parentheses` (Laravel preset: `named_class: false`) | `new Foo()` → `new Foo` when no args |

---

## Manual Verification

### 1. Verify Enums Work at Runtime

```bash
cd backend && php artisan tinker
```

```php
// Enum instantiation
App\Enums\UserRole::Admin;                   // => App\Enums\UserRole {name: "Admin", value: "admin"}
App\Enums\UserRole::Admin->label();          // => "الإدارة"
App\Enums\UserRole::values();                // => ["customer", "contractor", ...]
App\Enums\UserRole::from('admin');           // => App\Enums\UserRole::Admin
App\Enums\UserRole::tryFrom('invalid');      // => null
```

### 2. Verify Enum Casting on Models

```php
$user = App\Models\User::factory()->create(['role' => 'admin']);
$user->role;                  // => App\Enums\UserRole::Admin (not string)
$user->role instanceof App\Enums\UserRole;  // => true
```

### 3. Verify BaseRepository Works

```php
$repo = new App\Repositories\UserRepository;
$user = $repo->create([
    'name' => 'Test',
    'email' => 'test@test.com',
    'password' => bcrypt('pass'),
    'role' => 'customer',
]);
$repo->findById($user->id);       // => User model
$repo->delete($user);
$repo->restore($user->id);       // => User model, deleted_at is null
```

### 4. Verify role_user Pivot Migration

```bash
php artisan migrate:status | grep role_user
# Should show: Ran (with timestamp)
```

```php
// Tinker
$user = App\Models\User::first();
$role = App\Models\Role::where('name', 'admin')->first();
$user->roles()->attach($role->id, ['assigned_at' => now()]);
$user->roles()->count();   // => 1
```

### 5. Verify Seeder is Idempotent

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=RolePermissionSeeder  # Run twice — no duplicates
```

### 6. Verify Rollback Safety

```bash
php artisan migrate:rollback --step=1
# Drops role_user table

php artisan migrate
# Re-creates role_user table
```

---

## Troubleshooting

### `no such column: roles.deleted_at`

**Cause:** `Role` model should NOT use `SoftDeletes` — the `roles` table has no `deleted_at` column.

**Fix:** Verify `Role.php` does not use `SoftDeletes`. Only these 7 models use it: `User`, `Project`, `Phase`, `Task`, `Report`, `Order`, `Product`.

### `"draft" is not a valid backing value for enum App\Enums\ProjectStatus`

**Cause:** Old Stage 01 code was setting `'status' => 'draft'`. `ProjectStatus` has no `Draft` case.

**Fix:** `ProjectController::store()` must use `ProjectStatus::Pending->value`.

### `Strict comparison using === between UserRole and 'admin'`

**Cause:** Existing Stage 01 code comparing `$user->role` (now a `UserRole` enum) against a string literal.

**Fix:** Replace `$user->role === 'admin'` with `$user->role === UserRole::Admin` in all Policies, Form Requests, and Controllers.

### PDO `MYSQL_ATTR_SSL_CA` Deprecation Notices

**Cause:** PHP 8.3+ deprecates this constant. Shown as `DEPR` in test output.

**Impact:** None — these are deprecation notices from vendor Laravel config, not test failures. Tests still pass.

---

## Full Validation Command

```bash
cd backend && vendor/bin/phpstan analyse --memory-limit=512M && vendor/bin/pint --test && php artisan test
```

Expected output:
```
[OK] No errors          ← PHPStan
{"result":"pass"}       ← Pint
Tests: 31 passed        ← PHPUnit
```
