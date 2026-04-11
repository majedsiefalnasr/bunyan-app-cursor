# Plan Report — Database Schema Foundation

> **Phase:** 01_PLATFORM_FOUNDATION
> **Generated:** 2026-04-11T00:20:00Z

## Plan Summary

| Metric         | Value                                                                   |
| -------------- | ----------------------------------------------------------------------- |
| New Tables     | 1 (role_user pivot via migration)                                       |
| New Endpoints  | 0 (no HTTP layer in this stage)                                         |
| New Services   | 0                                                                       |
| New Classes    | 13 (10 Enums + BaseModel + HasBaseModelBehavior trait + BaseRepository) |
| New Seeders    | 1 (RolePermissionSeeder)                                                |
| Modified Files | 28 (13 models, 10 repositories, 4 factories, 1 seeder)                  |
| New Test Files | 10 (5 unit, 5 feature)                                                  |
| New Pages      | 0                                                                       |
| New Components | 0                                                                       |

## Architecture Decisions

### AD-1: String-Backed Enums

All domain value types use string-backed PHP 8.1+ native enums. Storing readable values in MySQL columns ensures:

- Self-documenting database
- No mapping layer required for API serialization
- Natural validation with `Rule::enum()`

### AD-2: BaseModel/Trait Composition for User

`User` extends `Authenticatable` (Laravel's auth base), so cannot extend `BaseModel` directly. Solution: extract shared behavior into `HasBaseModelBehavior` trait applied to `User`. All other models extend `BaseModel` directly.

### AD-3: role_user Pivot is Additive

The `role` string column on `users` remains the authoritative RBAC source. The `role_user` pivot is additive infrastructure for future multi-role scenarios. STAGE_04 decides whether to activate it.

### AD-4: BaseRepository uses `model()` method

Rather than constructor injection (which prevents abstract class usage), `BaseRepository` defines `abstract protected function model(): string` returning the FQCN. Each child repository implements this.

## Guardian Verdicts

| Guardian              | Verdict | Notes                                                   |
| --------------------- | ------- | ------------------------------------------------------- |
| Architecture Guardian | PASS    | Clean layering, forward-only migration, no RBAC changes |
| API Designer          | PASS    | No API changes in this stage                            |

## Risk Assessment

| Risk Level | Count | Details                                                                                |
| ---------- | ----- | -------------------------------------------------------------------------------------- |
| HIGH       | 0     | —                                                                                      |
| MEDIUM     | 2     | PHPStan level 8 on existing models; ensure return types added; role_user FK ordering   |
| LOW        | 3     | Factory enum string values mismatch; Seeder order; BaseRepository abstract enforcement |
