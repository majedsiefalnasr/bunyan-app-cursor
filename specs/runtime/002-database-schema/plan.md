# Plan — Database Schema Foundation

**Stage:** STAGE_02_DATABASE_SCHEMA
**Phase:** 01_PLATFORM_FOUNDATION
**Guardian Verdict:** PASS
**Implementation:** AUTHORIZED (pending Step 5 drift analysis)

---

## 1. Execution Strategy

This stage is **pure backend** — no frontend, no HTTP endpoints. All work is in:

- `backend/app/Enums/`
- `backend/app/Models/` (modifications to existing files)
- `backend/app/Models/Concerns/` (new)
- `backend/app/Repositories/` (modifications to existing + new base)
- `backend/database/migrations/` (1 new migration)
- `backend/database/seeders/` (1 new + 1 modified)
- `backend/database/factories/` (4 modified)
- `backend/tests/Unit/` (new)
- `backend/tests/Feature/Database/` (new)

**No migrations are modified.** Stage 01 migrations are immutable.

---

## 2. Implementation Phases

### Phase A — PHP Enums (unblocked, parallel-safe)

Create all 10 enum files. No dependencies on each other.

### Phase B — BaseModel + Concerns Trait (depends on: nothing)

Create `BaseModel` abstract class and `HasBaseModelBehavior` trait.

### Phase C — BaseRepository (depends on: nothing)

Create abstract `BaseRepository` class.

### Phase D — Model Updates (depends on: Phase A + Phase B)

Update all 13 models to:

- Extend `BaseModel` (or use trait for User)
- Add enum casts

### Phase E — Repository Updates (depends on: Phase C)

Update all 10 repositories to extend `BaseRepository`.

### Phase F — Migration (depends on: nothing — additive)

Create `role_user` pivot migration.

### Phase G — Factory Updates (depends on: Phase A)

Add role/status states using enum backing values.

### Phase H — Seeders (depends on: Phase A, existing seeders)

Create `RolePermissionSeeder`. Update `DatabaseSeeder`.

### Phase I — Tests (depends on: Phase A–H)

Write all unit and feature tests.

---

## 3. Technical Specifications

### 3.1 Enum Contract

Every enum MUST implement:

```php
public function label(): string;          // Arabic label
public static function values(): array;   // All backing values
```

PHPStan level 8 requires explicit return types on all methods.

### 3.2 BaseModel Contract

```php
abstract class BaseModel extends Model
{
    use SoftDeletes;

    public function scopeActive(Builder $query): Builder;
    public function scopeOrdered(Builder $query, string $col = 'created_at', string $dir = 'desc'): Builder;
}
```

### 3.3 BaseRepository Contract

```php
abstract class BaseRepository
{
    abstract protected function model(): string;

    public function findById(int $id): ?Model;
    public function findByIdOrFail(int $id): Model;
    public function all(array $filters = []): LengthAwarePaginator;
    public function create(array $data): Model;
    public function update(Model $model, array $data): Model;
    public function delete(Model $model): bool;
    public function restore(int $id): Model;
    protected function newQuery(): Builder;
    protected function paginate(Builder $query, int $perPage = 15): LengthAwarePaginator;
}
```

### 3.4 role_user Migration

```php
Schema::create('role_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
    $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('assigned_at')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'role_id']);
    $table->index('user_id');
    $table->index('role_id');
    $table->index('assigned_by');
});
```

### 3.5 RolePermissionSeeder Logic

```php
// Uses Role and Permission models
// Calls firstOrCreate on role_permissions pivot
// Admin gets all permissions via Role::where('name', 'admin')->first()
//   then $role->permissions()->sync($allPermissionIds)
```

### 3.6 Test Architecture

```
tests/
├── Unit/
│   ├── Enums/
│   │   ├── UserRoleTest.php
│   │   ├── ProjectStatusTest.php
│   │   ├── PhaseStatusTest.php
│   │   ├── TaskStatusTest.php
│   │   └── OtherEnumsTest.php  (OrderStatus, TransactionType, etc.)
│   └── Repositories/
│       └── BaseRepositoryTest.php
└── Feature/
    └── Database/
        ├── DatabaseSchemaTest.php
        ├── MigrationRollbackTest.php
        ├── SeederTest.php
        ├── SoftDeleteTest.php
        └── EnumCastTest.php
```

---

## 4. Architecture Guardian Verdict

### Architecture Guardian

**VERDICT: PASS**

- No business logic in models ✅
- No raw SQL ✅
- No Eloquent in services (repositories only) ✅
- Clean layer separation maintained ✅
- RBAC untouched (deferred to Stage 04) ✅
- Forward-only migration ✅
- Enum pattern follows ADR conventions ✅

### API Designer

**VERDICT: PASS** (no API changes in this stage)

---

## 5. Risk Assessment

| Risk                                              | Level  | Action                                                        |
| ------------------------------------------------- | ------ | ------------------------------------------------------------- |
| PHPStan fails on existing models after enum casts | MEDIUM | Run PHPStan after each model update                           |
| role_user migration FK fails if run out of order  | LOW    | Laravel runs migrations in timestamp order                    |
| Seeder circular dependency                        | LOW    | RolePermissionSeeder explicitly placed after PermissionSeeder |
| Factory enum string values mismatch               | LOW    | Use `EnumClass::Case->value` in factories                     |
| BaseRepository abstract method missing in child   | LOW    | PHP will throw Fatal Error at test time — caught immediately  |
