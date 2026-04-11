# Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Stage File:** `specs/phases/07_FRONTEND_APPLICATION/STAGE_29_NUXT_SHELL.md` > **Branch:** `spec/029-nuxt-shell` > **Created:** 2026-04-11T00:00:00Z

## Objective

Implement the Nuxt.js application shell for the Bunyan platform — the structural foundation for all frontend pages. This stage delivers: a multi-layout system (default/auth/public), role-based navigation, RTL-first layout with LTR toggle, dark mode, Arabic/English language switching, core composables, global UI feedback (loading, toasts, skeletons, error boundaries), and mobile-responsive navigation — all using Nuxt UI (`@nuxt/ui`) components and Tailwind CSS v4.

## Scope

### In Scope

- **Layout System**

  - `default.vue` — full-chrome layout: `UHeader` + sidebar + `<NuxtPage />` + `UFooter`
  - `auth.vue` — zero-chrome layout: centered `UCard` only
  - `public.vue` — public/marketing layout: header without sidebar

- **Navigation**

  - Main nav using `UNavigationMenu` with role-aware menu items (Customer / Contractor / Supervising Architect / Field Engineer / Admin)
  - Sidebar using `UNavigationTree` + persistent panel, collapsible on mobile via `USlideover`
  - Breadcrumb using `UBreadcrumb`, dynamically populated per route
  - Mobile hamburger → `UDrawer` for small viewports
  - Active route highlighting on nav items

- **UI Toggles**

  - RTL/LTR toggle: `useDirection` composable — sets `document.documentElement.dir`, persists to `localStorage`
  - Dark mode toggle: `useColorMode()` from `@vueuse/core` + Nuxt UI `AppConfig` color mode
  - Language switcher: Arabic/English via `@nuxtjs/i18n`, persisted to `localStorage`

- **Core Composables**

  - `useAuth` — reactive auth state (user, role, permissions, isAuthenticated, logout)
  - `useApi` — `$fetch`-based API client with Sanctum Bearer token header injection
  - `useNotification` — `useToast()` wrapper: `notify.success()`, `notify.error()`, `notify.info()`, `notify.warning()`
  - `useBreadcrumb` — `setBreadcrumb(items)` / `clearBreadcrumb()` for dynamic breadcrumb management
  - `useDirection` — `direction` (ref), `toggleDirection()`, `setDirection(dir)`

- **Global UI Feedback**

  - Global page loading indicator using `UProgress` (top bar)
  - Skeleton states: `USkeleton` wrappers for async content areas
  - Error boundary: global error page using `UAlert` (color="error") with retry action
  - Toast notifications: `UNotification` / `useToast()` — positioned top-end (RTL-aware)

- **RTL Implementation**

  - Default `dir="rtl"` on `<html>` via `nuxt.config.ts`
  - All Nuxt UI components used with logical CSS properties (RTL-native)
  - Tailwind logical utility classes (`ms-`, `me-`, `ps-`, `pe-`, `start-`, `end-`)

- **Nuxt Config Baseline**

  - Modules: `@nuxt/ui`, `@nuxtjs/i18n`
  - Default RTL/Arabic HTML attrs
  - Theme config: colors `["primary", "secondary", "success", "warning", "error", "info"]`

- **Unit Tests (Vitest)**

  - `useDirection` — toggle, persist, SSR-safe
  - `useBreadcrumb` — set/clear breadcrumb, route-driven
  - `useAuth` — login/logout state transitions, role access

- **E2E Tests (Playwright)**
  - Shell renders for each role (Customer, Contractor, Admin)
  - RTL direction toggle persists across navigation
  - Dark mode toggle applies `.dark` class
  - Language switch AR/EN updates visible text
  - Mobile drawer opens/closes on 375px viewport
  - Active navigation item highlights on route change

### Out of Scope

- Backend API changes (this stage is pure frontend shell)
- Page implementations (all downstream stages own their pages)
- Authentication flows and forms (handled by auth stage)
- User profile management
- Pinia store setup beyond auth state (each feature owns its store)
- Product catalog, e-commerce, or project-specific UI

## User Stories

### US1 — Application Shell Navigation

**As a** logged-in user of any role, **I want** a consistent navigation shell with role-specific menu items, **so that** I can access the correct sections of the platform for my role.

**Acceptance Criteria:**

- [ ] `UHeader` renders on all pages using `default` layout
- [ ] `UNavigationMenu` shows only menu items appropriate for the current user's role
- [ ] `UNavigationTree` sidebar renders and is keyboard-navigable
- [ ] Active route is visually indicated in both the header nav and sidebar
- [ ] `UBreadcrumb` renders dynamically based on current route depth
- [ ] All navigation elements are RTL-aligned by default

### US2 — RTL/LTR and Dark Mode Toggles

**As a** user, **I want** to toggle between RTL (Arabic) and LTR (English) layouts and between light/dark mode, **so that** I can read the platform comfortably in my preferred direction and theme.

**Acceptance Criteria:**

- [ ] RTL toggle button sets `document.documentElement.dir` to `rtl` or `ltr`
- [ ] Direction preference persists to `localStorage` and is restored on next visit
- [ ] Dark mode toggle applies `dark` class to `<html>` element
- [ ] Dark mode preference persists via Nuxt UI `useColorMode()`
- [ ] All Nuxt UI components respond correctly to both `dir` and color mode changes

### US3 — Language Switching (AR/EN)

**As a** user, **I want** to switch the platform language between Arabic and English, **so that** I can use the platform in my preferred language.

**Acceptance Criteria:**

- [ ] Language switcher is visible in the header
- [ ] Switching to English renders all i18n-keyed strings in English
- [ ] Switching to Arabic renders all i18n-keyed strings in Arabic
- [ ] Language selection persists across page navigation
- [ ] Direction changes automatically with language (Arabic → RTL, English → LTR)

### US4 — Mobile-Responsive Navigation

**As a** mobile user, **I want** a hamburger menu that opens a drawer with navigation, **so that** I can navigate the platform on small screens.

**Acceptance Criteria:**

- [ ] Sidebar is hidden on viewports < 768px
- [ ] Hamburger icon appears in `UHeader` on mobile
- [ ] Tapping hamburger opens `UDrawer` with full navigation tree
- [ ] Drawer closes on navigation item tap and on backdrop tap
- [ ] All drawer items are correctly sized for touch targets (min 44px)

### US5 — Global UI Feedback

**As a** user, **I want** clear loading indicators, toast notifications, and error states, **so that** I always know what the application is doing.

**Acceptance Criteria:**

- [ ] `UProgress` loading bar appears at top of screen during page transitions
- [ ] `useNotification` composable triggers `UNotification` toasts correctly
- [ ] `USkeleton` components replace content areas while async data loads
- [ ] Global error page renders with `UAlert` and a retry button on navigation error
- [ ] Toast notifications are positioned correctly in both RTL and LTR modes

## Technical Requirements

### Frontend (Nuxt.js)

**Layouts**

- [ ] `frontend/layouts/default.vue` — header + sidebar + main content + footer
- [ ] `frontend/layouts/auth.vue` — centered card only, no header/sidebar
- [ ] `frontend/layouts/public.vue` — header (no sidebar) + main + footer

**Components**

- [ ] `frontend/components/shell/AppHeader.vue` — `UHeader` with nav, toggles, user avatar/dropdown
- [ ] `frontend/components/shell/AppSidebar.vue` — `UNavigationTree` with role-filtered items
- [ ] `frontend/components/shell/AppBreadcrumb.vue` — `UBreadcrumb` bound to `useBreadcrumb`
- [ ] `frontend/components/shell/AppFooter.vue` — `UFooter` with minimal info
- [ ] `frontend/components/shell/AppMobileDrawer.vue` — `UDrawer` wrapper for mobile nav
- [ ] `frontend/components/shell/AppToastProvider.vue` — mounts `UNotification` outlet
- [ ] `frontend/components/shell/AppLoadingBar.vue` — `UProgress` bound to `useNuxtApp().hooks`
- [ ] `frontend/components/shell/AppUserMenu.vue` — `UDropdownMenu` with logout, profile, theme toggle
- [ ] `frontend/components/shell/LanguageSwitcher.vue` — i18n locale switcher
- [ ] `frontend/components/shell/DirectionToggle.vue` — RTL/LTR toggle button

**Composables**

- [ ] `frontend/composables/useAuth.ts` — auth state: `user`, `role`, `isAuthenticated`, `logout()`
- [ ] `frontend/composables/useApi.ts` — `$fetch` wrapper with `Authorization: Bearer` header
- [ ] `frontend/composables/useNotification.ts` — `notify.success/error/info/warning(msg, title?)`
- [ ] `frontend/composables/useBreadcrumb.ts` — `items`, `setBreadcrumb()`, `clearBreadcrumb()`
- [ ] `frontend/composables/useDirection.ts` — `direction`, `toggleDirection()`, `setDirection()`

**Pages (Error/Utility)**

- [ ] `frontend/error.vue` — global error page with `UAlert` + retry/home actions

**Config**

- [ ] `frontend/nuxt.config.ts` — modules `@nuxt/ui`, `@nuxtjs/i18n`, RTL HTML attrs, theme colors
- [ ] `frontend/i18n/locales/ar.json` — Arabic translations for shell strings
- [ ] `frontend/i18n/locales/en.json` — English translations for shell strings

**Navigation Definition**

- [ ] `frontend/config/navigation.ts` — role-keyed nav item definitions

**State Management**

- [ ] `frontend/stores/auth.ts` — Pinia store for auth state (user, token, role)
- [ ] `frontend/stores/ui.ts` — Pinia store for direction, color mode, sidebar open state

**Tests**

- [ ] `frontend/tests/composables/useDirection.test.ts` — Vitest unit tests
- [ ] `frontend/tests/composables/useBreadcrumb.test.ts` — Vitest unit tests
- [ ] `frontend/tests/composables/useAuth.test.ts` — Vitest unit tests
- [ ] `frontend/tests/e2e/shell.spec.ts` — Playwright E2E tests

**RTL Support**

- [ ] All layout components use Tailwind logical properties exclusively
- [ ] `dir="rtl"` default in `nuxt.config.ts` `app.head.htmlAttrs`
- [ ] `useDirection` dynamically overrides on toggle

### Backend (Laravel)

None. This stage is pure frontend.

## Dependencies

- **Upstream:** STAGE_01_PROJECT_INITIALIZATION (Nuxt app scaffold must exist)
- **Downstream:** All frontend page stages (STAGE_30+)

## Non-Functional Requirements

- [ ] No backend API calls in this stage — shell composables use mock/stub data until auth stage
- [ ] Arabic/RTL layout support — default direction is RTL
- [ ] Mobile responsive design — all components adapt at 375px, 768px, 1024px breakpoints
- [ ] Dark mode support — all components honor `prefers-color-scheme` and manual toggle
- [ ] Accessible — ARIA labels on nav items, keyboard-navigable sidebar, focus traps in drawer
- [ ] Performance — no layout shift on direction/mode toggle; font preloaded (Geist)
- [ ] i18n-ready — all shell strings extracted to translation keys

## Open Questions

- None — scope is fully defined from stage file.

## Clarifications

### Session 2026-04-11

**Q1: Should `useAuth` stub auth state in this stage or connect to a real API?**
A: `useAuth` should read from the Pinia `auth` store but NOT make API calls in this stage. The store will be populated with mock/stub data. The real API integration happens in the authentication stage.

**Q2: Should role-based nav filtering be enforced in the shell or deferred to middleware?**
A: Both. The nav component filters items client-side using the `useAuth` role. Route-level RBAC middleware is set up as stubs here, with full enforcement in the authentication stage.

**Q3: Which i18n strategy — `prefix_except_default` or `no_prefix`?**
A: Use `no_prefix` strategy for now. Arabic is the default locale (no URL prefix). English uses `/en` prefix — configured as `prefix_except_default`.

**Q4: Should `useApi` in this stage handle 401 auto-logout?**
A: Yes. `useApi` should catch 401 responses and call `useAuth().logout()` to clear state and redirect to `/login`. This is foundational for all downstream pages.
