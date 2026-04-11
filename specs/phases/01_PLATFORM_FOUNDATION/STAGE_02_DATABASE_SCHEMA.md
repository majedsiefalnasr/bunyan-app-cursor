# STAGE_02 — Database Schema Foundation

> **Phase:** 01_PLATFORM_FOUNDATION
> **Status:** CLOSED
> **Scope:** Core MySQL schema, base migrations, Eloquent model patterns
> **Risk Level:** MEDIUM

## Stage Status

Status: CLOSED
Step: closure
Risk Level: MEDIUM
Initiated: 2026-04-11T00:00:00Z
Last Updated: 2026-04-11T12:00:00Z
Completed: 2026-04-11T12:00:00Z

Scope Defined:
- 10 PHP Enums (UserRole, ProjectStatus, PhaseStatus, TaskStatus, OrderStatus, TransactionType, TransactionStatus, WorkflowType, ApprovalStatus, ReportType)
- BaseModel abstract class with SoftDeletes, scopeActive, scopeOrdered
- BaseRepository abstract class with 8 standard methods
- role_user pivot migration (additive, forward-only)
- Enum casts integrated into all 10 relevant models
- UserFactory role states (5 roles + inactive)
- Status states for ProjectFactory, PhaseFactory, TaskFactory
- RolePermissionSeeder + DatabaseSeeder ordering
- 10 test files (unit + feature)

Deferred Scope:
- Auth/RBAC logic (STAGE_03/04)
- API controllers (STAGE_03+)
- Frontend (later phases)

Architecture Governance Compliance:
- Specification drafted — governance audit pending

Notes:
Specification complete. All clarifications resolved. Ready for technical planning.

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
