# Technical Plan — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Based on:** `specs/runtime/029-nuxt-shell/spec.md` > **Created:** 2026-04-11T00:00:00Z

## Architecture Overview

This stage delivers the Bunyan application shell — the structural skeleton that wraps every page. It is a **pure-frontend stage** with zero backend changes.

### Key Architecture Decisions

1. **Nuxt UI v2.17 Component Mapping** — The stage spec references Nuxt UI v3 Pro names (`UNavigationTree`, `UHeader`, `UFooter`, `UDrawer`). The installed version is `@nuxt/ui ^2.17`. Plan uses the correct v2 equivalents:
   - `UNavigationTree` → `UVerticalNavigation`
   - `UNavigationMenu` → Custom `AppHeaderNav` using `NuxtLink` + Tailwind
   - `UHeader` / `UFooter` → Custom `AppHeader.vue` / `AppFooter.vue`
   - `USlideover` / `UDrawer` → `USlideOver` (Nuxt UI v2 name)
   - `UNotification` → `UNotifications` + `useToast()` (v2 API)
   - `UDropdownMenu` → `UDropdown` (v2 name)
   - `UProgress`, `USkeleton`, `UBreadcrumb`, `UAlert`, `UAvatar`, `UButton`, `UCard` — all exist in v2

2. **Single Layout for All Authenticated Roles** — The `default.vue` layout renders the full shell. Navigation items are role-filtered at runtime using `useAuth().role`. No separate per-role layouts. Existing `admin.vue` layout is superseded by this role-aware `default.vue`.

3. **i18n Direction Strategy** — `@nuxtjs/i18n` v9 locale config already includes `dir: 'rtl'` for Arabic and `dir: 'ltr'` for English. `nuxt.config.ts` will add `app.head.htmlAttrs: { dir: 'rtl', lang: 'ar' }` as SSR default. `useDirection` overrides at runtime; direction auto-syncs on locale change.

4. **Auth Store Extension** — Existing `auth.ts` Pinia store is minimal (token only). It must be extended with `user` (UserProfile), `role` (UserRole enum), `isAuthenticated` (computed). No breaking changes to existing `token` / `setToken` / `logout` API.

5. **useApi Already Exists** — `useApi.ts` is fully implemented with 401 auto-logout. This stage adds `useAuth.ts` as a higher-level composable that wraps `useAuthStore` and provides reactive auth state to components. `useApi` is not changed.

6. **No VeeValidate in Shell** — Shell has no forms. VeeValidate/Zod usage is deferred to downstream page stages.

7. **Pinia `ui.ts` Store** — New store for cross-component UI state: `isSidebarOpen`, `direction`, `colorMode`. `direction` state kept in store so all components react to toggle.

## Database Design

Not applicable — pure frontend stage.

## API Design

Not applicable — pure frontend stage. All composables use stub/Pinia store data in this stage.

## Service Layer Design

Not applicable — pure frontend stage.

## Frontend Design

### Layouts

| Layout  | File                  | Purpose                                         | Auth         |
| ------- | --------------------- | ----------------------------------------------- | ------------ |
| default | `layouts/default.vue` | Full shell: header + sidebar + content + footer | Required     |
| auth    | `layouts/auth.vue`    | Centered card, no chrome                        | Not required |
| public  | `layouts/public.vue`  | Header only, no sidebar                         | Not required |

**Note:** Existing `layouts/admin.vue` remains but is deprecated by the role-aware `default.vue`. It will be removed in a future cleanup stage.

### Components

| Component        | File                                    | Nuxt UI v2 Used                      | Purpose                |
| ---------------- | --------------------------------------- | ------------------------------------ | ---------------------- |
| AppHeader        | `components/shell/AppHeader.vue`        | `UButton`, `UAvatar`, `UDropdown`    | Top navigation bar     |
| AppSidebar       | `components/shell/AppSidebar.vue`       | `UVerticalNavigation`                | Role-filtered sidebar  |
| AppBreadcrumb    | `components/shell/AppBreadcrumb.vue`    | `UBreadcrumb`                        | Dynamic breadcrumb     |
| AppFooter        | `components/shell/AppFooter.vue`        | —                                    | Minimal footer         |
| AppMobileDrawer  | `components/shell/AppMobileDrawer.vue`  | `USlideOver` + `UVerticalNavigation` | Mobile nav drawer      |
| AppToastProvider | `components/shell/AppToastProvider.vue` | `UNotifications`                     | Toast outlet           |
| AppLoadingBar    | `components/shell/AppLoadingBar.vue`    | `UProgress`                          | Page transition bar    |
| AppUserMenu      | `components/shell/AppUserMenu.vue`      | `UDropdown` + `UAvatar`              | User avatar + dropdown |
| LanguageSwitcher | `components/shell/LanguageSwitcher.vue` | `UDropdown`                          | AR/EN switcher         |
| DirectionToggle  | `components/shell/DirectionToggle.vue`  | `UButton`                            | RTL/LTR toggle         |

### Pages (Error/Utility)

| Route | File        | Layout              | Notes                                |
| ----- | ----------- | ------------------- | ------------------------------------ |
| Error | `error.vue` | none (Nuxt special) | Replaces existing stub with `UAlert` |

### State Management (Pinia)

| Store              | State                                     | Actions                                         | Getters                       |
| ------------------ | ----------------------------------------- | ----------------------------------------------- | ----------------------------- |
| `auth.ts` (extend) | `token`, `user`, `role`                   | `setToken`, `setUser`, `logout`                 | `isAuthenticated`, `userRole` |
| `ui.ts` (new)      | `isSidebarOpen`, `direction`, `colorMode` | `toggleSidebar`, `setDirection`, `setColorMode` | —                             |

### Composables

| Composable        | File                             | Purpose                                                                           |
| ----------------- | -------------------------------- | --------------------------------------------------------------------------------- |
| `useAuth`         | `composables/useAuth.ts`         | Wraps `useAuthStore` — reactive `user`, `role`, `isAuthenticated`, `logout()`     |
| `useApi`          | `composables/useApi.ts`          | EXISTING — no changes needed                                                      |
| `useNotification` | `composables/useNotification.ts` | Wraps `useToast()` — `notify.success/error/info/warning(msg, title?)`             |
| `useBreadcrumb`   | `composables/useBreadcrumb.ts`   | `items`, `setBreadcrumb(items)`, `clearBreadcrumb()`                              |
| `useDirection`    | `composables/useDirection.ts`    | `direction`, `toggleDirection()`, `setDirection(dir)`, `localStorage` persistence |

### Navigation Configuration

```typescript
// frontend/config/navigation.ts
export type UserRole =
  | "customer"
  | "contractor"
  | "architect"
  | "engineer"
  | "admin";

export interface NavItem {
  label: string;
  labelKey: string; // i18n key
  to: string;
  icon: string; // Heroicon name
  roles: UserRole[]; // Empty = public (no auth required)
}

export const navigationItems: NavItem[] = [
  { labelKey: "nav.home", to: "/", icon: "i-heroicons-home", roles: [] },
  {
    labelKey: "nav.dashboard",
    to: "/dashboard",
    icon: "i-heroicons-squares-2x2",
    roles: ["customer", "contractor", "architect", "engineer", "admin"],
  },
  {
    labelKey: "nav.projects",
    to: "/projects",
    icon: "i-heroicons-building-office",
    roles: ["customer", "contractor", "architect", "engineer", "admin"],
  },
  {
    labelKey: "nav.reports",
    to: "/reports",
    icon: "i-heroicons-document-text",
    roles: ["contractor", "engineer", "admin"],
  },
  {
    labelKey: "nav.products",
    to: "/products",
    icon: "i-heroicons-shopping-bag",
    roles: ["customer", "admin"],
  },
  {
    labelKey: "nav.admin",
    to: "/admin",
    icon: "i-heroicons-cog-6-tooth",
    roles: ["admin"],
  },
];
```

### i18n Shell Keys to Add

**Arabic (`ar.json`) additions:**

```json
{
  "shell": {
    "direction": { "rtl": "عربي (يمين لشمال)", "ltr": "إنجليزي (يسار لشمال)" },
    "sidebar": { "toggle": "تبديل القائمة الجانبية", "close": "إغلاق" },
    "theme": { "light": "وضع النهار", "dark": "وضع الليل", "system": "تلقائي" },
    "user": { "profile": "الملف الشخصي", "logout": "تسجيل الخروج" },
    "loading": "جاري التحميل...",
    "nav": { "main": "القائمة الرئيسية", "breadcrumb": "مسار التنقل" }
  },
  "nav": {
    "dashboard": "لوحة التحكم",
    "reports": "التقارير",
    "admin": "الإدارة"
  }
}
```

**English (`en.json`) additions:**

```json
{
  "shell": {
    "direction": { "rtl": "Arabic (RTL)", "ltr": "English (LTR)" },
    "sidebar": { "toggle": "Toggle sidebar", "close": "Close" },
    "theme": { "light": "Light mode", "dark": "Dark mode", "system": "System" },
    "user": { "profile": "Profile", "logout": "Sign out" },
    "loading": "Loading...",
    "nav": { "main": "Main navigation", "breadcrumb": "Breadcrumb" }
  },
  "nav": {
    "dashboard": "Dashboard",
    "reports": "Reports",
    "admin": "Admin"
  }
}
```

### nuxt.config.ts Changes

```typescript
// Add to existing config:
app: {
  head: {
    htmlAttrs: { dir: 'rtl', lang: 'ar' },
    link: [
      { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
      { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&display=swap' },
    ],
  },
},
```

### Design System Tokens (Tailwind CSS)

Following DESIGN.md:

- Shadow-as-border: `shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]` (no CSS border)
- Header height: `h-16` (64px)
- Sidebar width: `w-64` (256px)
- Font: Geist Sans via CSS `font-family: 'Geist', sans-serif`
- Text primary: `text-[#171717]` (not pure black)
- Background: `bg-white`

## Middleware Chain

Not applicable (backend). Frontend navigation guard stubs:

```typescript
// middleware/auth.ts (stub)
export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuthStore();
  if (!auth.token && to.meta.requiresAuth) {
    return navigateTo("/ar/auth/login"); // prefix strategy uses /ar/
  }
});
```

## Error Handling

| Scenario         | Component                            | Display                                                |
| ---------------- | ------------------------------------ | ------------------------------------------------------ |
| Page-level error | `error.vue`                          | `UAlert` with status code, message, retry/home buttons |
| API 401          | `useApi.ts` (existing)               | Auto-logout + redirect (already implemented)           |
| API 4xx/5xx      | `useErrorNotification.ts` (existing) | Toast via `useToast()`                                 |
| Navigation error | `error.vue`                          | Nuxt `clearError()` + `useError()`                     |

## Testing Strategy

| Layer | Tool       | Target                                     | Files                         |
| ----- | ---------- | ------------------------------------------ | ----------------------------- |
| Unit  | Vitest     | `useDirection`, `useBreadcrumb`, `useAuth` | `tests/composables/*.test.ts` |
| E2E   | Playwright | Shell rendering, toggles, mobile nav       | `tests/e2e/shell.spec.ts`     |

### Unit Test Coverage Plan

```typescript
// useDirection.test.ts
describe("useDirection", () => {
  it("defaults to rtl (from localStorage or default)");
  it("toggleDirection() switches between rtl and ltr");
  it("persists direction to localStorage on change");
  it("restores direction from localStorage on mount");
  it('setDirection("ltr") sets direction to ltr');
});

// useBreadcrumb.test.ts
describe("useBreadcrumb", () => {
  it("starts with empty items array");
  it("setBreadcrumb() sets items correctly");
  it("clearBreadcrumb() resets to empty");
  it("multiple setBreadcrumb calls replace (not append)");
});

// useAuth.test.ts
describe("useAuth", () => {
  it("isAuthenticated is false when token is null");
  it("isAuthenticated is true when token is set");
  it("role returns null when no user set");
  it("role returns user.role when user is set");
  it("logout() clears token and user");
});
```

### E2E Test Plan

```typescript
// tests/e2e/shell.spec.ts
test("shell renders AppHeader and AppSidebar for authenticated user");
test("RTL direction toggle persists across navigation");
test("dark mode toggle applies .dark class to html");
test("language switch AR→EN updates visible nav text");
test("mobile drawer opens and closes on 375px viewport");
test("active nav item has active state on current route");
```

## Security Considerations

- [x] `useApi` already handles 401 → auto-logout (implemented)
- [ ] Route middleware stubs for `requiresAuth` meta flag
- [ ] No `v-html` in any shell component
- [ ] Auth token stays in Pinia store only (not directly in `localStorage`)
- [ ] Navigation items filtered client-side by role (defensive, not authoritative)

## i18n / RTL Considerations

- [x] `@nuxtjs/i18n` locales already have `dir: 'rtl'` and `dir: 'ltr'` configured
- [ ] `nuxt.config.ts` `app.head.htmlAttrs.dir = 'rtl'` as SSR default
- [ ] `useDirection` watches locale changes and syncs `document.dir`
- [ ] All Tailwind utility classes use logical properties (`ms-`, `me-`, `ps-`, `pe-`, `start-`, `end-`)
- [ ] Mixed Arabic/English text in nav labels uses `dir="auto"` on text nodes

## Risk Assessment

| Risk                                                          | Likelihood | Impact | Mitigation                                                                     |
| ------------------------------------------------------------- | ---------- | ------ | ------------------------------------------------------------------------------ |
| Nuxt UI v2 missing components (UHeader, UFooter, etc.)        | CONFIRMED  | MEDIUM | Build custom shell components using available v2 primitives                    |
| Direction toggle causing layout shift                         | MEDIUM     | LOW    | CSS `transition: none` on direction change; only `dir` attr swap               |
| `useColorMode` compatibility with Nuxt UI v2                  | LOW        | LOW    | Nuxt UI v2 supports `useColorMode` from `@vueuse/core` or `@nuxtjs/color-mode` |
| `USlideOver` z-index conflicts on mobile                      | LOW        | LOW    | Set explicit z-index on slide-over overlay                                     |
| Playwright E2E: `@nuxtjs/i18n` `prefix` strategy changes URLs | MEDIUM     | MEDIUM | All E2E test URLs prefixed with `/ar/` for Arabic tests                        |
