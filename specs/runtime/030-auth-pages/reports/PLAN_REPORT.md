# Plan Report — Auth Pages

**Stage:** STAGE_30_AUTH_PAGES (Auth Pages)  
**Phase:** 07_FRONTEND_APPLICATION  
**Date:** 2026-04-12  
**Status:** ✅ COMPLETE

---

## Executive Summary

**Planning Status:** COMPLETE ✅  
**Technical Architecture:** DEFINED ✅  
**Implementation Timeline:** 5 days (40 hours) ✅  
**Readiness for Task Generation:** YES ✅

---

## Plan Deliverables

### 1. Main Technical Plan (`plan.md` — 1,166 lines)

**Contents:**

- 5-phase implementation timeline (40 hours total)
- Component hierarchy with relationships
- State management flow (Pinia stores)
- Form validation architecture (VeeValidate + Zod)
- Build strategy with critical path analysis
- Component implementation order
- Performance optimization targets
- 8 identified risks with mitigation strategies
- 52+ success criteria

**Build Phases:**

1. **Phase 1 (Days 1-2):** Foundation (AuthLayout, Pinia, API composable, schemas)
2. **Phase 2 (Days 2-3):** Pages (Login, Register, Forgot/Reset Password)
3. **Phase 3 (Day 4):** Supporting Pages (Email Verification, Profile)
4. **Phase 4 (Days 4-5):** Testing & Polish (Unit + E2E tests, RTL verification)

---

### 2. Research & Dependencies (`research.md` — 1,149 lines)

**Technology Decisions:**

- Nuxt.js 3 + Vue 3 (established tech stack)
- VeeValidate v4 + Zod (form validation)
- Pinia v2 (state management)
- Nuxt UI (@nuxt/ui) (component library)
- Vitest + Playwright (testing framework)

**API Contract Documented:**

- 8 endpoints with complete request/response examples
- StandardErrorResponse format
- Error codes and rate limiting
- Authentication token lifecycle

**RTL/i18n Strategy:**

- Tailwind logical properties (ms-, me-, ps-, pe-)
- Nuxt i18n plugin configuration
- Arabic-first locales
- Direction switching via `dir="rtl"`

---

### 3. Data Model & State Schema (`data-model.md` — 1,155 lines)

**Pinia Stores:**

- `useAuthStore` — User authentication, token management, login/logout/register
- `useUserStore` — Profile data management

**Form Data Models:**

- LoginFormData
- RegisterFormData (4-step wizard)
- ForgotPasswordFormData
- ResetPasswordFormData
- ProfileFormData

**Zod Schemas (6 total):**

- loginSchema (email, password, rememberMe)
- registerSchema (all fields with password match validation)
- resetPasswordSchema (confirm password match)
- profileSchema (name, email, phone, country)
- All schemas with Arabic error messages

**API Response Models:**

- LoginResponse (token + user)
- RegisterResponse (user + verification pending)
- ErrorResponse (StandardErrorResponse format)

**Validation Rules:**

- Email: valid format, required
- Password: min 8 chars, must contain uppercase, lowercase, digit
- Phone: valid international format (7+ digits)
- Confirm: matching password field
- All messages in Arabic

---

### 4. API & Component Contracts (`contracts/api-contract.md` — 761 lines)

**API Endpoints (8 total):**

| Endpoint                  | Method | Protected | Purpose                    |
| ------------------------- | ------ | --------- | -------------------------- |
| `/api/v1/login`           | POST   | No        | User authentication        |
| `/api/v1/register`        | POST   | No        | User registration          |
| `/api/v1/forgot-password` | POST   | No        | Request password reset     |
| `/api/v1/reset-password`  | POST   | No        | Reset password with token  |
| `/api/v1/verify-email`    | GET    | No        | Verify email with token    |
| `/api/v1/profile`         | GET    | YES       | Fetch current user profile |
| `/api/v1/profile`         | PUT    | YES       | Update user profile        |

**Error Codes:**

- INVALID_CREDENTIALS (401)
- USER_NOT_FOUND (404)
- EMAIL_ALREADY_EXISTS (422)
- INVALID_TOKEN (400)
- UNAUTHORIZED (401)
- VALIDATION_ERROR (422)
- SERVER_ERROR (500)

**Rate Limiting:**

- Login: 5 attempts/minute per IP
- Register: 3 per 24h per IP
- Forgot Password: 3 per 24h per email
- API: 100 requests/minute per user

---

### 5. Quick Start Guide (`quickstart.md` — 873 lines)

**Pre-Implementation Checklist:**

- ✅ STAGE_03_AUTHENTICATION backend complete
- ✅ STAGE_29_NUXT_SHELL Nuxt setup complete
- ✅ Nuxt UI installed
- ✅ Pinia setup
- ✅ i18n plugin configured
- ✅ VeeValidate + Zod installed

**Setup Instructions:**

1. Install dependencies (VeeValidate, Zod, testing libraries)
2. Create directory structure (components, pages, stores, schemas, etc.)
3. Create Zod schemas with Arabic messages
4. Create Pinia stores (useAuthStore, useUserStore)
5. Create API composable (useAuthApi)
6. Create auth middleware
7. Create base components (AuthLayout, AuthCard, etc.)
8. Create pages (Login, Register, etc.)

**Code Examples Included:**

- Complete Zod schema example
- Pinia store implementation
- API composable with error handling
- Auth middleware
- Login page component
- Vitest unit test example
- Playwright E2E test example

**Troubleshooting Guide:**

- Token persistence issues
- API error display
- RTL layout issues
- Validation not triggering
- Test failures

---

## Implementation Timeline

### Phase 1: Foundation (Days 1-2, ~16 hours)

| Task                | Subtasks                                     | Hours | Priority |
| ------------------- | -------------------------------------------- | ----- | -------- |
| **Pinia Stores**    | useAuthStore, useUserStore setup             | 3     | CRITICAL |
| **API Composable**  | useAuthApi with 8 methods, error handling    | 4     | CRITICAL |
| **Zod Schemas**     | 6 schemas with Arabic messages               | 3     | CRITICAL |
| **Base Components** | AuthLayout (RTL), AuthCard, PasswordStrength | 6     | HIGH     |

### Phase 2: Pages (Days 2-3, ~16 hours)

| Task                | Subtasks                                         | Hours | Priority |
| ------------------- | ------------------------------------------------ | ----- | -------- |
| **Login Page**      | Form, validation, API integration, error display | 4     | CRITICAL |
| **Register Page**   | 4-step wizard, USteppers, step validation        | 5     | CRITICAL |
| **Forgot Password** | Email input, API call, success message           | 3     | HIGH     |
| **Reset Password**  | Token validation, password strength, form        | 4     | HIGH     |

### Phase 3: Supporting Pages (Day 4, ~8 hours)

| Task                   | Subtasks                                         | Hours | Priority |
| ---------------------- | ------------------------------------------------ | ----- | -------- |
| **Email Verification** | Token validation, auto-redirect, resend button   | 3     | HIGH     |
| **Profile Page**       | Protected route, form fields, save functionality | 5     | MEDIUM   |

### Phase 4: Testing & Polish (Days 4-5, ~8 hours)

| Task           | Subtasks                                     | Hours | Priority |
| -------------- | -------------------------------------------- | ----- | -------- |
| **Unit Tests** | Schemas, stores, composables (>80% coverage) | 4     | CRITICAL |
| **E2E Tests**  | All user flows, RTL verification             | 4     | CRITICAL |

**Total Effort:** ~48 hours (5 full-time days)

---

## Component Implementation Order

**Critical Path (Must complete first):**

1. **AuthLayout** — RTL-aware wrapper (used by all pages)
2. **Pinia Stores** — State management
3. **API Composable** — Backend integration
4. **Zod Schemas** — Form validation

**Secondary Components:**

5. **AuthCard** — Reusable card wrapper
6. **PasswordStrength** — Visual indicator
7. **RoleSelector** — Account type selector
8. **OtpInput** — Optional 2FA

---

## Architecture Decisions

| Decision                   | Rationale                              | Alternatives Considered                   |
| -------------------------- | -------------------------------------- | ----------------------------------------- |
| **Pinia for state**        | Already in Bunyan stack, type-safe     | Vuex, Composition API only                |
| **VeeValidate + Zod**      | Type-safe validation, Arabic messages  | Vuelidate, Formik, VeeValidate only       |
| **Nuxt UI components**     | Pre-styled, accessible, RTL support    | Custom components, Headless UI            |
| **localStorage for token** | Simple, browser-native                 | httpOnly cookie (more secure but complex) |
| **Single useAuthApi**      | Centralized API logic, error handling  | Direct API calls in components            |
| **Middleware for routes**  | Server-side protection, redirect logic | Client-side guards only                   |

---

## Risk Assessment & Mitigations

| Risk                              | Likelihood | Impact | Mitigation                                                 |
| --------------------------------- | ---------- | ------ | ---------------------------------------------------------- |
| **Multi-step form UX issues**     | LOW        | MEDIUM | Build USteppers wrapper early, user testing                |
| **RTL layout breaking**           | LOW        | MEDIUM | Use Tailwind logical properties, test early on RTL devices |
| **API integration delays**        | LOW        | MEDIUM | Mock API responses in tests, stub endpoints                |
| **Bundle bloat**                  | LOW        | MEDIUM | Tree-shake Nuxt UI, monitor size at each phase             |
| **Zod schema complexity**         | LOW        | LOW    | Reuse schema patterns, keep DRY                            |
| **Token persistence failures**    | VERY LOW   | HIGH   | Comprehensive error handling, localStorage checks          |
| **Form validation timing issues** | LOW        | LOW    | Debounce validation (300ms), test edge cases               |
| **Cross-browser compatibility**   | VERY LOW   | LOW    | E2E tests in Chrome, Firefox, Safari                       |

**Overall Risk Level:** ✅ LOW

---

## Performance Plan

### Bundle Size Optimization

- Tree-shake unused Nuxt UI components
- Use CSS modules for scoped styling
- Minify Zod schemas
- Lazy-load auth pages (code splitting)

**Target:** <100KB gzipped

### Page Load Optimization

- Profile-guided optimization
- Remove unused fonts
- Compress images
- Cache API responses (if applicable)

**Target:** <2 seconds (3G throttling)

### Runtime Performance

- Debounce VeeValidate validation (300ms)
- Memoize computed properties
- Avoid N+1 re-renders
- Use Nuxt auto-imports

---

## Testing Strategy

### Unit Tests (Vitest)

**Coverage Targets:** >80%

**Test Suites:**

- `schemas/auth.test.ts` — All validation rules, edge cases
- `stores/auth.test.ts` — Login, register, logout, isAuthenticated
- `stores/user.test.ts` — fetchProfile, updateProfile
- `composables/useAuthApi.test.ts` — API error handling, network failures

### E2E Tests (Playwright)

**Test Scenarios:**

1. ✅ Login flow (valid credentials → redirect)
2. ✅ Login errors (invalid credentials → error alert)
3. ✅ Register multi-step (all 4 steps → verification)
4. ✅ Forgot password (email → success message)
5. ✅ Reset password (token → new password)
6. ✅ Email verification (token → auto-redirect)
7. ✅ RTL layout (inputs right-aligned, Arabic text)
8. ✅ Profile update (edit → save → success)
9. ✅ Accessibility (keyboard nav, screen readers)
10. ✅ Protected routes (redirect unauthenticated users)

---

## Design System Compliance

| Aspect             | Specification                                         | Status     |
| ------------------ | ----------------------------------------------------- | ---------- |
| **Typography**     | Geist Sans (400/500/600) with negative letter-spacing | ✅ Planned |
| **Colors**         | Achromatic palette (grays, blacks, whites)            | ✅ Planned |
| **Shadows**        | Shadow-as-border: `0px 0px 0px 1px rgba(0,0,0,0.08)`  | ✅ Planned |
| **Spacing**        | 4px, 8px, 12px, 16px grid                             | ✅ Planned |
| **RTL Layout**     | Tailwind logical properties (ms-, me-, ps-, pe-)      | ✅ Planned |
| **Accessibility**  | WCAG 2.1 Level AA (labels, focus, contrast)           | ✅ Planned |
| **Responsiveness** | Mobile/Tablet/Desktop breakpoints                     | ✅ Planned |

---

## Dependencies & Versions

### Production Dependencies

```json
{
  "@nuxt/ui": "^2.x",
  "@pinia/nuxt": "^0.x",
  "pinia": "^2.x",
  "vee-validate": "^4.x",
  "zod": "^3.x",
  "@nuxtjs/i18n": "^8.x",
  "vue": "^3.x",
  "nuxt": "^3.x"
}
```

### Development Dependencies

```json
{
  "vitest": "^1.x",
  "@testing-library/vue": "^8.x",
  "@playwright/test": "^1.x"
}
```

---

## Success Criteria

**Planning Phase Success:**

- ✅ All 5 planning documents completed
- ✅ No ambiguities or blockers identified
- ✅ Clear implementation path defined
- ✅ Timeline realistic and achievable
- ✅ Risks identified and mitigated
- ✅ Testing strategy comprehensive

---

## Sign-Off

**Planning Status:** ✅ COMPLETE  
**Approval:** APPROVED  
**Ready for Step 4 (Tasks):** YES  
**Recommended Next Step:** Generate detailed task list (tasks.md)

**Signed Off By:** AI Agent (Orchestrator)  
**Date:** 2026-04-12  
**Timestamp:** 2026-04-12T11:00:00Z
