# Testing Guide — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Generated:** 2026-04-11T00:00:00Z

## Prerequisites

```bash
cd frontend
npm install
```

Backend is NOT required for this stage (pure frontend).

## Running Tests

### Unit Tests (Vitest)

```bash
cd frontend
npm run test
# Expected: 33 tests, 12 files, all pass
```

### Run specific test file

```bash
cd frontend
npm run test -- tests/unit/composables/useDirection.spec.ts
npm run test -- tests/unit/composables/useAuth.spec.ts
npm run test -- tests/unit/composables/useBreadcrumb.spec.ts
```

### E2E Tests (Playwright — requires dev server)

```bash
# Terminal 1: start dev server
cd frontend && npm run dev

# Terminal 2: run E2E tests
cd frontend && npm run test:e2e
# Or with UI:
cd frontend && npm run test:e2e:ui
```

### Lint + Typecheck

```bash
cd frontend
npm run lint         # ESLint
npm run typecheck    # nuxi typecheck (TypeScript)
npm run format:check # Prettier
```

## Manual Test Scenarios

### Scenario 1 — Default Layout Shell Renders

**Preconditions:**

- `npm run dev` running on `http://localhost:3000`
- Navigate to `http://localhost:3000/ar/`

**Steps:**

1. Open `http://localhost:3000/ar/` in browser
2. Verify header is visible at the top
3. Verify sidebar is visible on left (desktop, ≥1024px)
4. Verify footer is visible at the bottom
5. Verify brand text shows "بنيان"

**Expected Result:**

- Header shows logo, toggles, user menu
- Sidebar shows navigation items
- Footer shows copyright
- Page uses Geist font (check DevTools)
- `html[dir="rtl"]` is set (check Elements)

---

### Scenario 2 — RTL/LTR Direction Toggle

**Preconditions:**

- App running at `http://localhost:3000/ar/`

**Steps:**

1. Open DevTools → Elements → verify `<html dir="rtl">`
2. Click the direction toggle button (shows "EN" label)
3. Verify `<html dir="ltr">` in Elements
4. Verify sidebar moves from right to left side
5. Reload page
6. Verify direction is still "ltr" (persisted)
7. Check `localStorage.getItem('bunyan-direction')` in Console → should be `"ltr"`

**Expected Result:**

- Direction toggles between RTL and LTR
- Persists across page reload
- All components mirror correctly

---

### Scenario 3 — Dark Mode Toggle

**Preconditions:**

- App running at `http://localhost:3000/ar/`

**Steps:**

1. Verify light mode is active (white background)
2. Click the theme toggle button (moon icon)
3. Verify `<html class="dark">` in Elements
4. Verify background color changes to dark
5. Click again → returns to light mode

**Expected Result:**

- Dark mode applies immediately
- `html.dark` class set/removed
- All Nuxt UI components respond to dark mode

---

### Scenario 4 — Language Switch AR/EN

**Preconditions:**

- App running at `http://localhost:3000/ar/`

**Steps:**

1. Click language switcher in header (shows "العربية")
2. Select "English" from dropdown
3. Verify URL changes to `http://localhost:3000/en/`
4. Verify nav labels switch to English ("Dashboard", "Projects", etc.)
5. Verify direction changes to LTR (`html[dir="ltr"]`)

**Expected Result:**

- URL updates to `/en/` prefix
- All i18n strings update
- Direction auto-syncs to LTR for English

---

### Scenario 5 — Mobile Navigation Drawer

**Preconditions:**

- App running at `http://localhost:3000/ar/`
- DevTools → Mobile viewport 375x812 (iPhone 12)

**Steps:**

1. Verify sidebar is NOT visible (hidden on mobile)
2. Verify hamburger icon (three bars) is visible in header
3. Click hamburger → drawer slides in from left
4. Verify nav items are visible in drawer
5. Click "×" close button or backdrop
6. Verify drawer closes

**Expected Result:**

- Sidebar hidden below lg breakpoint
- Drawer opens/closes correctly
- Focus trap works in drawer (Tab key stays inside)

---

### Scenario 6 — Auth Layout (Centered Card)

**Preconditions:**

- App running

**Steps:**

1. Navigate to `http://localhost:3000/ar/auth/login`
2. Verify no sidebar or header navigation visible
3. Verify centered `UCard` wrapper
4. Verify brand logo above card

**Expected Result:**

- Clean auth layout with zero chrome
- Card centered vertically and horizontally

---

### Scenario 7 — Role-Based Navigation

**Preconditions:**

- Pinia auth store has user with specific role

**Steps (using Pinia Devtools):**

1. Open `http://localhost:3000/ar/`
2. Open Vue DevTools → Pinia → auth store
3. Set `user.role = 'customer'`
4. Verify sidebar shows: Home, Dashboard, Projects, Products (not Admin, Reports)
5. Change `user.role = 'admin'`
6. Verify sidebar shows ALL items including Admin

**Expected Result:**

- Navigation items filtered by role
- Customer does not see admin/reports
- Admin sees all items

---

### Scenario 8 — Toast Notifications

**Preconditions:**

- App running with Vue DevTools

**Steps (using browser console):**

```javascript
// Open browser console and run:
const { useNotification } = await import("/composables/useNotification.ts");
const { notify } = useNotification();
notify.success("العملية نجحت!", "نجاح");
notify.error("حدث خطأ ما", "خطأ");
```

**Expected Result:**

- Success toast (green) appears top-right (or top-left in RTL)
- Error toast (red) appears
- Toasts auto-dismiss after ~3 seconds

---

## Common Issues

| Issue                           | Cause                        | Fix                                                  |
| ------------------------------- | ---------------------------- | ---------------------------------------------------- |
| Sidebar not showing             | `ui.isSidebarOpen` is false  | Check Pinia devtools, or screen < lg breakpoint      |
| Direction not persisting        | `localStorage` blocked       | Check browser privacy settings                       |
| Toast not showing               | `UNotifications` not mounted | Verify `<AppToastProvider />` is in `app.vue`        |
| i18n keys showing raw           | Missing translation file key | Add key to `ar.json` and `en.json`                   |
| E2E tests fail (ECONNREFUSED)   | Dev server not running       | Run `npm run dev` first                              |
| `admin.vue` layout still in use | Deprecated not deleted       | Update page `definePageMeta` to use `default` layout |
