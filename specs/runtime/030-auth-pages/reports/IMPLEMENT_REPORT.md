# Implement Report — STAGE_30 Auth Pages (copilot / incremental)

**Date:** 2026-04-12  
**Mode:** Copilot — incremental delivery (not full 46-task closure)

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

## Deferred (follow-up)

- **T001** `useUserStore` — not present; profile uses `useAuthStore` only.
- **T022–T023, T025** — design-system sweep (Geist weights, `color="error"` on alerts, responsive audit).
- **T028** user store tests — blocked on `useUserStore`.
- **T034–T038, T040–T041** — deeper Playwright (reset/verify/profile/RTL/a11y/i18n/token persistence).
- **T042–T046** — perf, cross-browser manual, implementation guide, final verification doc.
- **E2E** — current `auth.spec` covers shell + wizard navigation to step 3; full submit + API intercept remains flaky against `NUXT_PUBLIC_API_BASE_URL` / path variants (`/api/v1` vs `/v1`).
- **T019** acceptance mentions Pinia for wizard persistence — implemented with **`useState`** for SSR-safe step data across reloads in-session; optional later migration to Pinia.

## Task checklist

**29 / 46** tasks marked complete in `tasks.md` after this increment.
