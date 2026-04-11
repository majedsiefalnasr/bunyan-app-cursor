# STAGE_01: Project Initialization — Technical Execution Plan

**Phase:** 01_PLATFORM_FOUNDATION  
**Status:** PLANNING  
**Date:** 2026-04-10  
**Audience:** Engineering team, project leads, AI agents

---

## Executive Summary

This document provides a detailed technical roadmap for initializing the Bunyan platform across backend (Laravel 11.x), frontend (Nuxt.js 3), database (MySQL 8.x), and CI/CD infrastructure (GitHub Actions). The plan spans **4 weeks of continuous development** broken into 5 sequential phases with parallel work tracks where safe.

**Critical Path:** Backend scaffolding → Migrations → API contracts → Frontend integration → Testing integration → CI/CD

**Key Dependencies:**

- Backend database schema must exist before services can be implemented
- API contracts must be finalized before frontend integration begins
- Testing configuration must be in place before any code can merge

---

## 1. Timeline & Phases

### Phase 1: Infrastructure & Setup (Days 1-2, ~8-10 hours)

**Objectives:**

- Initialize Laravel and Nuxt.js projects with correct versions and dependencies
- Set up Docker Compose for local development
- Configure all dev tools (linters, formatters, test runners)
- Establish project structure and Git workflow

**Deliverables:**

- Monorepo structure created (backend/, frontend/, docs/, specs/)
- `docker-compose.yml` with MySQL 8.0, Redis 7, PHP 8.3, Node 20
- All npm and composer dependencies installed and verified
- GitHub Actions CI/CD scaffolding (.github/workflows/)
- Pre-commit hooks setup (.husky/, .lintstagedrc.json)

**Parallel Work Tracks:**

- Backend: Laravel project creation, composer scaffolding
- Frontend: Nuxt project creation, npm scaffolding
- DevOps: Docker Compose, GitHub Actions templates

**Estimated Duration:** 8-10 hours  
**Risk:** Version incompatibilities (Laravel 11 + PHP 8.3, Nuxt 3 + Node 20) → Mitigation: Use tested LTS versions

**Success Criteria:**

- ✅ `npm run dev:backend` and `npm run dev:frontend` both start without errors
- ✅ Docker Compose services (MySQL, Redis) accessible on specified ports
- ✅ Pre-commit hooks block commits with linting violations

---

### Phase 2: Backend Database & Layering (Days 3-6, ~16-20 hours)

**Objectives:**

- Create all 13 database migrations with correct relationships and indexes
- Implement Eloquent models with scopes, accessors, and relationships
- Create 10 repository classes with query methods
- Establish Laravel service layer foundation
- Implement RBAC policies (8 policies)

**Deliverables:**

- 13 migrations (users, roles, projects, phases, tasks, reports, transactions, products, categories, orders, order_items, workflow_configs, approval_rules)
- 10 Eloquent models with full relationships graph
- 10 repository classes (UserRepository, ProjectRepository, PhaseRepository, TaskRepository, ReportRepository, WorkflowConfigRepository, ApprovalRuleRepository, TransactionRepository, ProductRepository, OrderRepository)
- 8 policy classes with authorization logic
- BaseController and error handling foundation

**Parallel Work Tracks:**

- Migrations and models (single developer)
- Repositories implementation (single developer)
- Policies and authorization (single developer)
- All work tracked in separate feature branches

**Estimated Duration:** 16-20 hours  
**Risk:** Complex relationships (many-to-many, polymorphic) → Mitigation: Use Eloquent relationship testing early

**Success Criteria:**

- ✅ All migrations run forward and rollback without errors: `php artisan migrate`
- ✅ All models load and relationships hydrate correctly
- ✅ 10 repositories 100% tested (unit tests pass)
- ✅ Policies correctly enforce role-based logic

---

### Phase 3: Backend API Contracts & Controllers (Days 7-10, ~12-16 hours)

**Objectives:**

- Define and document complete API contract (OpenAPI spec or similar)
- Create 8 API controller classes with thin implementations
- Create 15 Form Request validation classes
- Create 10 API Resource classes for response formatting
- Implement error response standardization
- Create authentication endpoints (login, register, logout, refresh)

**Deliverables:**

- Complete API contract document (endpoints, schemas, auth, error codes)
- 8 controller classes (Auth, Project, Phase, Task, Report, Transaction, Product, Order)
- 15 Form Request classes with comprehensive validation rules
- 10 API Resource classes for consistent response formatting
- Global exception handler with contract compliance
- Authentication service with Sanctum token management

**Parallel Work Tracks:**

- Controllers and Form Requests (single developer)
- API Resources (single developer)
- Authentication service (single developer)

**Estimated Duration:** 12-16 hours  
**Risk:** Validation rules complexity (nested arrays, file uploads) → Mitigation: Create validation helper utilities

**Success Criteria:**

- ✅ API contract document complete and reviewed by PM
- ✅ `POST /api/v1/auth/login` returns correct response structure
- ✅ All Form Requests validate input correctly and return contract-compliant errors
- ✅ Unauthenticated requests return 401, unauthorized requests return 403

---

### Phase 4: Backend Services & Business Logic (Days 11-15, ~20-24 hours)

**Objectives:**

- Implement 10 service classes with dependency injection
- Create 8 service test suites (unit tests, ≥80% coverage)
- Implement workflow state machine (WorkflowService)
- Create event/listener infrastructure for domain events
- Implement comprehensive error handling and logging

**Deliverables:**

- 10 service classes (AuthService, ProjectService, PhaseService, TaskService, ReportService, WorkflowService, TransactionService, ProductService, OrderService, NotificationService)
- 50+ PHPUnit unit tests with ≥80% coverage
- Domain event classes (PhaseApproved, PhaseCompleted, StatusTransitioned, etc.)
- Event listeners for side effects (email notifications, audit logging)
- Custom exception classes with error codes

**Parallel Work Tracks:**

- Services implementation (multiple developers, each owns 2-3 services)
- Unit tests (paired with service implementation)
- Event/listener infrastructure (single developer)

**Estimated Duration:** 20-24 hours  
**Risk:** Services interdependency issues → Mitigation: Use contracts (interfaces) to define dependencies early

**Success Criteria:**

- ✅ `composer run test` passes with ≥80% coverage
- ✅ All services resolve dependencies via constructor injection
- ✅ WorkflowService correctly validates state transitions
- ✅ No direct Eloquent queries in services (all via repositories)

---

### Phase 5: Frontend Scaffolding & Integration (Days 16-20, ~12-16 hours)

**Objectives:**

- Initialize Nuxt 3 with Nuxt UI, Pinia, i18n modules
- Create layout structure (default, auth, admin layouts)
- Implement 6 Pinia stores (auth, project, phase, task, ui, notification)
- Create composables for API client and common logic
- Implement RTL support and Arabic translations
- Create base components for reuse (layouts, forms, cards)

**Deliverables:**

- Nuxt 3 project structure complete
- 3 layout components (default, auth, admin)
- 6 Pinia stores with getters/setters/actions
- 8 composables (useAuth, useProject, useApi, useNotification, useI18n, useForm, usePagination, useDebounce)
- i18n configuration with Arabic/English support
- 4-5 base page templates (login, register, dashboard, 404)
- Geist font integration with Tailwind CSS
- RTL-aware component structure

**Parallel Work Tracks:**

- Project setup and configuration (single developer)
- Pinia stores (single developer)
- Composables and utilities (single developer)
- Base pages and components (single developer)

**Estimated Duration:** 12-16 hours  
**Risk:** i18n + RTL complexity → Mitigation: Use existing Vercel/Geist patterns, test early with Arabic text

**Success Criteria:**

- ✅ `npm run dev` runs without errors on localhost:3000
- ✅ All pages render with Nuxt UI components visible
- ✅ i18n configured with Arabic (default) and English
- ✅ RTL layout works when switching to Arabic locale
- ✅ `npm run typecheck` passes with zero TypeScript errors

---

### Phase 6: Testing Integration (Days 21-24, ~16-20 hours)

**Objectives:**

- Set up test infrastructure (PHPUnit, Vitest, Playwright)
- Create 30+ backend feature tests (API integration tests)
- Create 20+ frontend component/unit tests
- Create 10+ E2E tests (critical user flows)
- Configure test coverage reporting
- Integrate tests into CI/CD pipeline

**Deliverables:**

- Backend feature test suite (30+ tests, endpoints + RBAC + policies)
- Frontend unit/component test suite (20+ tests)
- E2E test suite (10+ critical flows: login, project creation, report submission)
- Test coverage reports (backend ≥80%, frontend ≥70%)
- CI/CD test job configurations
- Test data factories and seeders

**Parallel Work Tracks:**

- Backend tests (multiple developers)
- Frontend tests (single developer)
- E2E tests (single developer)

**Estimated Duration:** 16-20 hours  
**Risk:** Test environment setup (database, API mocking) → Mitigation: Use TestCase base classes, database transactions for isolation

**Success Criteria:**

- ✅ `composer run test` passes with ≥80% backend coverage
- ✅ `npm run test` passes with ≥70% frontend coverage
- ✅ `npm run test:e2e` passes all critical flows
- ✅ GitHub Actions CI/CD runs all tests on PR

---

### Phase 7: Documentation & Finalization (Days 25-28, ~8-10 hours)

**Objectives:**

- Complete developer onboarding guide (quickstart.md)
- Write API documentation (contracts, examples)
- Create architecture documentation (ADRs, module map)
- Finalize README.md and CONTRIBUTING.md
- Prepare for handoff to IMPLEMENT phase

**Deliverables:**

- quickstart.md (setup instructions, verification, troubleshooting)
- api-contract.md (OpenAPI spec or detailed endpoint docs)
- Architecture documentation (module map, layer diagram)
- README.md (project overview, features, tech stack)
- CONTRIBUTING.md (dev workflow, PR process, code standards)

**Single Track:** Documentation (senior engineer or PM)

**Estimated Duration:** 8-10 hours  
**Risk:** Documentation drift from implementation → Mitigation: Document as you build, use auto-generated docs

**Success Criteria:**

- ✅ New developer can follow quickstart.md to run project in <30 min
- ✅ API contract document matches actual endpoints
- ✅ All README links are valid

---

## 2. Critical Path Analysis

```
Phase 1 (Setup)
    ↓
Phase 2 (Database & Layering) ← CRITICAL PATH START
    ↓
Phase 3 (API Contracts & Controllers)
    ↓
Phase 4 (Services & Business Logic) ← CRITICAL PATH END
    ↓
Phase 5 (Frontend Integration)
    ↓
Phase 6 (Testing Integration)
    ↓
Phase 7 (Documentation)
```

**Critical Path Duration:** 15 days (Phases 2-4)  
**Total Duration:** 28 days (4 weeks)

**Critical Dependencies:**

1. Database schema (Phase 2) blocks all service development (Phase 4)
2. API contracts (Phase 3) block frontend integration (Phase 5)
3. Backend tests (Phase 6) must pass before frontend tests can validate end-to-end flows

**Non-Critical Phases (Can Start Earlier):**

- Phase 1 (Setup) happens in parallel with project kickoff
- Phase 5 (Frontend) can start after Phase 3 API contracts finalized

---

## 3. Risk Mitigation

### High-Risk Areas

#### Risk 1: Database Schema Complexity

**Severity:** HIGH  
**Probability:** MEDIUM

**Details:**

- 13 migrations with complex relationships (many-to-many, polymorphic)
- Foreign key constraints and cascading deletes
- Workflow engine requires polymorphic relationships (entity_type + entity_id)

**Mitigation:**

- Start with migration tests early (Phase 2)
- Document schema diagram before coding
- Review migrations in code review before Phase 3 starts
- Use Eloquent testing to validate relationships before Phase 4

**Owner:** Backend Lead

#### Risk 2: API Contract Misalignment

**Severity:** HIGH  
**Probability:** MEDIUM

**Details:**

- Frontend and backend teams may have different expectations for response format
- RBAC validation can cause inconsistent error codes (401 vs 403 vs 422)
- Pagination, filtering, sorting standards not defined

**Mitigation:**

- Create comprehensive API contract document BEFORE Phase 3 coding starts
- Include JSON schema examples for every endpoint
- Document error codes mapping (400, 401, 403, 422, 500)
- Have frontend architect review contract before backend implementation

**Owner:** API Architect

#### Risk 3: RBAC Enforcement Gaps

**Severity:** CRITICAL  
**Probability:** HIGH

**Details:**

- Easy to miss policy checks on certain endpoints
- Complex nested authorization (task access through project through customer)
- Admin bypass logic can introduce security holes

**Mitigation:**

- Use strict code review for all policy implementations
- Create policy test matrix (every role × every action)
- Use architecture guardian validation (automated checks)
- Audit all routes for missing `can:` middleware

**Owner:** Security Lead

#### Risk 4: Frontend-Backend Integration Timing

**Severity:** MEDIUM  
**Probability:** MEDIUM

**Details:**

- Frontend development can't fully proceed without stable API
- Changes to API during Phase 4 (services) can break frontend
- CORS, authentication token handling, error mapping issues

**Mitigation:**

- Create mock API server for frontend before Phase 4 finalized
- Use API contract stubs (return fake but valid JSON)
- Coordinate frontend-backend integration meetings weekly
- Have frontend test against real API early (Phase 5 onwards)

**Owner:** Tech Lead

#### Risk 5: Testing Coverage Gaps

**Severity:** MEDIUM  
**Probability:** MEDIUM

**Details:**

- 80% backend coverage target is strict
- Complex workflows (multi-step approvals, state transitions) hard to test
- E2E tests flaky if database not properly seeded

**Mitigation:**

- Set up test factory infrastructure early (Phase 2)
- Use database transactions to isolate tests
- Create test scenarios document (all flows that need E2E coverage)
- Run tests locally before pushing (pre-commit hook)
- Use test coverage badges to track progress

**Owner:** QA Lead

---

## 4. Dependency Resolution Order

### Must Complete Before Others Can Start

```
1. Phase 1 Setup (all)
   ├─ Docker Compose (needed for dev)
   ├─ Git workflow (.husky, branch protection)
   └─ CI/CD scaffolding (needed for validation)

2. Phase 2 Database & Layering
   ├─ All 13 migrations must run without error
   ├─ All models must load and hydrate
   └─ All repositories must be queryable

3. Phase 3 API Contracts & Controllers
   ├─ Contract document MUST be reviewed before coding
   ├─ Authentication endpoints must work (POST /auth/login)
   └─ Error response format must be consistent

4. Phase 4 Services & Business Logic
   ├─ All 10 services must be dependency-injectable
   ├─ All services must have ≥80% unit test coverage
   └─ No direct Eloquent queries outside repositories

5. Phase 5 Frontend Scaffolding
   ├─ All API endpoints must return valid JSON
   ├─ Authentication token must be usable in frontend
   └─ CORS must be configured

6. Phase 6 Testing Integration
   ├─ Backend tests must pass before E2E
   ├─ Frontend components must render before integration tests
   └─ Test database must be isolated (SQLite in-memory)

7. Phase 7 Documentation
   ├─ All code must be finalized
   └─ All API endpoints must be stable
```

### Parallel Work (No Blocking Dependencies)

- Phase 1 Setup: Backend scaffolding, Frontend scaffolding, DevOps
- Phase 2: Multiple developers can work on different models/services in parallel
- Phase 5: Can start after Phase 3 API contracts, before Phase 4 finalized
- Phase 6: Tests can be written alongside Phase 4 service implementations

---

## 5. Resource Allocation

### Team Composition Recommended

**Total:** 4-5 developers + 1 DevOps engineer + 1 QA lead

| Role                | Phase  | Weeks |
| ------------------- | ------ | ----- |
| Backend Lead        | 1-7    | 4     |
| Frontend Lead       | 1, 5-7 | 3     |
| DevOps Engineer     | 1, 6-7 | 1.5   |
| Backend Developer 2 | 2-4, 6 | 3     |
| Backend Developer 3 | 2-4, 6 | 3     |
| QA Lead             | 1, 6-7 | 2     |

### Effort Distribution (in person-weeks)

- **Phase 1 (Setup):** 1 person-week
- **Phase 2 (Database & Layering):** 2 person-weeks
- **Phase 3 (API Contracts):** 1.5 person-weeks
- **Phase 4 (Services):** 3 person-weeks
- **Phase 5 (Frontend):** 1.5 person-weeks
- **Phase 6 (Testing):** 2 person-weeks
- **Phase 7 (Documentation):** 0.5 person-weeks

**Total:** 11.5 person-weeks (~1 developer for 11+ weeks, or 2-3 developers for 4 weeks)

---

## 6. Success Metrics

### Phase-by-Phase Gates

**Phase 1 Complete When:**

- ✅ `npm run dev:backend` and `npm run dev:frontend` both run error-free
- ✅ Docker Compose services all healthy (`docker-compose ps`)
- ✅ First commit pushed to feature branch with clean CI

**Phase 2 Complete When:**

- ✅ All 13 migrations apply and rollback without errors
- ✅ All models load correctly with `php artisan tinker`
- ✅ All repositories have 10+ test cases passing

**Phase 3 Complete When:**

- ✅ API contract document reviewed and approved by PM
- ✅ `POST /api/v1/auth/login` returns correct structure
- ✅ All endpoints return consistent error format

**Phase 4 Complete When:**

- ✅ `composer run test` passes with ≥80% coverage
- ✅ All services use constructor injection (no `new` keyword)
- ✅ WorkflowService correctly validates state transitions

**Phase 5 Complete When:**

- ✅ `npm run dev` runs without errors
- ✅ All pages render with Nuxt UI visible
- ✅ `npm run typecheck` passes with zero errors

**Phase 6 Complete When:**

- ✅ `npm run test` passes with ≥70% coverage
- ✅ `npm run test:e2e` passes 10/10 critical flows
- ✅ GitHub Actions CI/CD green on main branch

**Phase 7 Complete When:**

- ✅ quickstart.md tested (new dev follows it successfully)
- ✅ API contract matches actual endpoints
- ✅ All README links valid

---

## 7. Rollback Strategy

### If Phase Cannot Complete

**Phase 1 Fails:**

- Roll back all commits, restart infrastructure from scratch
- Re-evaluate tool versions and dependencies

**Phase 2 Fails:**

- Roll back all migrations: `php artisan migrate:rollback`
- Review schema design, consult with architect

**Phase 3 Fails:**

- Keep migrations (Phase 2), roll back controllers/requests
- Review API contract, get approval before re-implementing

**Phase 4 Fails:**

- Keep migrations + controllers (Phase 2-3), roll back services
- Review test cases, identify failing test scenarios

**Phase 5 Fails:**

- Keep backend (Phase 1-4), roll back frontend scaffolding
- Review i18n + RTL implementation, try simpler approach

**Phase 6 Fails:**

- Keep all code, focus on test infrastructure
- Don't merge code without test coverage

---

## 8. Communication Plan

### Weekly Standup (Every Monday)

- Phase lead reports: % complete, blockers, risks
- All phase leads attend (30 min)

### Phase Gate Meetings (End of each phase)

- Phase lead presents deliverables
- Architecture review: Does it meet spec?
- Go/No-Go decision before proceeding

### Escalation Path

1. Task → Phase Lead (1 hour decision window)
2. Phase Lead → Tech Lead (4 hour decision window)
3. Tech Lead → Project Manager (same day)

---

## 9. Definition of Done

**Code is "Done" when:**

1. ✅ Passes all linting checks (`composer run lint`, `npm run lint`)
2. ✅ Passes all tests (backend ≥80%, frontend ≥70%)
3. ✅ Passes static analysis (`phpstan analyse`)
4. ✅ Passes TypeScript checks (`npm run typecheck`)
5. ✅ Passes code review (2 approvals minimum)
6. ✅ Deployment checklist completed
7. ✅ Documentation updated
8. ✅ No known security vulnerabilities (checked via composer audit, npm audit)

**Phase is "Done" when:**

1. ✅ All phase deliverables completed
2. ✅ All code merged to `develop` branch
3. ✅ Phase gate review passed
4. ✅ No critical bugs in phase code
5. ✅ Next phase can begin without blockers

---

## 10. Estimated Timeline (Gantt Overview)

```
Week 1:
  [Phase 1: Setup                          ]

Week 2:
  [Phase 2: Database & Layering           ]

Week 3:
  [Phase 3: API Contracts] [Phase 5: Frontend (starts)]
  [Phase 4: Services      ]

Week 4:
  [Phase 4 cont.          ]
  [Phase 6: Testing Integration           ]
  [Phase 7: Documentation                 ]
```

**Actual start date:** 2026-04-10 (Thursday)
**Expected completion:** 2026-05-08 (Wednesday, 28 calendar days)

---

**Generated by:** PLAN Step  
**Specification Date:** 2026-04-10  
**Status:** READY FOR REVIEW
