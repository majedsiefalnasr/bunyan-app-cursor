# Tasks Report — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Generated:** 2026-04-11T00:00:00Z

## Task Summary

| Metric         | Value |
| -------------- | ----- |
| Total Tasks    | 35    |
| Parallelizable | 17    |
| Sequential     | 18    |
| HIGH Risk      | 0     |
| MEDIUM Risk    | 8     |
| LOW Risk       | 27    |

## Risk-Ranked Task View

### 🔴 HIGH Risk Tasks

None — this is a pure frontend shell stage with no database migrations or auth enforcement.

### 🟡 MEDIUM Risk Tasks

| ID   | Description                  | Risk Factor                                                     |
| ---- | ---------------------------- | --------------------------------------------------------------- |
| T005 | Extend auth Pinia store      | Must not break existing `useApi` token injection                |
| T007 | Create `useAuth` composable  | Must be SSR-safe; `useAuthStore` access outside context         |
| T010 | Create `useDirection`        | `document.dir` access must be guarded with `import.meta.client` |
| T022 | Create `AppHeader` component | Most complex component; integrates 5+ sub-components            |
| T023 | Replace `default.vue` layout | Affects all pages; must not regress existing pages              |
| T027 | Update `app.vue`             | Global mount point; errors here break all pages                 |
| T028 | Create auth middleware stub  | Wrong redirect path (prefix strategy) causes login loops        |
| T033 | Create E2E tests             | Playwright tests must use `/ar/` URL prefix                     |

### 🟢 LOW Risk Tasks

| ID   | Description                 | Risk Factor                     |
| ---- | --------------------------- | ------------------------------- |
| T001 | Update nuxt.config.ts       | Additive-only change            |
| T002 | Create types/auth.ts        | New file, no side effects       |
| T003 | Create types/ui.ts          | New file, no side effects       |
| T004 | Create navigation.ts config | New file, no side effects       |
| T006 | Create ui Pinia store       | New store, no existing deps     |
| T008 | Create useNotification      | Wraps existing useToast         |
| T009 | Create useBreadcrumb        | Uses useState, SSR-safe         |
| T011 | Extend ar.json              | Additive JSON additions         |
| T012 | Extend en.json              | Additive JSON additions         |
| T013 | Create AppUserMenu          | Isolated component              |
| T014 | Create LanguageSwitcher     | Isolated component              |
| T015 | Create DirectionToggle      | Isolated component              |
| T016 | Create AppLoadingBar        | Uses Nuxt hooks                 |
| T017 | Create AppToastProvider     | Single `<UNotifications />`     |
| T018 | Create AppBreadcrumb        | Isolated component              |
| T019 | Create AppSidebar           | Isolated, uses nav config       |
| T020 | Create AppMobileDrawer      | USlideOver wrapper              |
| T021 | Create AppFooter            | Minimal markup                  |
| T024 | Replace auth.vue            | Additive Nuxt UI upgrade        |
| T025 | Create public.vue layout    | New file, no regressions        |
| T026 | Replace error.vue           | Replaces existing stub          |
| T029 | Create role middleware stub | New file, no active enforcement |
| T030 | useDirection unit tests     | Isolated test file              |
| T031 | useBreadcrumb unit tests    | Isolated test file              |
| T032 | useAuth unit tests          | Isolated test file              |
| T034 | Run lint + typecheck        | Validation only                 |
| T035 | Run unit tests              | Validation only                 |

## External Dependencies

| Task ID | Package/Library    | Version  | Purpose                          |
| ------- | ------------------ | -------- | -------------------------------- |
| T001    | `@nuxt/ui`         | `^2.17`  | Shell components (installed)     |
| T001    | `@nuxtjs/i18n`     | `^9.5`   | i18n + RTL direction (installed) |
| T010    | `@vueuse/core`     | `^10.10` | `useStorage` for localStorage    |
| T016    | Nuxt page hooks    | built-in | `page:start`, `page:finish`      |
| T033    | `@playwright/test` | `^1.45`  | E2E tests (installed)            |

## High-Downstream-Impact Tasks

| Task ID | Description              | Downstream Impact                                              |
| ------- | ------------------------ | -------------------------------------------------------------- |
| T005    | Extend auth store        | All downstream pages use `useAuth()` — contract must be stable |
| T007    | Create `useAuth`         | Foundation composable for all authenticated pages              |
| T008    | Create `useNotification` | All pages use `notify.success/error` for feedback              |
| T022    | Create `AppHeader`       | Renders on every page using `default` layout                   |
| T023    | Replace `default.vue`    | Affects all authenticated pages                                |
| T027    | Update `app.vue`         | Global root — errors affect all pages                          |
| T028    | Auth middleware stub     | All protected routes depend on this                            |
