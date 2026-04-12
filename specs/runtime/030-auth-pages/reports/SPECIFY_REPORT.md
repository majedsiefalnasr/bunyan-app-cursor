# Specify Report — Auth Pages

**Stage:** STAGE_30_AUTH_PAGES (Auth Pages)  
**Phase:** 07_FRONTEND_APPLICATION  
**Date:** 2026-04-12  
**Status:** ✅ COMPLETE

---

## Executive Summary

**Specification Status:** COMPLETE ✅  
**Scope Definition:** LOCKED ✅  
**Ambiguities Resolved:** YES ✅  
**Readiness for Planning:** YES ✅

---

## Specification Deliverables

### 1. Main Specification Document

**Location:** `specs/runtime/030-auth-pages/spec.md`

**Contents:**

- **Objective:** Implement all authentication-related frontend pages with form validation and RTL support
- **Scope Extent:** 6 pages + 5 shared components
- **Pages:** Login, Register (multi-step), Forgot Password, Reset Password, Email Verification, Profile
- **Shared Components:** AuthLayout, AuthCard, PasswordStrength, RoleSelector, OtpInput
- **Validation Strategy:** VeeValidate + Zod with Arabic error messages
- **State Management:** Pinia (useAuthStore, useUserStore)
- **Design System:** Vercel-inspired (Geist fonts, shadow-as-border, achromatic palette)
- **RTL Support:** Full Arabic-first layout with Tailwind logical properties

---

## Scope Analysis

### In Scope ✅

| Category            | Items                                  | Status |
| ------------------- | -------------------------------------- | ------ |
| **Pages**           | 6 pages specified                      | ✅     |
| **Components**      | 5 shared components                    | ✅     |
| **Form Validation** | VeeValidate + Zod schemas              | ✅     |
| **Pinia Stores**    | useAuthStore, useUserStore             | ✅     |
| **API Integration** | 8 endpoints documented                 | ✅     |
| **Testing**         | Unit (Vitest) + E2E (Playwright)       | ✅     |
| **Design System**   | Full Geist/shadow-as-border compliance | ✅     |
| **RTL/i18n**        | Arabic-first with WCAG 2.1 AA          | ✅     |
| **Performance**     | Bundle <100KB, load <2s targets        | ✅     |

### Out of Scope ✅

- Backend API implementation (delegated to STAGE_03_AUTHENTICATION)
- Advanced 2FA/MFA flows (optional future enhancement)
- Social authentication backend integration (frontend placeholders only)
- Password strength API scoring (client-side algorithm only)

---

## Requirement Categories

### 1. Functional Requirements

| Requirement            | Specification                                         | Status       |
| ---------------------- | ----------------------------------------------------- | ------------ |
| **Login Page**         | Email + password + remember me                        | ✅ Specified |
| **Register Page**      | 4-step wizard (type → personal → contact → verify)    | ✅ Specified |
| **Forgot Password**    | Email input → reset link                              | ✅ Specified |
| **Reset Password**     | Token validation → new password → strength indicator  | ✅ Specified |
| **Email Verification** | Confirmation page with resend option                  | ✅ Specified |
| **Profile Page**       | Edit name, email, phone, country (protected)          | ✅ Specified |
| **Form Validation**    | Client-side (Zod) + server-side errors                | ✅ Specified |
| **Auth Middleware**    | Protected routes redirect unauthenticated users       | ✅ Specified |
| **Token Management**   | Login stores token, logout clears token               | ✅ Specified |
| **Error Handling**     | Arabic error messages, StandardErrorResponse contract | ✅ Specified |

### 2. Non-Functional Requirements

| Requirement           | Target                                                     | Status       |
| --------------------- | ---------------------------------------------------------- | ------------ |
| **RTL Support**       | Full Arabic-first layout                                   | ✅ Specified |
| **Accessibility**     | WCAG 2.1 Level AA                                          | ✅ Specified |
| **Performance**       | < 2s page load (3G), < 100KB bundle                        | ✅ Specified |
| **Browser Support**   | Modern browsers (Chrome, Firefox, Safari, Edge)            | ✅ Specified |
| **Responsiveness**    | Mobile (<768px), Tablet (768-1024px), Desktop (>1024px)    | ✅ Specified |
| **Design System**     | Vercel (Geist fonts, shadow-as-border, achromatic palette) | ✅ Specified |
| **Component Library** | Nuxt UI (@nuxt/ui) mandatory                               | ✅ Specified |
| **State Library**     | Pinia mandatory                                            | ✅ Specified |
| **Form Validation**   | VeeValidate + Zod mandatory                                | ✅ Specified |
| **i18n**              | Arabic/English with locale switching                       | ✅ Specified |

### 3. Technical Architecture

| Layer              | Specification                                    | Status       |
| ------------------ | ------------------------------------------------ | ------------ |
| **Pages**          | 6 Vue components with routing                    | ✅ Specified |
| **Components**     | 5 reusable Nuxt UI wrappers                      | ✅ Specified |
| **Schemas**        | Zod schemas with Arabic validation messages      | ✅ Specified |
| **Pinia Stores**   | useAuthStore, useUserStore with actions/computed | ✅ Specified |
| **API Composable** | useAuthApi with 8 methods                        | ✅ Specified |
| **Middleware**     | auth.ts for protected routes                     | ✅ Specified |
| **Layouts**        | AuthLayout (RTL) for all auth pages              | ✅ Specified |
| **Error Handling** | StandardErrorResponse contract                   | ✅ Specified |
| **Token Storage**  | localStorage (or httpOnly cookie preference)     | ✅ Specified |

---

## Nuxt UI Component Mapping

| Form Element        | Nuxt UI Component                   | Purpose                            | Status |
| ------------------- | ----------------------------------- | ---------------------------------- | ------ |
| Container           | `UCard`                             | Page wrapper with shadow-as-border | ✅     |
| Form wrapper        | `UForm`                             | VeeValidate integration            | ✅     |
| Field label + input | `UFormField` + `UInput`             | Form fields with error display     | ✅     |
| Email input         | `UInput` (type="email")             | Email validation                   | ✅     |
| Password input      | `UInput` (type="password") + toggle | Show/hide toggle                   | ✅     |
| Submit button       | `UButton`                           | Loading state, disabled state      | ✅     |
| Multi-step wizard   | `USteppers`                         | Register progress                  | ✅     |
| Account type        | `URadioGroup`                       | Customer/Contractor selection      | ✅     |
| OTP input           | `UPinInput`                         | 6-digit code (future 2FA)          | ✅     |
| Password strength   | `UProgress`                         | Visual strength indicator          | ✅     |
| Alerts/Errors       | `UAlert`                            | Error/success/info messages        | ✅     |
| Toggle              | `UCheckbox`                         | "Remember me" checkbox             | ✅     |

---

## Form Validation Specifications

### Zod Schemas

```typescript
// Login Schema
{
  email: required, valid email format
  password: required, min 8 characters
  rememberMe: optional boolean
}

// Register Schema (4 steps)
{
  accountType: required enum (customer | contractor)
  firstName: required, min 2 characters
  lastName: required, min 2 characters
  email: required, valid email format
  phone: required, valid phone format (7+ digits)
  country: required, min 2 characters
  password: required, min 8 characters
  confirmPassword: required, must match password
}

// Password Reset Schema
{
  newPassword: required, min 8 characters
  confirmPassword: required, must match newPassword
}

// Profile Schema
{
  firstName: required, min 2 characters
  lastName: required, min 2 characters
  email: required, valid email format
  phone: required, valid phone format
  country: required, min 2 characters
}
```

### Validation Messaging

- **Language:** Arabic-first (English fallback)
- **Field-level errors:** Display below each input in red
- **Form-level errors:** Display in UAlert at top of form
- **Real-time feedback:** Validate on blur + input (debounced)
- **Example:** `email: z.string().email("البريد الإلكتروني غير صالح")`

---

## Pinia Store Structure

### useAuthStore

```typescript
State:
  - user: object | null
  - token: string | null
  - isLoading: boolean

Computed:
  - isAuthenticated: boolean

Actions:
  - login(email, password): Promise<void>
  - register(data): Promise<void>
  - logout(): void
  - fetchUser(): Promise<void>
```

### useUserStore

```typescript
State:
  - profile: object | null
  - isLoading: boolean

Actions:
  - fetchProfile(): Promise<void>
  - updateProfile(data): Promise<void>
```

---

## Testing Specification

### Unit Tests (Vitest)

**Test Suites:**

1. Zod schema validation (email, password, required fields, pattern matching)
2. Pinia store actions (login, register, logout, isAuthenticated)
3. API composable error handling (network errors, validation errors)

**Coverage Target:** > 80%

### E2E Tests (Playwright)

**Test Scenarios:**

1. Login flow (valid credentials → redirect to /dashboard)
2. Login errors (invalid credentials → error UAlert in Arabic)
3. Registration multi-step (all 4 steps → email verification)
4. Password reset (forgot email → link → new password)
5. Email verification (token validation → auto-redirect)
6. RTL layout (inputs right-aligned, errors in Arabic)
7. Profile update (edit → save → success notification)
8. Accessibility (keyboard navigation, screen reader support)
9. Protected routes (unauthenticated redirect to /auth/login)

---

## Design System Compliance Checklist

| Aspect             | Specification                                          | Status |
| ------------------ | ------------------------------------------------------ | ------ |
| **Typography**     | Geist fonts (400/500/600) with negative letter-spacing | ✅     |
| **Colors**         | Achromatic palette (grays, blacks, whites)             | ✅     |
| **Shadows**        | Shadow-as-border: `0px 0px 0px 1px rgba(0,0,0,0.08)`   | ✅     |
| **Spacing**        | 4px, 8px, 12px, 16px grid                              | ✅     |
| **Radius**         | Max 8px border-radius                                  | ✅     |
| **RTL Layout**     | Tailwind logical properties (ms-, me-, ps-, pe-)       | ✅     |
| **Responsiveness** | Mobile/Tablet/Desktop breakpoints                      | ✅     |
| **Accessibility**  | WCAG 2.1 Level AA (labels, focus, contrast)            | ✅     |

---

## Dependencies Analysis

### Upstream Dependencies (Blocking)

| Stage                       | Purpose                                                | Impact                        |
| --------------------------- | ------------------------------------------------------ | ----------------------------- |
| **STAGE_03_AUTHENTICATION** | Backend API endpoints (login, register, verify, reset) | CRITICAL: Must complete first |
| **STAGE_29_NUXT_SHELL**     | Nuxt app setup, routing, Nuxt UI configuration         | CRITICAL: Must complete first |

### Downstream Dependencies (Blocked by this stage)

- All authenticated pages (dashboards, project management, reporting)
- User profile pages
- Admin dashboards

---

## Risk Assessment

| Risk                       | Likelihood | Impact | Mitigation                                  |
| -------------------------- | ---------- | ------ | ------------------------------------------- |
| Complex multi-step form UX | LOW        | MEDIUM | Spec includes detailed step validation      |
| RTL layout issues          | LOW        | MEDIUM | Use Tailwind logical properties, test early |
| Zod schema complexity      | LOW        | MEDIUM | Reuse schema patterns across pages          |
| Performance bundle bloat   | LOW        | MEDIUM | Target <100KB, tree-shake unused components |
| API integration failures   | LOW        | MEDIUM | Comprehensive error handling specified      |

**Overall Risk Level:** LOW ✅

---

## Success Criteria

- ✅ All 6 pages implemented with Nuxt UI components
- ✅ All forms validated with Zod + VeeValidate
- ✅ Pinia stores manage auth state correctly
- ✅ All error messages in Arabic
- ✅ RTL layout fully functional
- ✅ Unit tests pass (>80% coverage)
- ✅ E2E tests pass (all scenarios)
- ✅ Bundle size <100KB (gzipped)
- ✅ Page load <2s (3G throttling)
- ✅ Design system compliant
- ✅ WCAG 2.1 Level AA verified
- ✅ Zero console errors/warnings

---

## Sign-Off

**Specification Status:** ✅ COMPLETE  
**Clarifications Required:** NO  
**Ready for Step 2 (Clarify):** YES  
**Recommended Next Step:** Proceed directly to **Step 3 (Plan)** (no ambiguities detected)

**Signed Off By:** AI Agent (Orchestrator)  
**Date:** 2026-04-12  
**Timestamp:** 2026-04-12T10:28:00Z
