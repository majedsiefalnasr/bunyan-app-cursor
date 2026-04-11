# Specify Report — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Generated:** 2026-04-11T00:00:00Z

## Specification Summary

| Metric                 | Value                                   |
| ---------------------- | --------------------------------------- |
| User Stories           | 5                                       |
| Acceptance Criteria    | 25                                      |
| Technical Requirements | 38 frontend items, 0 backend items      |
| Dependencies           | Upstream: 1 (STAGE_01), Downstream: all |
| Open Questions         | 0 (4 clarifications resolved inline)    |

## Scope Defined

**Layouts (3):** default, auth, public — covering all Bunyan page contexts.

**Navigation System:** Role-based `UNavigationMenu` (header) + `UNavigationTree` (sidebar) + `UBreadcrumb` + `UDrawer` (mobile). Navigation items filtered by user role from `frontend/config/navigation.ts`.

**Core Composables (5):** `useAuth`, `useApi`, `useNotification`, `useBreadcrumb`, `useDirection` — foundational utilities for all downstream pages.

**Shell Components (10):** `AppHeader`, `AppSidebar`, `AppBreadcrumb`, `AppFooter`, `AppMobileDrawer`, `AppToastProvider`, `AppLoadingBar`, `AppUserMenu`, `LanguageSwitcher`, `DirectionToggle`.

**Global UI Feedback:** `UProgress` loading bar, `USkeleton` placeholders, `UAlert` error boundary, `useToast()` notification system.

**RTL/i18n:** Default `dir="rtl"`, `@nuxtjs/i18n` with Arabic (default, no prefix) and English (`/en`), direction auto-syncs with locale.

**Dark Mode:** Nuxt UI `useColorMode()` integration with system preference detection.

**Tests:** 3 Vitest unit test files + 1 Playwright E2E file with 6 scenarios.

## Deferred Scope

- Backend API changes — none required (pure frontend stage)
- Real authentication API calls — `useAuth` uses stub/Pinia store data; real auth in downstream stage
- Page implementations — each downstream stage owns its pages
- User profile management — deferred to user profile stage
- Feature-specific Pinia stores — each feature stage owns its store
- Product catalog, project, or e-commerce UI — out of scope for shell

## Risk Assessment

| Risk                                            | Level  | Mitigation                                                                    |
| ----------------------------------------------- | ------ | ----------------------------------------------------------------------------- |
| Nuxt UI RTL compatibility gaps                  | MEDIUM | Use only Nuxt UI components (RTL-native); test all toggles in both directions |
| `@nuxtjs/i18n` direction auto-sync complexity   | MEDIUM | `useDirection` watches locale and auto-sets dir on locale change              |
| Playwright E2E setup not configured in frontend | LOW    | Playwright is in scope for this stage's test setup                            |
| Font (Geist) loading performance                | LOW    | Preload in `nuxt.config.ts` head config                                       |
| Mobile drawer focus trap accessibility          | LOW    | Nuxt UI `UDrawer` provides built-in focus trap                                |

## Clarifications Resolved

1. **`useAuth` stub vs real API** — uses Pinia store with mock data; no API calls in this stage
2. **Role-based nav enforcement location** — both client-side (nav filter) and route middleware stubs
3. **i18n URL strategy** — `prefix_except_default` (Arabic = `/`, English = `/en`)
4. **`useApi` 401 auto-logout** — yes, catches 401 and calls `logout()` + redirects to `/login`

## Checklist Status

- Requirements checklist: Created at `checklists/requirements.md`
- 38 frontend items tracked
- 0 backend items (pure frontend stage)
