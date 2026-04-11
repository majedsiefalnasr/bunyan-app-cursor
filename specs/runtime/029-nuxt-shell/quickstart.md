# Quickstart — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Get running in 15 minutes**

## Prerequisites

- Node.js 20+ installed
- `npm install` run in `frontend/`
- Backend not required (pure frontend stage)

## Step 1: Start the Development Server

```bash
cd frontend
npm run dev
# App runs at http://localhost:3000
# Arabic (default): http://localhost:3000/ar/
# English: http://localhost:3000/en/
```

## Step 2: Verify Shell Renders

Open `http://localhost:3000/ar/` — you should see:

- Header with Bunyan logo
- Sidebar (desktop) or hamburger (mobile)
- Empty main content area
- Footer

## Step 3: Test RTL Toggle

1. Click the RTL/LTR toggle button in the header
2. Page layout should mirror (sidebar moves from right to left)
3. Reload — direction should persist from localStorage

## Step 4: Test Dark Mode

1. Click the theme toggle in the header (sun/moon icon)
2. Page should switch to dark mode
3. Reload — mode should persist via cookie

## Step 5: Test Language Switch

1. Click the language switcher (العربية / English)
2. URL should change from `/ar/` to `/en/`
3. All nav labels should update

## Step 6: Test Mobile Navigation

1. Open Chrome DevTools → Device toolbar → iPhone 12 (375px)
2. Hamburger icon should appear in header
3. Click hamburger → `USlideOver` drawer should open
4. Click any nav item → drawer should close and navigate

## Step 7: Run Unit Tests

```bash
cd frontend
npm run test
# Tests: tests/composables/useDirection.test.ts
#        tests/composables/useBreadcrumb.test.ts
#        tests/composables/useAuth.test.ts
```

## Step 8: Run E2E Tests

```bash
cd frontend
npm run test:e2e
# Tests: tests/e2e/shell.spec.ts
# Playwright opens in headless mode
```

## Step 9: Lint Check

```bash
cd frontend
npm run lint && npm run typecheck
```

## Key Files Reference

| File                                      | Purpose               |
| ----------------------------------------- | --------------------- |
| `frontend/layouts/default.vue`            | Main app shell        |
| `frontend/layouts/auth.vue`               | Auth/login layout     |
| `frontend/layouts/public.vue`             | Public pages layout   |
| `frontend/components/shell/`              | All shell components  |
| `frontend/composables/useAuth.ts`         | Auth state access     |
| `frontend/composables/useDirection.ts`    | RTL/LTR toggle        |
| `frontend/composables/useBreadcrumb.ts`   | Breadcrumb management |
| `frontend/composables/useNotification.ts` | Toast notifications   |
| `frontend/stores/auth.ts`                 | Auth Pinia store      |
| `frontend/stores/ui.ts`                   | UI state Pinia store  |
| `frontend/config/navigation.ts`           | Nav item definitions  |
| `frontend/locales/ar.json`                | Arabic translations   |
| `frontend/locales/en.json`                | English translations  |

## Troubleshooting

**Direction not persisting:**

- Check `localStorage.getItem('bunyan-direction')` in browser console
- Ensure `useDirection.initDirection()` is called in `app.vue` `onMounted`

**Sidebar not showing on desktop:**

- Check `ui.isSidebarOpen` in Pinia devtools
- Ensure layout breakpoint class `hidden lg:block` on sidebar

**UNotifications not showing:**

- Ensure `<UNotifications />` is mounted once in `app.vue` (outside `<NuxtLayout>`)
- `useToast()` must be called within Nuxt context
