# Plan Report — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Generated:** 2026-04-11T00:00:00Z

## Plan Summary

| Metric                  | Value                                                 |
| ----------------------- | ----------------------------------------------------- |
| New Tables              | 1 (password_reset_tokens)                             |
| Modified Tables         | 0 (users, personal_access_tokens unchanged)           |
| New Endpoints           | 4 (forgot-password, reset-password, verify, resend)   |
| Modified Endpoints      | 5 (login, register, logout, profile, update)          |
| New Services            | 0 (AuthService enhanced, not new)                     |
| New Repositories        | 1 (UserRepository)                                    |
| New Form Requests       | 2 (ForgotPasswordRequest, ResetPasswordRequest)       |
| Modified Form Requests  | 2 (LoginRequest, RegisterRequest)                     |
| New Frontend Pages      | 4 (register, forgot-password, reset-password, verify) |
| Modified Frontend Pages | 1 (login rewrite)                                     |
| New Frontend Files      | 1 (app.config.ts)                                     |
| Backend Test Files      | 2 (unit + feature)                                    |
| Frontend Test Files     | 2 (store + middleware)                                |

## Implementation Phases

| Phase                | Focus                       | Tasks | Risk   |
| -------------------- | --------------------------- | ----- | ------ |
| A — Backend Refactor | Fix architecture violations | 8     | MEDIUM |
| B — Backend Extend   | Add new features            | 7     | MEDIUM |
| C — Frontend Fix     | Resolve inconsistencies     | 4     | LOW    |
| D — Frontend Build   | Build auth pages            | 7     | LOW    |
| E — Testing          | Comprehensive test suite    | 3     | LOW    |

## Architecture Decisions

1. **Refactor-before-extend:** Fix controller business logic duplication before adding new features to prevent propagating the anti-pattern
2. **Repository pattern:** Create `UserRepository` for consistent data access across auth flows
3. **Cookie-based token storage:** Use `useCookie()` for SSR-compatible token persistence (not localStorage)
4. **Role restriction:** Public registration creates `customer` only — admin assigns other roles
5. **Soft verification:** Email verification is non-blocking — users can access platform without verification
6. **User enumeration prevention:** Forgot password always returns success, even for non-existent emails

## Guardian Verdicts

| Guardian              | Verdict | Notes                                                                                                                                   |
| --------------------- | ------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| Architecture Guardian | PASS    | Plan follows thin controller → service → repository pattern. RBAC enforced via Sanctum middleware. Error contract maintained.           |
| API Designer          | PASS    | All endpoints follow RESTful conventions under `/api/v1/auth/*`. Rate limiting applied. Response format consistent with error contract. |

## Risk Assessment

| Risk Level | Count | Details                                                                 |
| ---------- | ----- | ----------------------------------------------------------------------- |
| HIGH       | 0     | —                                                                       |
| MEDIUM     | 3     | Backend refactor (test breakage), email delivery config, token security |
| LOW        | 2     | Rate limit tuning, role enum migration blast radius                     |

## Artifacts Generated

| Artifact     | Path                    |
| ------------ | ----------------------- |
| Research     | `research.md`           |
| Data Model   | `data-model.md`         |
| API Contract | `contracts/auth-api.md` |
| Plan         | `plan.md`               |
| Quickstart   | `quickstart.md`         |
