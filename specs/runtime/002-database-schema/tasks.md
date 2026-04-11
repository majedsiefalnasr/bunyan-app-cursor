# Tasks — Database Schema Foundation

**Total Tasks:** 72
**Completed:** 0

---

## Phase A — PHP Enums [parallel-safe]

- [ ] T001 [P] Create `backend/app/Enums/UserRole.php` — string-backed enum with 5 cases, `label()` Arabic method, `values()` static method
- [ ] T002 [P] Create `backend/app/Enums/ProjectStatus.php` — string-backed enum: pending, active, on_hold, completed, cancelled with Arabic labels
- [ ] T003 [P] Create `backend/app/Enums/PhaseStatus.php` — string-backed enum: pending, in_progress, completed, approved, rejected with Arabic labels
- [ ] T004 [P] Create `backend/app/Enums/TaskStatus.php` — string-backed enum: pending, in_progress, completed, approved, rejected with Arabic labels
- [ ] T005 [P] Create `backend/app/Enums/OrderStatus.php` — string-backed enum: pending, processing, shipped, delivered, cancelled, refunded with Arabic labels
- [ ] T006 [P] Create `backend/app/Enums/TransactionType.php` — string-backed enum: payment, withdrawal, refund, commission with Arabic labels
- [ ] T007 [P] Create `backend/app/Enums/TransactionStatus.php` — string-backed enum: pending, completed, failed, cancelled with Arabic labels
- [ ] T008 [P] Create `backend/app/Enums/WorkflowType.php` — string-backed enum: project, phase, task with Arabic labels
- [ ] T009 [P] Create `backend/app/Enums/ApprovalStatus.php` — string-backed enum: pending, approved, rejected with Arabic labels
- [ ] T010 [P] Create `backend/app/Enums/ReportType.php` — string-backed enum: progress, inspection, incident, completion with Arabic labels

---

## Phase B — BaseModel + Trait

- [ ] T011 Create `backend/app/Models/Concerns/HasBaseModelBehavior.php` — trait with `SoftDeletes`, `scopeActive(Builder $query): Builder`, `scopeOrdered(Builder $query, string $column, string $direction): Builder`
- [ ] T012 Create `backend/app/Models/BaseModel.php` — abstract class extending `Model`, using `HasFactory`, `SoftDeletes`, defining `scopeActive()`, `scopeOrdered()`

---

## Phase C — BaseRepository

- [ ] T013 Create `backend/app/Repositories/BaseRepository.php` — abstract class with `abstract protected function model(): string`, `findById()`, `findByIdOrFail()`, `all()`, `create()`, `update()`, `delete()`, `restore()`, `newQuery()`, `paginate()`

---

## Phase D — Model Updates (depends on T001–T012)

- [ ] T014 Update `backend/app/Models/Project.php` — extend `BaseModel` instead of `Model`, add `status` cast to `ProjectStatus`
- [ ] T015 Update `backend/app/Models/Phase.php` — extend `BaseModel`, add `status` cast to `PhaseStatus`
- [ ] T016 Update `backend/app/Models/Task.php` — extend `BaseModel`, add `status` cast to `TaskStatus`
- [ ] T017 Update `backend/app/Models/Report.php` — extend `BaseModel`, add `type` cast to `ReportType`
- [ ] T018 Update `backend/app/Models/Order.php` — extend `BaseModel`, add `status` cast to `OrderStatus`
- [ ] T019 Update `backend/app/Models/OrderItem.php` — extend `BaseModel`
- [ ] T020 Update `backend/app/Models/Transaction.php` — extend `BaseModel`, add `type` cast to `TransactionType`, `status` cast to `TransactionStatus`
- [ ] T021 Update `backend/app/Models/WorkflowConfiguration.php` — extend `BaseModel`, add `type` cast to `WorkflowType`
- [ ] T022 Update `backend/app/Models/ApprovalRule.php` — extend `BaseModel`, add `status` cast to `ApprovalStatus`
- [ ] T023 Update `backend/app/Models/Role.php` — extend `BaseModel`
- [ ] T024 Update `backend/app/Models/Permission.php` — extend `BaseModel`
- [ ] T025 Update `backend/app/Models/Product.php` — extend `BaseModel`
- [ ] T026 Update `backend/app/Models/User.php` — add `use HasBaseModelBehavior`, add `role` cast to `UserRole`, add `role_user()` BelongsToMany relationship

---

## Phase E — Repository Updates (depends on T013)

- [ ] T027 Update `backend/app/Repositories/UserRepository.php` — extend `BaseRepository`, implement `model()` returning `User::class`
- [ ] T028 Update `backend/app/Repositories/ProjectRepository.php` — extend `BaseRepository`, implement `model()`
- [ ] T029 Update `backend/app/Repositories/PhaseRepository.php` — extend `BaseRepository`, implement `model()`
- [ ] T030 Update `backend/app/Repositories/TaskRepository.php` — extend `BaseRepository`, implement `model()`
- [ ] T031 Update `backend/app/Repositories/ReportRepository.php` — extend `BaseRepository`, implement `model()`
- [ ] T032 Update `backend/app/Repositories/ApprovalRuleRepository.php` — extend `BaseRepository`, implement `model()`
- [ ] T033 Update `backend/app/Repositories/WorkflowConfigurationRepository.php` — extend `BaseRepository`, implement `model()`
- [ ] T034 Update `backend/app/Repositories/OrderRepository.php` — extend `BaseRepository`, implement `model()`
- [ ] T035 Update `backend/app/Repositories/ProductRepository.php` — extend `BaseRepository`, implement `model()`
- [ ] T036 Update `backend/app/Repositories/TransactionRepository.php` — extend `BaseRepository`, implement `model()`

---

## Phase F — Migration (forward-only, additive)

- [ ] T037 Create `backend/database/migrations/2026_04_11_120000_create_role_user_table.php` — pivot table with user_id FK, role_id FK, assigned_by FK (nullable), assigned_at (nullable), unique(user_id, role_id), indexes, `down()` drops table
- [ ] T038 Validate migration with `cd backend && php artisan migrate --pretend` — must report no errors

---

## Phase G — Factory Updates (depends on T001–T010)

- [ ] T039 Update `backend/database/factories/UserFactory.php` — add `customer()`, `contractor()`, `supervisingArchitect()`, `fieldEngineer()`, `admin()`, `inactive()` states using `UserRole` enum backing values
- [ ] T040 Update `backend/database/factories/ProjectFactory.php` — add `pending()`, `active()`, `onHold()`, `completed()`, `cancelled()` states using `ProjectStatus` enum
- [ ] T041 Update `backend/database/factories/PhaseFactory.php` — add `pending()`, `inProgress()`, `completed()`, `approved()`, `rejected()` states using `PhaseStatus` enum
- [ ] T042 Update `backend/database/factories/TaskFactory.php` — add `pending()`, `inProgress()`, `completed()`, `approved()`, `rejected()` states using `TaskStatus` enum

---

## Phase H — Seeders (depends on T001–T010)

- [ ] T043 Create `backend/database/seeders/RolePermissionSeeder.php` — assigns permissions to roles: customer (6 perms), contractor (10 perms), supervising_architect (8 perms), field_engineer (5 perms), admin (all perms); uses `firstOrCreate` on role_permissions pivot
- [ ] T044 Update `backend/database/seeders/DatabaseSeeder.php` — call in order: RoleSeeder, PermissionSeeder, RolePermissionSeeder, UserSeeder, ProductSeeder

---

## Phase I — Unit Tests (depends on T001–T013)

- [ ] T045 Create `backend/tests/Unit/Enums/UserRoleTest.php` — test: all 5 cases exist, `label()` returns Arabic strings, `values()` returns correct array, `from('customer')` returns UserRole::Customer, `tryFrom('invalid')` returns null
- [ ] T046 Create `backend/tests/Unit/Enums/ProjectStatusTest.php` — test: all 5 cases, labels, values(), from(), tryFrom()
- [ ] T047 Create `backend/tests/Unit/Enums/PhaseStatusTest.php` — test: all 5 cases, labels, values(), from(), tryFrom()
- [ ] T048 Create `backend/tests/Unit/Enums/TaskStatusTest.php` — test: all 5 cases, labels, values(), from(), tryFrom()
- [ ] T049 Create `backend/tests/Unit/Enums/OtherEnumsTest.php` — test: OrderStatus (6 cases), TransactionType (4), TransactionStatus (4), WorkflowType (3), ApprovalStatus (3), ReportType (4) — labels and values()
- [ ] T050 Create `backend/tests/Unit/Repositories/BaseRepositoryTest.php` — test using an in-memory concrete implementation: findById returns model, findByIdOrFail throws on missing, create persists, update saves, delete soft-deletes, restore recovers

---

## Phase J — Feature Tests (depends on full implementation)

- [ ] T051 Create `backend/tests/Feature/Database/DatabaseSchemaTest.php` — assert tables exist: users, roles, permissions, role_permissions, role_user, projects, phases, tasks, reports, orders, order_items, products, transactions, workflow_configurations, approval_rules; assert key columns exist on each
- [ ] T052 Create `backend/tests/Feature/Database/MigrationRollbackTest.php` — call `artisan migrate:rollback` and assert tables are dropped; call `artisan migrate` and assert tables re-created (uses `RefreshDatabase`)
- [ ] T053 Create `backend/tests/Feature/Database/SeederTest.php` — call DatabaseSeeder, assert: 5 roles exist with correct names, all expected permissions seeded, role-permission assignments correct (admin has all perms, customer has 6), test users exist
- [ ] T054 Create `backend/tests/Feature/Database/SoftDeleteTest.php` — create User, Project, Phase, Task; soft delete each; assert `deleted_at` set; assert not found in default queries; assert found with `withTrashed()`; assert `restore()` recovers
- [ ] T055 Create `backend/tests/Feature/Database/EnumCastTest.php` — create models with enum values, read back from DB, assert $model->role returns UserRole instance, $model->status returns ProjectStatus instance etc.; assert `->value` matches stored string; assert `->label()` returns Arabic

---

## Phase K — Validation Gate

- [ ] T056 Run `cd backend && vendor/bin/pint --test` — must report 0 violations
- [ ] T057 Run `cd backend && vendor/bin/pint` — auto-fix any style issues
- [ ] T058 Run `cd backend && vendor/bin/phpstan analyse --memory-limit=512M` — must report 0 errors at level 8
- [ ] T059 Run `cd backend && php artisan test --filter EnumTest` — all enum unit tests pass
- [ ] T060 Run `cd backend && php artisan test --filter DatabaseSchemaTest` — schema assertions pass
- [ ] T061 Run `cd backend && php artisan test --filter SeederTest` — seeder tests pass
- [ ] T062 Run `cd backend && php artisan test --filter SoftDeleteTest` — soft delete tests pass
- [ ] T063 Run `cd backend && php artisan test --filter EnumCastTest` — enum cast tests pass
- [ ] T064 Run `cd backend && php artisan test` — all backend tests pass (0 failures)
- [ ] T065 Run `cd backend && php artisan migrate:rollback --pretend` — no errors
- [ ] T066 Run `cd backend && php artisan migrate --pretend` — no errors
- [ ] T067 Run `cd backend && php artisan db:seed --pretend` — no errors

---

## Phase L — Quickstart Verification

- [ ] T068 Create `backend/app/Enums/` directory if not exists (should be auto-created)
- [ ] T069 Create `backend/app/Models/Concerns/` directory for the trait
- [ ] T070 Create `backend/tests/Unit/Enums/` directory
- [ ] T071 Create `backend/tests/Feature/Database/` directory
- [ ] T072 Verify `composer dump-autoload` runs cleanly after all new classes added
