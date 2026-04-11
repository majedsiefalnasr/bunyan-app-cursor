# Research — Database Schema Foundation

## 1. PHP Native Enums (PHP 8.1+)

Laravel 9+ supports PHP native enums as Eloquent cast targets via the `$casts` array. String-backed enums are stored as their backing value in the database and automatically deserialized.

### Key Patterns

```php
// Backed enum with label and values methods
enum UserRole: string
{
    case Customer = 'customer';
    case Contractor = 'contractor';
    case SupervisingArchitect = 'supervising_architect';
    case FieldEngineer = 'field_engineer';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'العميل',
            self::Contractor => 'المقاول',
            self::SupervisingArchitect => 'المهندس المشرف',
            self::FieldEngineer => 'المهندس الميداني',
            self::Admin => 'الإدارة',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

// In model:
protected $casts = [
    'role' => UserRole::class,
];

// Usage:
$user->role; // Returns UserRole::Customer
$user->role->label(); // Returns 'العميل'
$user->role->value; // Returns 'customer'
```

### Eloquent Enum Casting

- Laravel automatically calls `from()` when reading from DB
- Calls `->value` (backing value) when writing to DB
- Validation: use `Rule::enum(UserRole::class)` in Form Requests
- Factory: use `UserRole::Customer->value` as string in definitions

## 2. BaseModel Pattern

Laravel's Eloquent models don't enforce a base class beyond `Illuminate\Database\Eloquent\Model`. The pattern uses an abstract intermediate:

```php
abstract class BaseModel extends Model
{
    use SoftDeletes;

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeOrdered(Builder $query, string $column = 'created_at', string $direction = 'desc'): Builder
    {
        return $query->orderBy($column, $direction);
    }
}
```

**User model exception:** `User` extends `Authenticatable` (which extends `Model`). Solution: extract shared traits into `HasBaseModelBehavior` trait:

```php
trait HasBaseModelBehavior
{
    use SoftDeletes;

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }
}
```

## 3. Repository Pattern

The repository pattern in Laravel uses constructor injection and type-hinting. Laravel's service container auto-resolves via type hints.

```php
abstract class BaseRepository
{
    abstract protected function model(): string; // Returns FQCN of model

    protected function newQuery(): Builder
    {
        return app($this->model())->newQuery();
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->find($id);
    }
}
```

**Binding:** Repositories are bound in `AppServiceProvider::register()` or a dedicated `RepositoryServiceProvider`.

## 4. Migration Ordering

MySQL enforces FK constraints during migration. The `role_user` pivot requires `users` and `roles` tables to exist first.

Current migration timestamps (Stage 01):

- `174656` → users
- `174657` → roles

New Stage 02 migration must use timestamp `2026_04_11_120000` which sorts AFTER Stage 01.

## 5. Factory States in Laravel

Laravel factories use the `state()` method:

```php
public function customer(): static
{
    return $this->state(fn (array $attributes) => [
        'role' => UserRole::Customer->value,
    ]);
}
```

## 6. PHPStan Level 8 Compliance

At level 8, PHPStan requires:

- Return types on all public methods
- Parameter types on all methods
- No `mixed` without justification
- Enum cases properly typed

Enums with `label(): string` and `values(): array` satisfy level 8 requirements.

## 7. Testing Patterns for Enums

```php
it('has correct Arabic labels', function () {
    expect(UserRole::Customer->label())->toBe('العميل');
    expect(UserRole::Admin->label())->toBe('الإدارة');
});

it('values() returns all backing values', function () {
    expect(UserRole::values())->toEqual([
        'customer', 'contractor', 'supervising_architect', 'field_engineer', 'admin'
    ]);
});
```
