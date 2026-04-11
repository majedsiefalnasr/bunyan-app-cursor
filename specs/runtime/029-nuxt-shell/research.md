# Research — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Created:** 2026-04-11T00:00:00Z

## Nuxt UI v2 Component Inventory (Relevant to Shell)

Source: Nuxt UI v2 docs (https://ui.nuxt.com/v2) — verified against installed version `^2.17`.

| Component           | v2 Name               | Usage in Shell                  |
| ------------------- | --------------------- | ------------------------------- |
| Vertical Navigation | `UVerticalNavigation` | AppSidebar, AppMobileDrawer     |
| Dropdown            | `UDropdown`           | AppUserMenu, LanguageSwitcher   |
| Avatar              | `UAvatar`             | AppUserMenu                     |
| Breadcrumb          | `UBreadcrumb`         | AppBreadcrumb                   |
| Progress            | `UProgress`           | AppLoadingBar                   |
| Skeleton            | `USkeleton`           | Page-level loading placeholders |
| Alert               | `UAlert`              | error.vue, AppErrorBoundary     |
| Notifications       | `UNotifications`      | AppToastProvider                |
| Button              | `UButton`             | DirectionToggle, hamburger      |
| Slide Over          | `USlideOver`          | AppMobileDrawer                 |
| Badge               | `UBadge`              | Nav item badge counts (future)  |
| Card                | `UCard`               | auth.vue centered card          |
| Icon                | `UIcon`               | Nav item icons                  |

## `UVerticalNavigation` API (v2)

```typescript
interface VerticalNavigationLink {
  label: string;        // display text
  icon?: string;        // heroicon name e.g. 'i-heroicons-home'
  to?: string;          // route path
  badge?: string | number;
  active?: boolean;     // manually control active state
  click?: () => void;
  slot?: string;        // custom slot name
  children?: VerticalNavigationLink[];  // nested items (accordion)
  disabled?: boolean;
}

// Usage
<UVerticalNavigation :links="navItems" />
```

## `USlideOver` API (v2)

```typescript
// v-model controls open state
<USlideOver v-model="isOpen" side="right">
  <template #default="{ close }">
    <!-- content -->
  </template>
</USlideOver>

// side prop: 'left' | 'right' (default 'right'; for RTL should be 'right' = visual left)
```

**RTL note:** With `dir="rtl"` on `<html>`, `side="right"` opens from the visual right (which is the physical right). For a traditional Arabic sidebar, use `side="left"` which opens from physical left (visual right in RTL).

## `useToast()` API (v2)

```typescript
const toast = useToast();
toast.add({
  title: "Success",
  description: "Done",
  color: "green",
  icon: "i-heroicons-check",
});
toast.add({
  title: "Error",
  description: "Failed",
  color: "red",
  icon: "i-heroicons-x-circle",
});
// color options: 'green' | 'red' | 'amber' | 'blue' | 'gray' | 'white' | 'black' | (primary/etc.)

// UNotifications must be mounted once in app.vue or layout:
<UNotifications />;
```

## `@nuxtjs/i18n` v9 Direction Handling

With `@nuxtjs/i18n` v9 and locale `dir` property:

- The locale's `dir` is available via `useI18n().localeProperties.value.dir`
- The `html` tag `dir` attribute is NOT automatically set by the module — it must be managed manually or via `useHead()`
- Best pattern: in `app.vue` or a composable, watch `locale` and update `document.documentElement.dir`

```typescript
// In useDirection.ts or app.vue
const { locale, localeProperties } = useI18n();
watch(
  locale,
  () => {
    const dir = localeProperties.value.dir ?? "rtl";
    document.documentElement.dir = dir;
    document.documentElement.lang = locale.value;
  },
  { immediate: true }
);
```

## Nuxt `useColorMode` for Dark Mode

With Nuxt UI v2, color mode is handled by `@nuxt/ui` which auto-imports `@nuxtjs/color-mode`. The API:

```typescript
const colorMode = useColorMode();
colorMode.preference = "dark"; // 'light' | 'dark' | 'system'
colorMode.value; // resolved mode ('light' | 'dark')
// Nuxt UI v2 adds 'dark' class to <html> element automatically
```

The `app.config.ts` controls Nuxt UI theme colors.

## `UProgress` for Page Loading (v2)

```typescript
// Bind to Nuxt page loading hooks
const nuxtApp = useNuxtApp();
const isLoading = ref(false);

nuxtApp.hook("page:start", () => {
  isLoading.value = true;
});
nuxtApp.hook("page:finish", () => {
  isLoading.value = false;
});

// Template
<UProgress v-if="isLoading" class="fixed top-0 inset-x-0 z-50" size="xs" />;
```

## Tailwind Logical Properties for RTL

Tailwind CSS v3 (installed) includes logical property utilities:

| Physical           | Logical (RTL-safe)              |
| ------------------ | ------------------------------- |
| `ml-4`             | `ms-4` (margin-inline-start)    |
| `mr-4`             | `me-4` (margin-inline-end)      |
| `pl-4`             | `ps-4` (padding-inline-start)   |
| `pr-4`             | `pe-4` (padding-inline-end)     |
| `left-0`           | `start-0`                       |
| `right-0`          | `end-0`                         |
| `text-left`        | `text-start`                    |
| `text-right`       | `text-end`                      |
| `flex-row-reverse` | not needed — `dir` handles it   |
| `space-x-4`        | `space-x-4 rtl:space-x-reverse` |

**Important:** Use logical properties everywhere in shell components. Never use `ml-`, `mr-`, `pl-`, `pr-`, `left-`, `right-` in RTL-aware components.

## `UBreadcrumb` API (v2)

```typescript
interface BreadcrumbLink {
  label: string;
  to?: string;
  icon?: string;
}

// Usage
const items = [
  { label: 'الرئيسية', to: '/' },
  { label: 'المشاريع', to: '/projects' },
  { label: 'مشروع رقم 1' },
]
<UBreadcrumb :links="items" />
```

## Existing Code Patterns (In-Codebase)

### useApi (existing — do not change)

- `$fetch.create()` with base URL from `useRuntimeConfig`
- Bearer token injection from `useAuthStore().token`
- Correlation ID header per request
- 401 → `auth.logout()` + `navigateTo('/auth/login')`
- 403/RBAC_ROLE_DENIED → `navigateTo('/dashboard')`
- Error store push + toast notification

### useErrorNotification (existing — do not change)

- `showErrorNotification({ code, message, details, statusCode })` — uses `useToast()` internally

### AppErrorBoundary (existing)

- Already mounted in `app.vue` wrapping `<NuxtLayout>`
- Catches Vue errors and renders `UAlert`
- Used as the global error boundary — no duplicate needed

## Design System Application to Shell

From DESIGN.md:

```css
/* Shadow-as-border for header */
.app-header {
  box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.08);
  /* NOT: border-bottom: 1px solid ... */
}

/* Sidebar border */
.app-sidebar {
  box-shadow: 1px 0px 0px 0px rgba(0, 0, 0, 0.08); /* RTL-safe: use logical border approach */
}

/* Font */
body {
  font-family: "Geist", "Arial", sans-serif;
}
h1,
h2,
h3 {
  font-weight: 600;
  letter-spacing: -0.96px;
} /* card title scale */
```

**Tailwind approach (preferred over custom CSS):**

```html
<!-- Header with shadow-as-border -->
<header
  class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] bg-white dark:bg-[#171717]"
></header>
```
