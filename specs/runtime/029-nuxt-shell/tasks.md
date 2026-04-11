# Tasks — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Based on:** `specs/runtime/029-nuxt-shell/plan.md` > **Created:** 2026-04-11T00:00:00Z
> **Total Tasks:** 35

## Legend

- `T001` — Sequential task ID in execution order
- `[P]` — Parallelizable task (can run concurrently with other `[P]` tasks in same group)
- `[US1]` — User story reference
- `- [ ]` — Incomplete | `- [X]` — Complete

## Setup & Foundation

- [x] T001 [US1] Update `frontend/nuxt.config.ts` — add `app.head.htmlAttrs: { dir: 'rtl', lang: 'ar' }` and Geist font preload link
- [x] T002 [US1] Create TypeScript type definitions: `frontend/types/auth.ts` (UserRole, UserProfile)
- [x] T003 [US1] Create TypeScript type definitions: `frontend/types/ui.ts` (Direction, ColorMode)
- [x] T004 [US1] Create navigation config: `frontend/config/navigation.ts` — NavItem interface + role-keyed nav items array

## State Management

- [x] T005 [US1] Extend Pinia auth store: `frontend/stores/auth.ts` — add `user: UserProfile | null`, `setUser()`, computed `isAuthenticated`, computed `userRole` (preserving existing token/setToken/logout API)
- [x] T006 [US2] Create Pinia UI store: `frontend/stores/ui.ts` — `isSidebarOpen`, `direction`, `colorMode` state with `toggleSidebar`, `setDirection`, `toggleDirection`, `setColorMode` actions

## Core Composables

- [x] T007 [US1] Create composable: `frontend/composables/useAuth.ts` — wraps `useAuthStore`, exposes reactive `user`, `role`, `isAuthenticated`, `logout()`, `hasRole()`
- [x] T008 [US5] Create composable: `frontend/composables/useNotification.ts` — wraps `useToast()` with `notify.success/error/info/warning(msg, title?)`
- [x] T009 [US1] Create composable: `frontend/composables/useBreadcrumb.ts` — `useState`-based `items`, `setBreadcrumb()`, `clearBreadcrumb()`
- [x] T010 [US2] Create composable: `frontend/composables/useDirection.ts` — `direction` (from UI store), `setDirection()` (syncs `document.dir` + `localStorage`), `toggleDirection()`, `initDirection()`

## i18n Translations

- [x] T011 [P] [US3] Extend `frontend/locales/ar.json` — add `shell.*` keys (direction, sidebar, theme, user, loading, nav), `nav.dashboard`, `nav.reports`, `nav.admin`
- [x] T012 [P] [US3] Extend `frontend/locales/en.json` — add `shell.*` keys (direction, sidebar, theme, user, loading, nav), `nav.dashboard`, `nav.reports`, `nav.admin`

## Shell Components (Parallelizable Group 1)

- [x] T013 [P] [US1] Create component: `frontend/components/shell/AppUserMenu.vue` — `UDropdown` with `UAvatar`, profile link, logout action using `useAuth().logout()`
- [x] T014 [P] [US3] Create component: `frontend/components/shell/LanguageSwitcher.vue` — `UDropdown` showing AR/EN options, calls `setLocale()` from `useI18n()`
- [x] T015 [P] [US2] Create component: `frontend/components/shell/DirectionToggle.vue` — `UButton` with icon, calls `useDirection().toggleDirection()`
- [x] T016 [P] [US5] Create component: `frontend/components/shell/AppLoadingBar.vue` — `UProgress` bound to `page:start` / `page:finish` Nuxt hooks
- [x] T017 [P] [US5] Create component: `frontend/components/shell/AppToastProvider.vue` — mounts `<UNotifications />` outlet

## Shell Components (Parallelizable Group 2 — depends on T013-T017)

- [x] T018 [P] [US1] Create component: `frontend/components/shell/AppBreadcrumb.vue` — `UBreadcrumb` bound to `useBreadcrumb().items`
- [x] T019 [P] [US1] Create component: `frontend/components/shell/AppSidebar.vue` — `UVerticalNavigation` with role-filtered items from `navigation.ts` via `useAuth().role`
- [x] T020 [P] [US4] Create component: `frontend/components/shell/AppMobileDrawer.vue` — `USlideOver` containing `UVerticalNavigation`, controlled by `ui.isSidebarOpen`
- [x] T021 [P] [US5] Create component: `frontend/components/shell/AppFooter.vue` — minimal footer with app name, copyright, shadow-as-border top

## Header (depends on T013-T017)

- [x] T022 [US1] [US2] [US3] [US4] Create component: `frontend/components/shell/AppHeader.vue` — shadow-as-border header with: logo/brand, `AppLoadingBar` (top-edge), desktop nav links, `DirectionToggle`, dark mode toggle, `LanguageSwitcher`, `AppUserMenu`, hamburger button (mobile only, calls `ui.toggleSidebar()`)

## Layouts

- [x] T023 [US1] Replace `frontend/layouts/default.vue` — full shell: `AppHeader` + `AppSidebar` (desktop) + `<slot />` + `AppFooter`, sidebar hidden on mobile
- [x] T024 [US1] Replace `frontend/layouts/auth.vue` — `UCard` centered layout with `<slot />`, no header/sidebar
- [x] T025 [US1] Create `frontend/layouts/public.vue` — `AppHeader` (no sidebar) + `<slot />` + `AppFooter`

## Error Page

- [x] T026 [US5] Replace `frontend/error.vue` — `UAlert` with error `statusCode` + `message`, retry button (`clearError()`), home button (`navigateTo('/')`)

## App Root

- [x] T027 [US5] Update `frontend/app.vue` — mount `<AppToastProvider />` outside `<NuxtLayout>`, call `useDirection().initDirection()` in `onMounted`, set `useHead` with reactive `lang` + `dir`

## Route Middleware Stubs

- [x] T028 [US1] Create `frontend/middleware/auth.ts` stub — redirect to `/ar/auth/login` if `!auth.token` and route `meta.requiresAuth === true`
- [x] T029 [US1] Create `frontend/middleware/role.ts` stub — redirect to `/ar/dashboard` if user role not in route `meta.roles`

## Unit Tests

- [x] T030 [P] [US2] Create unit tests: `frontend/tests/composables/useDirection.test.ts` — 5 test cases (default, toggle, persist, restore, setDirection)
- [x] T031 [P] [US1] Create unit tests: `frontend/tests/composables/useBreadcrumb.test.ts` — 4 test cases (empty init, set, clear, replace)
- [x] T032 [P] [US1] Create unit tests: `frontend/tests/composables/useAuth.test.ts` — 5 test cases (isAuthenticated false, true, role null, role set, logout)

## E2E Tests

- [x] T033 [US1] Create E2E tests: `frontend/tests/e2e/shell.spec.ts` — 6 scenarios: shell renders, RTL toggle persists, dark mode toggle, language switch, mobile drawer, active nav

## Validation

- [x] T034 Run lint and typecheck: `cd frontend && npm run lint && npm run typecheck`
- [x] T035 Run unit tests: `cd frontend && npm run test`
