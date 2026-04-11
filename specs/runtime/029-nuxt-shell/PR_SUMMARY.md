# PR — Nuxt Shell

## Summary

**Stage:** Nuxt Shell
**Phase:** 07_FRONTEND_APPLICATION
**Branch:** `spec/029-nuxt-shell` → `develop`
**Tasks:** 35 / 35 completed

Implements the complete Nuxt.js application shell for Bunyan — the structural foundation wrapping every page. Delivers RTL-first multi-layout system, role-based navigation, dark mode, Arabic/English i18n, core composables, and global UI feedback using Nuxt UI v2.

## What Changed

### Backend

None. Pure frontend stage.

### Frontend

**New Layouts (3)**

- `layouts/default.vue` — Full shell: header + role-filtered sidebar + main content + footer
- `layouts/auth.vue` — Centered `UCard` layout for auth pages (no chrome)
- `layouts/public.vue` — Header + main + footer (no sidebar)

**New Shell Components (10)**

- `components/shell/AppHeader.vue` — Top bar with brand, direction toggle, dark mode, language switcher, user menu
- `components/shell/AppSidebar.vue` — `UVerticalNavigation` with role-filtered items (desktop)
- `components/shell/AppMobileDrawer.vue` — `USlideOver` mobile navigation drawer
- `components/shell/AppBreadcrumb.vue` — Dynamic `UBreadcrumb` bound to composable state
- `components/shell/AppFooter.vue` — Minimal footer with shadow-as-border
- `components/shell/AppLoadingBar.vue` — `UProgress` bar tied to Nuxt page hooks
- `components/shell/AppToastProvider.vue` — `UNotifications` outlet
- `components/shell/AppUserMenu.vue` — User avatar dropdown with logout
- `components/shell/LanguageSwitcher.vue` — AR/EN locale switcher
- `components/shell/DirectionToggle.vue` — RTL/LTR toggle (`data-testid="rtl-toggle"`)

**New Composables (4)**

- `composables/useAuth.ts` — Reactive auth state: `user`, `role`, `isAuthenticated`, `logout()`, `hasRole()`
- `composables/useNotification.ts` — `notify.success/error/info/warning()` toast wrapper
- `composables/useBreadcrumb.ts` — `setBreadcrumb/clearBreadcrumb` with `useState` SSR safety
- `composables/useDirection.ts` — RTL/LTR toggle with `localStorage` persistence

**New Pinia Store**

- `stores/ui.ts` — `isSidebarOpen`, `direction`, `colorMode` with toggle actions

**Modified Pinia Store**

- `stores/auth.ts` — Extended with `user: UserProfile | null`, `setUser()`, computed `isAuthenticated` and `userRole` (non-breaking)

**New TypeScript Types**

- `types/auth.ts` — `UserRole`, `UserProfile`
- `types/ui.ts` — `Direction`, `ColorMode`

**Navigation Config**

- `config/navigation.ts` — Role-keyed `NavItem[]` for 5 user roles

**Route Middleware Stubs**

- `middleware/auth.ts` — Redirects unauthenticated users to `/ar/auth/login`
- `middleware/role.ts` — Redirects unauthorized roles to `/ar/dashboard`

**Modified Files**

- `app.vue` — Mounts `AppToastProvider`, calls `initDirection()`, reactive `useHead`
- `error.vue` — Full `UAlert` error page with 404/403/500 handling
- `nuxt.config.ts` — RTL defaults (`dir="rtl"`, `lang="ar"`) + Geist font preload
- `locales/ar.json` — Added `shell.*` keys + nav dashboard/reports/admin
- `locales/en.json` — Added `shell.*` keys + nav dashboard/reports/admin
- `vitest.config.ts` — Exclude `tests/e2e/**` from Vitest glob

**New Tests**

- `tests/unit/composables/useAuth.spec.ts` — 8 tests
- `tests/unit/composables/useDirection.spec.ts` — 8 tests
- `tests/unit/composables/useBreadcrumb.spec.ts` — 5 tests
- `tests/e2e/shell.spec.ts` — 6 Playwright scenarios

### Database

None. No migrations.

## Breaking Changes

- **`stores/auth.ts`** — `logout()` now also clears `user` (was only clearing `token`). Downstream callers expecting `user` to persist after logout should update.
- **`layouts/default.vue`** — Complete replacement of stub layout. Pages using `default` layout now render in the full shell. Pages that were previously relying on the stub's minimal markup may need visual review.

## Testing

- [x] Unit tests pass: `cd frontend && npm run test` → 33/33 passed
- [x] Frontend lint passes: `cd frontend && npm run lint` → 0 errors
- [x] TypeScript check passes: `cd frontend && npm run typecheck` → 0 errors
- [ ] E2E tests (Playwright) — require dev server: `npm run test:e2e` (manual only)
- N/A — No backend changes

## Checklist

- [x] RBAC: middleware stubs applied; full enforcement deferred to auth stage
- [x] Arabic/RTL: `dir="rtl"` default; logical Tailwind properties throughout shell
- [x] Error contract: `useApi` contract unchanged; error display via `UAlert`
- [x] No N+1: no database queries in shell (composables use Pinia store only)
- [x] i18n: all shell strings in `ar.json` + `en.json` translation keys
- N/A — No new API endpoints, migrations, or backend routes

## Design System

- Shadow-as-border on header, sidebar, footer: `shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]`
- Geist font preloaded in `nuxt.config.ts`
- Primary text: `text-[#171717]`
- Background: `bg-white` / `bg-[#fafafa]` (surfaces)
- All components respond to dark mode via Nuxt UI `useColorMode()`

## Related

- Stage File: `specs/phases/07_FRONTEND_APPLICATION/STAGE_29_NUXT_SHELL.md`
- Testing Guide: `specs/runtime/029-nuxt-shell/guides/TESTING_GUIDE.md`
- Closure Report: `specs/runtime/029-nuxt-shell/reports/CLOSURE_REPORT.md`
