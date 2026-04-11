# Specify Report — Database Schema Foundation

> **Phase:** 01_PLATFORM_FOUNDATION
> **Generated:** 2026-04-11T00:00:00Z

## Specification Summary

| Metric                 | Value                                                                                                                                       |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| User Stories           | 5 (Enums, BaseModel, BaseRepository, role_user pivot, Enum casts)                                                                           |
| Acceptance Criteria    | 52 checklist items across functional + non-functional                                                                                       |
| Technical Requirements | 10 PHP Enums, 1 BaseModel, 1 BaseRepository, 1 migration, 10 model enum casts, factory states, 1 new seeder, seeder ordering, 10 test files |
| Dependencies           | STAGE_01_PROJECT_INITIALIZATION (PRODUCTION READY)                                                                                          |
| Open Questions         | 0 (all resolved via clarifications)                                                                                                         |

## Scope Defined

### PHP Enums (10 total)

- `UserRole`, `ProjectStatus`, `PhaseStatus`, `TaskStatus`, `OrderStatus`, `TransactionType`, `TransactionStatus`, `WorkflowType`, `ApprovalStatus`, `ReportType`
- All string-backed, with `label()` Arabic method and `values()` static method

### BaseModel

- Abstract Eloquent base with `SoftDeletes`, `scopeActive()`, `scopeOrdered()`
- All concrete models extended

### BaseRepository

- Abstract base with 8 common methods
- All existing repositories updated to extend it

### role_user Pivot Migration

- Forward-only, additive migration for multi-role future support
- Does not break existing `role` string column on users

### Enum Integration into Models

- 10 model cast updates (no migrations required)

### Factory States

- UserFactory: 5 role states + inactive state
- ProjectFactory, PhaseFactory, TaskFactory: status states

### RolePermissionSeeder + DatabaseSeeder Update

- Role-permission matrix seeded per spec
- DatabaseSeeder ordered correctly

### Tests (10 test files)

- Unit: enum tests (x10), BaseRepository test
- Feature: schema, rollback, seeders, soft delete, enum cast

## Deferred Scope

- Authentication logic → STAGE_03_AUTHENTICATION
- RBAC middleware, policies → STAGE_04_RBAC_SYSTEM
- API controllers/routes → STAGE_03+
- Frontend components → later phases
- `role_user` pivot activation in RBAC checks → STAGE_04

## Risk Assessment

| Risk                                   | Level  | Mitigation                                           |
| -------------------------------------- | ------ | ---------------------------------------------------- |
| Modifying existing models (enum casts) | LOW    | No migration required; PHP-level cast only           |
| role_user migration breaking Stage 01  | LOW    | Additive-only; no existing columns modified          |
| PHPStan level 8 failing on new enums   | MEDIUM | Enums must be properly typed; all methods documented |
| Seeders running in wrong order         | LOW    | DatabaseSeeder ordering explicitly specified         |

## Checklist Status

- Requirements checklist: Created at `checklists/requirements.md`
- 52 items tracked across functional and non-functional categories
