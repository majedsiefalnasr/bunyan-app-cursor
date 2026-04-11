# Requirements Checklist — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Created:** 2026-04-11T00:00:00Z

## Architecture Compliance

- [ ] RBAC middleware applied on all protected routes (stubbed in shell, enforced in auth stage)
- [ ] No backend API calls from shell components (stub data only in this stage)
- [ ] Composables are auto-imported by Nuxt (no explicit imports required)
- [ ] All layouts follow Nuxt layout convention (`definePageMeta({ layout: '...' })`)
- [ ] Pinia stores used for cross-component state (no prop drilling)
- [ ] Error contract followed for `useApi` responses (success/data/message/errors)
- [ ] No business logic in page components — composables only

## Security

- [ ] `useApi` injects `Authorization: Bearer <token>` from Pinia auth store
- [ ] `useApi` handles 401 → auto-logout + redirect to login
- [ ] Token stored in Pinia store (not directly in localStorage/cookies from components)
- [ ] No sensitive data (token, user PII) logged to console
- [ ] Navigation guard stubs in place for protected routes
- [ ] XSS prevention — no raw `v-html` usage in shell components

## Frontend

- [ ] Arabic/RTL layout: `dir="rtl"` default in `nuxt.config.ts`
- [ ] All shell components use Tailwind logical properties (`ms-`, `me-`, `ps-`, `pe-`)
- [ ] `useDirection` persists toggle to `localStorage` and restores on mount
- [ ] Dark mode: `useColorMode()` integration with Nuxt UI AppConfig
- [ ] Language switcher: `@nuxtjs/i18n` locale switching with `prefix_except_default`
- [ ] Mobile responsive: all layouts work at 375px, 768px, 1024px viewpoints
- [ ] `UDrawer` mobile nav is touch-friendly (min 44px touch targets)
- [ ] Loading bar via `UProgress` tied to page transition hooks
- [ ] Toast notifications: `useToast()` wired through `useNotification` composable
- [ ] `USkeleton` components prepared for async content placeholders
- [ ] Global error page (`error.vue`) renders `UAlert` with retry/home actions
- [ ] All user-facing strings in i18n translation keys
- [ ] Arabic translations (`ar.json`) and English translations (`en.json`) complete for shell strings
- [ ] ARIA labels on all navigation elements
- [ ] Keyboard navigation through sidebar and header nav
- [ ] Focus trap in `UDrawer` when open

## Nuxt UI Component Usage

- [ ] `UHeader` — app header component
- [ ] `UNavigationMenu` — horizontal nav in header
- [ ] `UNavigationTree` — sidebar tree nav
- [ ] `USlideover` / `UDrawer` — mobile navigation drawer
- [ ] `UBreadcrumb` — dynamic breadcrumb
- [ ] `UFooter` — app footer
- [ ] `UDropdownMenu` — user avatar dropdown (logout, profile)
- [ ] `UAvatar` — user avatar in header
- [ ] `UProgress` — page loading indicator
- [ ] `USkeleton` — loading placeholders
- [ ] `UAlert` — error boundary display
- [ ] `UNotification` / `useToast()` — toast notification system

## Testing

- [ ] `useDirection.test.ts` — toggles `document.dir`, persists/restores from `localStorage`
- [ ] `useBreadcrumb.test.ts` — `setBreadcrumb()` and `clearBreadcrumb()` behave correctly
- [ ] `useAuth.test.ts` — `isAuthenticated`, `role`, `logout()` state transitions
- [ ] E2E: shell renders for Customer, Contractor, and Admin roles
- [ ] E2E: RTL toggle persists across navigation
- [ ] E2E: dark mode toggle applies `.dark` class
- [ ] E2E: language switch AR/EN updates text
- [ ] E2E: mobile drawer opens/closes on 375px viewport
- [ ] E2E: active nav item highlighted on current route

## Performance

- [ ] Geist font preloaded in `nuxt.config.ts` `app.head.link`
- [ ] No layout shift (CLS) on direction/color mode toggle
- [ ] `UNavigationTree` items computed only once per role (not on every render)
- [ ] No unnecessary re-renders in header on page navigation

## i18n

- [ ] All shell strings in `ar.json` and `en.json`
- [ ] `@nuxtjs/i18n` configured with `defaultLocale: 'ar'`, `strategy: 'prefix_except_default'`
- [ ] Language direction auto-syncs with locale change (AR → RTL, EN → LTR)
- [ ] i18n locale persisted across page refresh
