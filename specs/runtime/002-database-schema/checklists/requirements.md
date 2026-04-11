# Requirements Checklist — Database Schema Foundation

## Functional Requirements

### Enums
- [ ] `UserRole` enum with 5 values + Arabic labels + values() method
- [ ] `ProjectStatus` enum with 5 values + Arabic labels
- [ ] `PhaseStatus` enum with 5 values + Arabic labels
- [ ] `TaskStatus` enum with 5 values + Arabic labels
- [ ] `OrderStatus` enum with 6 values + Arabic labels
- [ ] `TransactionType` enum with 4 values + Arabic labels
- [ ] `TransactionStatus` enum with 4 values + Arabic labels
- [ ] `WorkflowType` enum with 3 values + Arabic labels
- [ ] `ApprovalStatus` enum with 3 values + Arabic labels
- [ ] `ReportType` enum with 4 values + Arabic labels

### BaseModel
- [ ] `BaseModel` abstract class created
- [ ] `SoftDeletes` included in BaseModel
- [ ] `scopeActive()` scope defined
- [ ] `scopeOrdered()` scope defined
- [ ] All concrete models updated to extend BaseModel

### BaseRepository
- [ ] `BaseRepository` abstract class created
- [ ] `findById()` method defined
- [ ] `findByIdOrFail()` method defined
- [ ] `all()` method with filter support
- [ ] `create()` method defined
- [ ] `update()` method defined
- [ ] `delete()` method (soft delete)
- [ ] `restore()` method defined
- [ ] `paginate()` helper defined
- [ ] All existing repositories updated to extend BaseRepository

### Migration
- [ ] `role_user` pivot migration created
- [ ] FK constraints on user_id and role_id
- [ ] Unique constraint on (user_id, role_id)
- [ ] `down()` rollback defined
- [ ] Migration validated with `php artisan migrate --pretend`

### Enum Integration
- [ ] User model casts `role` to `UserRole`
- [ ] Project model casts `status` to `ProjectStatus`
- [ ] Phase model casts `status` to `PhaseStatus`
- [ ] Task model casts `status` to `TaskStatus`
- [ ] Order model casts `status` to `OrderStatus`
- [ ] Transaction model casts `type` to `TransactionType`
- [ ] Transaction model casts `status` to `TransactionStatus`
- [ ] WorkflowConfiguration model casts `type` to `WorkflowType`
- [ ] ApprovalRule model casts `status` to `ApprovalStatus`
- [ ] Report model casts `type` to `ReportType`

### Factories
- [ ] UserFactory has `customer()` state
- [ ] UserFactory has `contractor()` state
- [ ] UserFactory has `supervisingArchitect()` state
- [ ] UserFactory has `fieldEngineer()` state
- [ ] UserFactory has `admin()` state
- [ ] UserFactory has `inactive()` state
- [ ] ProjectFactory has status states
- [ ] PhaseFactory has status states
- [ ] TaskFactory has status states

### Seeders
- [ ] `RolePermissionSeeder` created and assigns all permissions to roles
- [ ] `DatabaseSeeder` calls all seeders in correct order

## Non-Functional Requirements

### Architecture
- [ ] No business logic in models (only relationships, casts, scopes)
- [ ] No Eloquent queries in services (via repositories only)
- [ ] Repositories only extend `BaseRepository`
- [ ] Enums used everywhere raw strings were used for domain values

### Database
- [ ] All migrations forward-only (no existing migrations modified)
- [ ] All FK columns have indexes
- [ ] Charset: utf8mb4, collation: utf8mb4_unicode_ci
- [ ] Soft deletes on all main entity tables (already exist from Stage 01)

### Testing
- [ ] Unit tests for all 10 enums (label, values, from, tryFrom)
- [ ] Unit test for BaseRepository contract
- [ ] Feature test: DatabaseSchemaTest (all tables have correct columns)
- [ ] Feature test: MigrationRollbackTest (all migrations rollback cleanly)
- [ ] Feature test: SeederTest (data seeded correctly)
- [ ] Feature test: SoftDeleteTest (User, Project, Phase, Task)
- [ ] Feature test: EnumCastTest (enum casting works end-to-end)
- [ ] All tests pass: `cd backend && php artisan test`
- [ ] Lint passes: `cd backend && vendor/bin/php-cs-fixer fix --dry-run`
- [ ] PHPStan passes: `cd backend && vendor/bin/phpstan analyse`

### RBAC
- [ ] No auth/RBAC logic introduced (deferred to STAGE_03/04)

### i18n
- [ ] All enum labels in Arabic
- [ ] No hardcoded English-only user-facing text in enums
