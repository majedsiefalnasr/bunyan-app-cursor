# STAGE_30 — Auth Pages Implementation Guide

**Branch:** `spec/030-auth-pages`  
**Last updated:** 2026-04-12

## Architecture

| Layer          | Responsibility                                                                                                                                                |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Pages**      | `frontend/pages/auth/*`, `frontend/pages/profile/index.vue` — Nuxt UI forms, `definePageMeta`, locale-aware links via `useLocalePath`                         |
| **Layouts**    | `frontend/layouts/auth.vue` — wraps content in `AuthLayout` + `UCard`                                                                                         |
| **Components** | `frontend/components/auth/*` — `AuthCard`, `AuthLayout`, `PasswordStrength`, `RoleSelector`, `OtpInput`                                                       |
| **State**      | `useAuthStore` (session + `login`/`register`/`fetchUser`/`updateProfile`/`logout`), `useUserStore` (profile mirror + `fetchProfile`/`updateProfile` for T001) |
| **HTTP**       | `useAuthApi` composable — typed calls; `useApi` attaches Bearer token and handles 401/403                                                                     |

## API integration

- Base URL: `runtimeConfig.public.apiBaseUrl` (see `frontend/.env.example`).
- Endpoints: `/v1/auth/login`, `/register`, `/forgot-password`, `/reset-password`, `/email/resend`, `/email/verify/{id}/{hash}` (GET, signed), `/profile` (GET/PUT), `/logout` (POST).
- Errors: Laravel `StandardErrorResponse` shape (`success`, `error.code`, `error.message`).

## Component usage

- Wrap auth forms in `AuthCard` inside layout `UCard` for consistent title/description + touch-friendly inputs (`min-h-11` via `AuthCard` root classes).
- Multi-step register uses `useState('auth-register-wizard')` for SSR-safe wizard persistence.
- Profile page calls `useUserStore().fetchProfile()` on mount and `updateProfile` on save; **Cancel** resets fields from the latest loaded profile.

## Commands

```bash
cd frontend
npm install
npm run lint
npm run typecheck
npm run test
npm run build
PLAYWRIGHT_TEST=1 npm run test:e2e -- --project=chromium
```

## Build / performance note (T042)

`npm run build` reports **total** Nitro output on the order of **~1.1 MB gzip** for the full app — above the original “&lt;100 KB gzip” aspirational target, which applies to the **entire** Nuxt bundle, not auth alone. Auth route chunks (example: `login` server chunk ~**4.4 kB gzip** in a local build) are suitable for per-route budgeting in future CI gates.
