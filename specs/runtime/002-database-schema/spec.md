# Spec: Database Schema Foundation

**Stage:** STAGE_02_DATABASE_SCHEMA
**Phase:** 01_PLATFORM_FOUNDATION
**Branch:** `spec/002-database-schema`
**Status:** DRAFT
**Depends On:** STAGE_01_PROJECT_INITIALIZATION (PRODUCTION READY)

---

## 1. Overview

Stage 02 establishes the architectural foundation layer on top of Stage 01's scaffold. Stage 01 produced a working application skeleton with raw models, migrations, and repositories. Stage 02 formalizes and hardens that foundation by introducing:

- PHP Enums for all domain value types (replacing raw strings)
- A `BaseModel` abstract class with shared traits
- A `BaseRepository` abstract contract and base implementation
- A `role_user` pivot migration for future multi-role capability
- Enum-based casts integrated into all Eloquent models
- Factory states for all 5 user roles
- A `RolePermissionSeeder` assigning permissions to roles
- Complete DatabaseSeeder with correct ordering
- Unit tests for enums, models, and repositories
- Feature tests for database integrity and schema constraints

Stage 02 **does not** introduce HTTP controllers, API endpoints, or auth logic — those belong to STAGE_03 and STAGE_04.

---

## 2. Context: Stage 01 Deliverables (Already In Codebase)

The following already exist and must NOT be modified or replaced:

### 2.1 Existing Migrations
| Migration | Table | Status |
|---|---|---|
| `2026_04_10_174655` | `personal_access_tokens` | Exists |
| `2026_04_10_174656` | `users` | Exists — has `role` string column |
| `2026_04_10_174657` | `roles` | Exists |
| `2026_04_10_174658` | `permissions` | Exists |
| `2026_04_10_174659` | `role_permissions` | Exists (pivot) |
| `2026_04_10_174700` | `projects` | Exists |
| `2026_04_10_174701` | `phases` | Exists |
| `2026_04_10_174702` | `tasks` | Exists |
| `2026_04_10_174703` | `workflow_configurations` | Exists |
| `2026_04_10_174704` | `approval_rules` | Exists |
| `2026_04_10_174705` | `reports` | Exists |
| `2026_04_10_174707` | `products` | Exists |
| `2026_04_10_174708` | `orders` | Exists |
| `2026_04_10_174709` | `order_items` | Exists |
| `2026_04_10_174710` | `transactions` | Exists |
| `2026_04_10_174711` | report title/content add | Exists |

### 2.2 Existing Models
User, Role, Permission, Project, Phase, Task, Report, ApprovalRule, WorkflowConfiguration, Order, OrderItem, Product, Transaction

### 2.3 Existing Repositories
UserRepository, ProjectRepository, PhaseRepository, TaskRepository, ReportRepository, ApprovalRuleRepository, WorkflowConfigurationRepository, OrderRepository, ProductRepository, TransactionRepository

### 2.4 Existing Seeders
DatabaseSeeder, RoleSeeder, PermissionSeeder, UserSeeder, ProductSeeder

### 2.5 Existing Factories
UserFactory, ProjectFactory, PhaseFactory, TaskFactory, ReportFactory, ProductFactory, OrderFactory, TransactionFactory

---

## 3. Scope: Stage 02 Deliverables

### 3.1 PHP Enums (`backend/app/Enums/`)

| Enum | File | Values |
|---|---|---|
| `UserRole` | `UserRole.php` | customer, contractor, supervising_architect, field_engineer, admin |
| `ProjectStatus` | `ProjectStatus.php` | pending, active, on_hold, completed, cancelled |
| `PhaseStatus` | `PhaseStatus.php` | pending, in_progress, completed, approved, rejected |
| `TaskStatus` | `TaskStatus.php` | pending, in_progress, completed, approved, rejected |
| `OrderStatus` | `OrderStatus.php` | pending, processing, shipped, delivered, cancelled, refunded |
| `TransactionType` | `TransactionType.php` | payment, withdrawal, refund, commission |
| `TransactionStatus` | `TransactionStatus.php` | pending, completed, failed, cancelled |
| `WorkflowType` | `WorkflowType.php` | project, phase, task |
| `ApprovalStatus` | `ApprovalStatus.php` | pending, approved, rejected |
| `ReportType` | `ReportType.php` | progress, inspection, incident, completion |

Each enum must:
- Implement `BackedEnum` (string-backed)
- Have an `label(): string` method returning Arabic text
- Have a `values(): array` static method
- Have a `from()` and `tryFrom()` (inherited from native PHP enum)

### 3.2 BaseModel (`backend/app/Models/BaseModel.php`)

Abstract Eloquent model providing:
- `SoftDeletes` trait (all main entities must soft-delete)
- Standardized `$casts` baseline
- `scopeActive()` — filter non-deleted, active records
- `scopeOrdered()` — order by `created_at` desc by default

All existing models must extend `BaseModel` instead of `Model` where applicable:
- User (extends `Authenticatable`, so BaseModel traits applied via trait composition)
- Project, Phase, Task, Report, ApprovalRule, WorkflowConfiguration, Order, OrderItem, Product, Transaction

### 3.3 BaseRepository (`backend/app/Repositories/BaseRepository.php`)

Abstract base class providing:
```
findById(int $id): ?Model
findByIdOrFail(int $id): Model
all(array $filters = []): LengthAwarePaginator
create(array $data): Model
update(Model $model, array $data): Model
delete(Model $model): bool
restore(int $id): Model
paginate(Builder $query, int $perPage = 15): LengthAwarePaginator
```

All existing repositories must extend `BaseRepository`.

### 3.4 New Migration: `role_user` Pivot

Add a forward-only migration to support future multi-role assignment. The existing `role` column on `users` stays as the **primary role** (single role for RBAC checks), while `role_user` supports auxiliary role assignments:

```
Migration: 2026_04_11_120000_create_role_user_table.php
Table: role_user
Columns:
  - user_id (FK → users)
  - role_id (FK → roles)
  - assigned_at (timestamp, nullable)
  - assigned_by (FK → users, nullable)
Unique: (user_id, role_id)
```

### 3.5 Enum Integration Into Models

Update model `$casts` to use PHP enums:

| Model | Column | Enum Cast |
|---|---|---|
| User | `role` | `UserRole` |
| Project | `status` | `ProjectStatus` |
| Phase | `status` | `PhaseStatus` |
| Task | `status` | `TaskStatus` |
| Order | `status` | `OrderStatus` |
| Transaction | `type` | `TransactionType` |
| Transaction | `status` | `TransactionStatus` |
| WorkflowConfiguration | `type` | `WorkflowType` |
| ApprovalRule | `status` | `ApprovalStatus` |
| Report | `type` | `ReportType` |

### 3.6 Factory States

Enhance `UserFactory` with role states:
- `->customer()` state
- `->contractor()` state
- `->supervisingArchitect()` state
- `->fieldEngineer()` state
- `->admin()` state
- `->inactive()` state

Enhance `ProjectFactory` with status states:
- `->pending()`, `->active()`, `->completed()`, `->cancelled()`

Enhance `PhaseFactory` with status states.
Enhance `TaskFactory` with status states.

### 3.7 RolePermissionSeeder

New seeder: `backend/database/seeders/RolePermissionSeeder.php`

Assigns permissions to roles:

| Role | Permissions |
|---|---|
| customer | project.view, project.create, order.view, order.create, transaction.view, report.view |
| contractor | project.view, project.update, phase.view, phase.create, phase.update, task.view, task.create, task.update, report.view, report.create, transaction.view |
| supervising_architect | project.view, project.approve, phase.view, phase.update, task.view, task.update, report.view, report.create |
| field_engineer | task.view, task.update, report.view, report.create, report.update |
| admin | ALL permissions |

### 3.8 DatabaseSeeder Update

Update `DatabaseSeeder.php` to call all seeders in correct dependency order:
1. RoleSeeder
2. PermissionSeeder
3. RolePermissionSeeder
4. UserSeeder
5. ProductSeeder

### 3.9 Tests

#### Unit Tests (`backend/tests/Unit/`)
- `Enums/UserRoleTest.php` — label(), values(), from(), tryFrom()
- `Enums/ProjectStatusTest.php` — label(), values()
- `Enums/PhaseStatusTest.php` — label(), values()
- `Enums/TaskStatusTest.php` — label(), values()
- `Repositories/BaseRepositoryTest.php` — findById, findByIdOrFail, create, update, delete

#### Feature Tests (`backend/tests/Feature/Database/`)
- `DatabaseSchemaTest.php` — all tables exist with correct columns
- `MigrationRollbackTest.php` — all migrations roll back cleanly
- `SeederTest.php` — seeders run without errors, data is correct
- `SoftDeleteTest.php` — soft delete works on User, Project, Phase, Task
- `EnumCastTest.php` — models correctly cast string values to enums

---

## 4. Architecture Decisions

### 4.1 Single Role vs Multi-Role

**Decision:** Retain the `role` string column on `users` as the **authoritative primary role** for all RBAC checks (performance, simplicity). Add `role_user` pivot for potential future multi-role scenarios (not used in RBAC logic until STAGE_04 explicitly enables it).

**Rationale:** The Bunyan domain model has strict role separation. A user is fundamentally one role type. The pivot table is additive/non-breaking.

### 4.2 String-Backed Enums

**Decision:** All enums are string-backed (not int-backed) for database readability and API clarity.

**Rationale:** MySQL stores readable strings, debug queries are self-documenting, API serialization requires no mapping.

### 4.3 BaseModel Pattern

**Decision:** `BaseModel extends Model` (not Eloquent `Model` directly in concrete classes). `User` extends `Authenticatable` which extends `Model`, so User gets traits via `use BaseModelTrait` composition pattern.

**Rationale:** Laravel's `User` model must extend `Authenticatable` for Sanctum. A trait `HasBaseModelBehavior` is used for User instead of direct inheritance.

---

## 5. Out of Scope

- Authentication endpoints (STAGE_03)
- RBAC middleware and policies (STAGE_04)
- Frontend components
- API controllers or routes
- Queue jobs or events

---

## Clarifications

### Session 2026-04-11

**Q1: Should `role_user` pivot replace the `role` column on users?**
A: No. The `role` column on users is retained as the primary role for RBAC. The `role_user` pivot is additive for future multi-role scenarios. STAGE_04 (RBAC) will define whether to use the pivot for authorization.

**Q2: Should existing models be modified or extended?**
A: Existing model files are modified in-place (PHP class body updated). This is not a migration change — it is application code evolution permitted between stages.

**Q3: What is the `status` column type for existing tables?**
A: All existing `status` columns are `string` type (VARCHAR). Adding enum casts in Laravel models does not require a migration — it's application-level deserialization. No migration needed for enum casting.

**Q4: Are factories missing for any entities?**
A: `PhaseFactory` already exists. The following need state additions: UserFactory (role states), ProjectFactory (status states), PhaseFactory (status states), TaskFactory (status states).
