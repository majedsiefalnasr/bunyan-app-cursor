# Security Checklist — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Created:** 2026-04-11T00:00:00Z

## Authentication & Authorization

- [ ] `useApi` composable injects `Authorization: Bearer <token>` header on every request
- [ ] `useApi` intercepts 401 responses → calls `useAuth().logout()` → redirects to `/login`
- [ ] Route middleware stubs created for protected routes (`/dashboard`, `/projects`, etc.)
- [ ] No protected routes accessible without a valid auth token in Pinia store
- [ ] `useAuth.isAuthenticated` is reactive and drives navigation guard stubs

## Token Handling

- [ ] Auth token stored exclusively in Pinia `auth` store (not raw `localStorage` from components)
- [ ] Token is NOT logged to console at any point
- [ ] Token is NOT embedded in Vue template HTML (no `{{ token }}` rendering)
- [ ] Token NOT exposed in URL query params

## XSS Prevention

- [ ] No `v-html` directives used in any shell component
- [ ] User-provided content (e.g., display name in `UAvatar`) rendered as text, not HTML
- [ ] No `innerHTML` manipulation in composables

## Content Security

- [ ] Navigation items defined in `frontend/config/navigation.ts` — not dynamically constructed from user input
- [ ] i18n translation strings are static keys — no interpolation from user data in shell strings

## Sensitive Data Exposure

- [ ] User avatar URL from auth store — no sensitive profile fields exposed in nav
- [ ] No API response data rendered in shell components that could expose PII
- [ ] Error messages in `error.vue` are generic — no stack traces exposed to end-user

## CSRF

- [ ] Nuxt (SSR/SPA) relies on Sanctum token auth — CSRF token handling deferred to auth stage
- [ ] No form submissions in shell (toggles/switchers use click events, not forms)
