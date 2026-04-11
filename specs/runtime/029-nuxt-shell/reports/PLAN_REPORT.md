# Plan Report — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Generated:** 2026-04-11T00:00:00Z

## Plan Summary

| Metric          | Value                                                                     |
| --------------- | ------------------------------------------------------------------------- |
| New Tables      | 0                                                                         |
| New Endpoints   | 0                                                                         |
| New Services    | 0                                                                         |
| New Pages       | 0 (error.vue replacement only)                                            |
| New Layouts     | 1 new (public.vue), 2 replacements (default.vue, auth.vue)                |
| New Components  | 10 shell components                                                       |
| New Composables | 4 new (useAuth, useNotification, useBreadcrumb, useDirection) + 0 changed |
| New Stores      | 1 new (ui.ts), 1 extended (auth.ts)                                       |
| New Config      | 2 (navigation.ts, app.head RTL config)                                    |
| New Types       | 2 files (types/auth.ts, types/ui.ts)                                      |

## Architecture Decisions

1. **Nuxt UI v2 Component Mapping** — Stage spec referenced v3 Pro component names. Plan corrects all references to v2 equivalents (`UVerticalNavigation`, `USlideOver`, `UDropdown`, `UNotifications`). Custom shell components built where v2 has no direct equivalent (AppHeader, AppFooter, AppLoadingBar).

2. **Single Role-Aware Default Layout** — One `default.vue` layout handles all authenticated roles. Navigation items filtered reactively by `useAuth().role`. Existing `admin.vue` layout preserved but deprecated; removal deferred.

3. **i18n URL Strategy Correction** — Clarification said `prefix_except_default`; actual `nuxt.config.ts` uses `strategy: 'prefix'` (all locales prefixed). Plan preserves existing `prefix` strategy. Arabic at `/ar/`, English at `/en/`.

4. **Auth Store Extension (Non-Breaking)** — Existing `token`, `setToken()`, `logout()` API preserved. Adding `user: UserProfile | null`, `setUser()`, `isAuthenticated` computed. `useApi.ts` is not changed.

5. **`useBreadcrumb` uses `useState`** — SSR-compatible reactive state shared across composable instances. No Pinia store for breadcrumb (ephemeral page data).

## Guardian Verdicts

| Guardian              | Verdict | Notes                                                                              |
| --------------------- | ------- | ---------------------------------------------------------------------------------- |
| Architecture Guardian | PASS    | No layering violations; composable/store separation correct; RBAC stubs documented |
| API Designer          | PASS    | No API endpoints; composable contracts are clean and non-breaking                  |

## Risk Assessment

| Risk Level | Count | Details                                                                        |
| ---------- | ----- | ------------------------------------------------------------------------------ |
| HIGH       | 0     | —                                                                              |
| MEDIUM     | 2     | Nuxt UI v2 RTL SlideOver side behavior; Playwright URLs must use `/ar/` prefix |
| LOW        | 3     | `useColorMode` compatibility, USlideOver z-index, direction toggle CLS         |
