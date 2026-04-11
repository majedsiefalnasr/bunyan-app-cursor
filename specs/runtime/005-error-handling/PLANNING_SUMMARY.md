# STAGE_05: Error Handling & Logging — Planning Summary

**Generated:** 2026-04-11  
**Agent:** SpecKit.Plan  
**Status:** COMPLETE  
**Deliverables:** 5 documents + 1 contract

---

## Files Generated

### 1. plan.md (14 KB)
**Comprehensive Technical Plan**
- Architecture overview with ASCII flow diagrams
- Complete data models (ErrorCode enum, Resource models)
- All API contracts with 6 example error responses
- Exception hierarchy (base + 7 specific classes)
- File structure to create (14 backend + 11 frontend files)
- Implementation order with 5 phases and dependencies
- Middleware pipeline with exact registration order
- Error code to HTTP status mapping table (12 codes)
- RBAC error detail filtering matrix
- Arabic/RTL implementation checklist
- Testing strategy (backend, frontend, integration, E2E)
- Governance compliance notes

**Key Sections:**
- 1. Architecture Overview
- 2. Data Models
- 3. API Contracts
- 4. Exception Hierarchy
- 5. File Structure (25 total files)
- 6. Implementation Order (5 phases, 14 steps)
- 7. Middleware Pipeline
- 8. Error Code Mapping Table
- 9. RBAC Filtering Matrix
- 10. Arabic/RTL Checklist
- 11. Testing Strategy
- 12. Governance Compliance
- 13. Dependencies & Blockers
- 14. Success Metrics
- 15. Quick Start Checklist

---

### 2. research.md (12 KB)
**Technical Research & Best Practices**
- Laravel exception handling patterns (5 subsections)
- Nuxt.js 3 error boundaries & lifecycle (6 subsections)
- Structured logging libraries (Monolog, JSON format)
- Correlation ID patterns (request vs session, distributed tracing)
- Error notification libraries (Nuxt UI toast, custom implementations)
- RBAC filtering patterns (role-based visibility, policies)
- Frontend API client patterns (fetch with interceptors, typing, retry logic)
- Translation & i18n patterns (Laravel, Nuxt, form validation)
- Best practices & anti-patterns (DO vs DON'T)
- Performance considerations (logging, response size, toast performance)
- Testing error handling (unit, integration, component examples)
- Tools & libraries summary

---

### 3. data-model.md (10 KB)
**Database Schema & Data Models**
- Persistent error logging schema (optional, for Phase 2)
- Error metrics schema (optional, for Phase 3)
- Audit trail schema (optional, for Phase 4)
- ErrorLog model with scopes (byCode, byCorrelationId, inDateRange)
- ErrorLoggingService with query methods
- Error metrics aggregation job
- Audit trail trait for automatic model tracking
- Database design principles (indexing, retention, partitioning)
- In-memory ErrorCodeRegistry (application layer)
- Implementation order (MVP to Phase 4)
- SQL queries for common use cases (trace, hourly, top errors)
- Schema summary with row counts and sizes

---

### 4. quickstart.md (7 KB)
**Get Running in 60 Minutes**
- Phase 1: Backend Bootstrap (15 min)
  - Create ErrorCode enum
  - Create ApiResponse trait
  - Update exception handler
- Phase 2: Frontend Bootstrap (30 min)
  - useApi interceptor composable
  - useErrorNotification composable
  - AppErrorBoundary component
  - Update app.vue
- Phase 3: E2E Test (15 min)
  - Create test endpoints
  - Test validation error
  - Test frontend integration
- Next steps (add more after quickstart)
- Troubleshooting guide
- Files created (190 lines of code)

---

### 5. contracts/laravel-exception-handler.md (4 KB)
**Implementation Contract: Exception Handler**
- Interface contract definition
- 7 rendering rules in priority order:
  1. ValidationException → 422
  2. AuthorizationException → 403
  3. ModelNotFoundException → 404
  4. ThrottleRequestsException → 429
  5. DomainException (custom) → varies
  6. Not Authenticated → 401
  7. Fallback → 500
- Logging contract
- RBAC filtering contract
- Response format contract
- Implementation checklist (13 items)

---

## Key Metrics

### Data Models Defined
- ✓ 1 ErrorCode enum (12 codes)
- ✓ 2 Resource models (SuccessResource, ErrorResource)
- ✓ 3 Optional schemas (ErrorLogs, ErrorMetrics, AuditTrails)
- ✓ 1 ErrorCodeRegistry (in-memory)

### API Contracts Specified
- ✓ Success response format (200/201/204)
- ✓ Error response format (400/401/403/404/422/429/500/503)
- ✓ 6 detailed example responses
- ✓ Correlation ID header protocol
- ✓ 12 error codes with fixed HTTP status

### Middleware Pipeline Order
1. InjectCorrelationId (first)
2. Auth (Laravel built-in)
3. RBAC (per-route, policies)
4. LogApiActivity (start)
5. Route Handler / Exception
6. Exception Handler (catches)
7. LogApiActivity (end/duration)

### File Structure to Create
- **Backend:** 14 files
  - Enums: 1 (ErrorCode)
  - Exceptions: 7 (custom hierarchy)
  - Http: 3 (ApiResponse trait, 2 Resources)
  - Middleware: 3 (CorrelationId, Logging, Filtering)
  - Services: 2 (Logging, Registry)
  - Migrations: 1 (optional)
- **Frontend:** 11 files
  - Composables: 2 (useApi, useErrorNotification)
  - Components: 1 (AppErrorBoundary)
  - Pages: 3 (404, 500, 403)
  - Layouts: 1 (error)
  - Stores: 1 (error Pinia)
  - Types: 1 (errors)
  - Middleware: 1 (errorHandler)

### Implementation Phases
1. **Phase 1:** Backend Exception Infrastructure (2 days)
   - ErrorCode enum
   - Exception hierarchy
   - ApiResponse trait
   - Handler updates

2. **Phase 2:** Backend Middleware & Logging (1 day)
   - InjectCorrelationId
   - LogApiActivity
   - Structured logging config
   - Middleware registration

3. **Phase 3:** Frontend Interceptor & Error Handling (1 day)
   - useApi composable
   - useErrorNotification composable
   - AppErrorBoundary component
   - Error pages
   - Error store

4. **Phase 4:** Localization/i18n (0.5 days)
   - Backend translations
   - Frontend translations
   - Form messages

5. **Phase 5:** Integration & E2E Testing (1 day)
   - Feature tests
   - Integration tests
   - Full-stack E2E

### Error Code Mapping
| Code | HTTP | Severity | Retryable |
|---|---|---|---|
| VALIDATION_ERROR | 422 | Warning | ✗ |
| AUTH_INVALID_CREDENTIALS | 401 | Warning | ✗ |
| AUTH_TOKEN_EXPIRED | 401 | Warning | ✗ |
| AUTH_UNAUTHORIZED | 401 | Warning | ✗ |
| RBAC_ROLE_DENIED | 403 | Warning | ✗ |
| RESOURCE_NOT_FOUND | 404 | Warning | ✗ |
| WORKFLOW_INVALID_TRANSITION | 422 | Warning | ✗ |
| WORKFLOW_PREREQUISITES_UNMET | 422 | Warning | ✗ |
| PAYMENT_FAILED | 422 | Error | ✓ |
| RATE_LIMIT_EXCEEDED | 429 | Warning | ✓ |
| SERVER_ERROR | 500 | Error | ✓ |
| SERVICE_UNAVAILABLE | 503 | Error | ✓ |

### RBAC Filtering Rules
- **All roles:** Error code, message, validation details, workflow details
- **Non-admin:** No stack trace, no internal details
- **Admin (dev only):** Stack trace, exception class, file/line
- **Admin (production):** No stack trace (even admin)

### Testing Approach
- Backend: Unit tests for exceptions (5+), feature tests for endpoints (7+)
- Frontend: Unit tests for composables (3+), component tests (2+)
- Integration: Full-stack error flows (5+)
- E2E: User-facing error scenarios (3+)

### Governance Compliance
- ✓ AGENTS.md: Error contract binding
- ✓ ADRs: Architecture decisions respected
- ✓ error-handling-patterns skill: Patterns applied
- ✓ i18n-governance skill: Arabic/RTL first
- ✓ DESIGN.md: Shadow-as-border, Geist fonts
- ✓ db-migration-governance: Migration discipline
- ✓ Conflict resolution: ADR > Specs > Plan

---

## How to Use These Documents

### For Architects
1. Read `plan.md` section 1 (Architecture Overview)
2. Review section 8 (Error Code Mapping Table)
3. Verify section 12 (Governance Compliance)

### For Backend Developers
1. Read `plan.md` section 4 (Exception Hierarchy)
2. Study `research.md` sections 1-3 (Laravel patterns, logging)
3. Follow `quickstart.md` Phase 1 (Bootstrap in 15 min)
4. Reference `contracts/laravel-exception-handler.md` while coding

### For Frontend Developers
1. Read `plan.md` section 3 (API Contracts)
2. Study `research.md` sections 4-7 (Interceptors, toast, i18n)
3. Follow `quickstart.md` Phase 2 (Bootstrap in 30 min)
4. Reference `research.md` section 4 (Error notification patterns)

### For QA/Testing
1. Read `plan.md` section 11 (Testing Strategy)
2. Create tests from `quickstart.md` Phase 3
3. Reference test examples in `research.md` section 11

### For Project Managers
1. Read this summary document
2. Review `plan.md` section 6 (Implementation Order)
3. Use `plan.md` section 14 (Success Metrics) for completion criteria

---

## Next Actions

1. **Review & Approval** (1 hour)
   - Stakeholders review all 5 documents
   - Clarify any open questions (5 from spec.md section 11)
   - Approve data models and API contracts

2. **Implementation** (4-5 days)
   - Backend team: Phases 1-2 (3 days)
   - Frontend team: Phases 3-4 (2 days)
   - Both: Phase 5 (1 day shared)

3. **Validation** (1 day)
   - Run all tests
   - Verify governance compliance
   - Merge to main branch

---

## Key Clarifications Awaiting Input

From `specs/runtime/005-error-handling/spec.md` section 11:

1. **Q1: Correlation ID Scope**
   - Request-level (current) or session-level?
   - Decision: Request-level (simpler for MVP)

2. **Q2: RBAC Error Detail Visibility**
   - Per-error-type specificity rules?
   - Decision: Follow matrix in plan.md section 9

3. **Q3: Toast Notification Behavior**
   - Queue vs Replace vs Stack?
   - Decision: Stack (show all errors simultaneously)

4. **Q4: Logging Destination**
   - File vs Stdout vs External Service?
   - Decision: File-based for MVP, expand later

5. **Q5: Error Retry Logic**
   - Which errors retryable? What strategy?
   - Decision: PAYMENT_FAILED, RATE_LIMIT_EXCEEDED, SERVICE_UNAVAILABLE only

---

## Authority & References

**Binding Authority:**
- AGENTS.md (error contract is binding)
- ADRs in docs/architecture/
- error-handling-patterns skill
- i18n-governance skill

**Documentation Standards:**
- DESIGN.md (Vercel-inspired visual language)
- CONTRIBUTING.md (code style)
- db-migration-governance skill
- api-testing-patterns skill

---

**Planning Complete:** 2026-04-11  
**Ready for Implementation:** Yes  
**Total Planning Time:** ~4 hours  
**Planning Documents:** 5 files + 1 contract  
**Total Pages:** ~45 pages of detailed specifications and guidance
