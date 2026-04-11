# STAGE_02 — Database Schema Foundation

> **Phase:** 01_PLATFORM_FOUNDATION
> **Status:** PRODUCTION READY
> **Scope:** Core MySQL schema, base migrations, Eloquent model patterns
> **Risk Level:** MEDIUM

## Stage Status

Status: PRODUCTION READY
Step: closure
Risk Level: MEDIUM
Initiated: 2026-04-11T00:00:00Z
Last Updated: 2026-04-11T12:30:00Z
Completed: 2026-04-11T12:30:00Z
PR: https://github.com/majedsiefalnasr/bunyan-app-cursor/pull/2

Quality Gates:

- PHPStan Level 5: PASS (0 errors)
- Tests: 31 passed / 0 failed (278 assertions)
- Architecture Guardian: PASS
- Security Auditor: PASS
- Performance Optimizer: PASS
- Code Reviewer: PASS

Deliverables:

- 10 PHP Enums (UserRole, ProjectStatus, PhaseStatus, TaskStatus, OrderStatus, TransactionType, TransactionStatus, WorkflowType, ApprovalStatus, ReportType)
- BaseModel abstract class with opt-in SoftDeletes + scopeOrdered
- HasBaseModelBehavior trait for User model composition
- BaseRepository abstract class (8 standard CRUD + query methods)
- role_user pivot migration with assigned_by audit trail
- Enum casts integrated into all 13 models
- Factory states for UserFactory, ProjectFactory, PhaseFactory, TaskFactory
- RolePermissionSeeder + DatabaseSeeder ordering
- 11 test files (5 unit, 5 feature, 1 repository)

Deferred Scope:

- Auth/RBAC logic (STAGE_03/04)
- API controllers (STAGE_03+)
- Frontend (later phases)

Architecture Governance Compliance:

- All ADRs followed
- No invention of architecture
- RBAC enforced server-side
- Layered architecture maintained (Controllers → Services → Repositories → Models)

## Objective

Define the core database schema for Bunyan. Establish migration patterns, Eloquent model conventions, and the repository pattern foundation.

## Scope

### Backend

- Core migrations: users, roles, permissions tables
- Base Eloquent model with shared traits (HasUuid, HasTimestamps, SoftDeletes)
- Repository pattern base class
- Database seeder structure
- Factory classes for testing

### Database Design

| Table           | Purpose                                                             |
| --------------- | ------------------------------------------------------------------- |
| users           | User accounts (all roles)                                           |
| roles           | Role definitions (Customer, Contractor, Architect, Engineer, Admin) |
| permissions     | Granular permission definitions                                     |
| role_user       | User-role pivot                                                     |
| permission_role | Permission-role pivot                                               |

## Dependencies

- **Upstream:** STAGE_01_PROJECT_INITIALIZATION
- **Downstream:** STAGE_03_AUTHENTICATION, STAGE_04_RBAC_SYSTEM
