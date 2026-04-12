# Implement Report — STAGE_30 Auth Pages (copilot / incremental)

**Date:** 2026-04-12  
**Mode:** Copilot + autopilot — stage closed at **46/46** tasks

## Summary

Implemented and hardened the **existing** Nuxt auth surface to match the stage plan: shared Zod schemas, reusable auth UI components, locale-aware routing (`useLocalePath`), protected **profile** and **dashboard** stubs, `PUT /v1/auth/profile` via `useAuthStore.updateProfile`, and Vitest coverage for schemas + `PasswordStrength`.

## Autopilot increment (Step 6 continuation)

| Area         | Change                                                                                                                                                                                                        |
| ------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `useAuthApi` | `frontend/composables/useAuthApi.ts` — typed login/register/forgot/reset/verifyEmail/getProfile/updateProfile/resend/logout; auth pages + Pinia store call into it                                            |
| Auth store   | `frontend/stores/auth.ts` — delegates HTTP to `useAuthApi`                                                                                                                                                    |
| Auth layout  | `frontend/components/auth/AuthLayout.vue` — RTL `dir` from locale, min-height, padding; `layouts/auth.vue` wraps brand + `UCard` + footer                                                                     |
| Register     | Multi-step wizard (`useState('auth-register-wizard')`), Zod per-step validation, step 4 resend + link to verify-email; `useAuth().register(..., { skipPostRegisterNavigation: true })` for in-flow completion |
| Schemas      | Wizard schemas + merged credentials schema (`registerWizardCredentialsSchema`)                                                                                                                                |
| RoleSelector | Customer/contractor descriptions (AR/EN i18n), `selectRole`, `data-testid` for E2E                                                                                                                            |
| i18n         | Wizard copy, role descriptions, validation strings                                                                                                                                                            |
| Tests        | Vitest: `useAuthApi.spec`, `stores/auth.spec`, `auth` component spec, extended `schemas/auth.spec`; Playwright: `auth.spec.ts` (login shell + password toggle + wizard steps 1–3), `middleware.spec.ts`       |
| UX           | `data-testid="auth-error-alert"` on login for future stricter E2E                                                                                                                                             |

## Delivered (earlier increment)

| Area       | Change                                                                                                                                                                                       |
| ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Schemas    | `frontend/schemas/auth.ts` — login, register, forgot, reset, profile update                                                                                                                  |
| Components | `AuthCard`, `PasswordStrength`, `RoleSelector`, `OtpInput` under `frontend/components/auth/`                                                                                                 |
| Pages      | Refactored `login`, `register`, `forgot-password`, `reset-password`, `verify-email` to use schemas + `AuthCard` + locale paths; added `pages/profile/index.vue`, `pages/dashboard/index.vue` |
| Store      | `updateProfile` on `useAuthStore`; `useAuth().updateProfile` wrapper                                                                                                                         |
| Navigation | `useAuth`, `useApi`, `middleware/auth`, `AppUserMenu`, home `index` — `useLocalePath` instead of hardcoded `/ar/...`                                                                         |
| i18n       | New keys for password strength, profile, dashboard, remember-me labels                                                                                                                       |
| Tests      | `tests/unit/schemas/auth.spec.ts`, `tests/unit/components/PasswordStrength.spec.ts`; `tests/setup.ts` stubs `useLocalePath`                                                                  |

## Residual / follow-up (optional)

- **Vitest coverage %:** add `@vitest/coverage-v8` + CI threshold if product requires numeric enforcement (`VERIFICATION.md` notes).
- **E2E depth:** some flows use mocked `**/v1/...` and `**/api/v1/...` patterns; full login→dashboard against a live API is covered in `guides/TESTING_GUIDE.md` manually.
- **T019:** wizard persistence uses **`useState`**, not Pinia — acceptable per runtime report; migrate only if product requires Pinia-only persistence.

## Task checklist

**46 / 46** tasks marked complete in `tasks.md` (final increment: `useUserStore`, design/responsive/error polish, E2E `rtl`/`accessibility`/`i18n`, docs, verification).

## Final increment (closure batch)

- `frontend/stores/user.ts` — `fetchProfile` / `updateProfile` + profile mirror; profile page uses store + **Cancel** (`profile.cancel` i18n).
- T022–T025: `AuthLayout` / `AuthCard` / layout `UCard` responsive widths; `role="alert"` on auth error alerts; typography tracking.
- Vitest `user.spec.ts`; Playwright `rtl.spec.ts`, `accessibility.spec.ts`, `i18n.spec.ts`; expanded `auth.spec.ts` (forgot/reset/profile/verify/cookie smoke).
- `IMPLEMENTATION_GUIDE.md`, `VERIFICATION.md`, `guides/TESTING_GUIDE.md`, `reports/CLOSURE_REPORT.md`, `PR_SUMMARY.md`.
