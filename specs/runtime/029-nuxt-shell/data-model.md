# Data Model — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Created:** 2026-04-11T00:00:00Z

## Frontend State Model

No database changes in this stage. All state is client-side.

### Pinia Store: `auth` (extended)

```typescript
// stores/auth.ts — extended from existing stub
interface UserProfile {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  avatar?: string | null;
}

type UserRole = "customer" | "contractor" | "architect" | "engineer" | "admin";

interface AuthState {
  token: string | null; // existing
  user: UserProfile | null; // NEW
}

// Existing actions preserved: setToken(), logout()
// New action: setUser(profile: UserProfile)
// Computed getters:
//   isAuthenticated: boolean = !!token
//   userRole: UserRole | null = user?.role ?? null
```

### Pinia Store: `ui` (new)

```typescript
// stores/ui.ts
type Direction = "rtl" | "ltr";
type ColorMode = "light" | "dark" | "system";

interface UIState {
  isSidebarOpen: boolean; // desktop sidebar collapsed state
  direction: Direction; // 'rtl' | 'ltr'
  colorMode: ColorMode; // 'light' | 'dark' | 'system'
}

// Actions:
//   toggleSidebar(): void
//   setSidebarOpen(open: boolean): void
//   setDirection(dir: Direction): void
//   toggleDirection(): void
//   setColorMode(mode: ColorMode): void
```

### Composable: `useAuth`

```typescript
// composables/useAuth.ts
export function useAuth() {
  const store = useAuthStore();
  const router = useRouter();

  const user = computed(() => store.user);
  const role = computed(() => store.user?.role ?? null);
  const isAuthenticated = computed(() => !!store.token);

  async function logout() {
    store.logout();
    await navigateTo("/ar/auth/login");
  }

  function hasRole(...roles: UserRole[]): boolean {
    return role.value !== null && roles.includes(role.value);
  }

  return { user, role, isAuthenticated, logout, hasRole };
}
```

### Composable: `useDirection`

```typescript
// composables/useDirection.ts
export function useDirection() {
  const uiStore = useUIStore();

  const direction = computed(() => uiStore.direction);

  function setDirection(dir: "rtl" | "ltr") {
    uiStore.setDirection(dir);
    if (import.meta.client) {
      document.documentElement.dir = dir;
      document.documentElement.lang = dir === "rtl" ? "ar" : "en";
      localStorage.setItem("bunyan-direction", dir);
    }
  }

  function toggleDirection() {
    setDirection(direction.value === "rtl" ? "ltr" : "rtl");
  }

  function initDirection() {
    if (import.meta.client) {
      const saved = localStorage.getItem("bunyan-direction") as
        | "rtl"
        | "ltr"
        | null;
      if (saved) setDirection(saved);
    }
  }

  return { direction, setDirection, toggleDirection, initDirection };
}
```

### Composable: `useBreadcrumb`

```typescript
// composables/useBreadcrumb.ts
export interface BreadcrumbItem {
  label: string;
  to?: string;
  icon?: string;
}

export function useBreadcrumb() {
  const items = useState<BreadcrumbItem[]>("breadcrumb", () => []);

  function setBreadcrumb(newItems: BreadcrumbItem[]) {
    items.value = newItems;
  }

  function clearBreadcrumb() {
    items.value = [];
  }

  return { items: readonly(items), setBreadcrumb, clearBreadcrumb };
}
```

### Composable: `useNotification`

```typescript
// composables/useNotification.ts
export function useNotification() {
  const toast = useToast();

  const notify = {
    success(message: string, title?: string) {
      toast.add({
        title: title ?? "",
        description: message,
        color: "green",
        icon: "i-heroicons-check-circle",
      });
    },
    error(message: string, title?: string) {
      toast.add({
        title: title ?? "",
        description: message,
        color: "red",
        icon: "i-heroicons-x-circle",
      });
    },
    info(message: string, title?: string) {
      toast.add({
        title: title ?? "",
        description: message,
        color: "blue",
        icon: "i-heroicons-information-circle",
      });
    },
    warning(message: string, title?: string) {
      toast.add({
        title: title ?? "",
        description: message,
        color: "amber",
        icon: "i-heroicons-exclamation-triangle",
      });
    },
  };

  return { notify };
}
```

### Navigation Config Type

```typescript
// config/navigation.ts
export type UserRole =
  | "customer"
  | "contractor"
  | "architect"
  | "engineer"
  | "admin";

export interface NavItem {
  labelKey: string;
  to: string;
  icon: string;
  roles: UserRole[];
  badge?: number;
}
```

## TypeScript Type Definitions

```typescript
// types/auth.ts (new file)
export type UserRole =
  | "customer"
  | "contractor"
  | "architect"
  | "engineer"
  | "admin";

export interface UserProfile {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  avatar?: string | null;
}

// types/ui.ts (new file)
export type Direction = "rtl" | "ltr";
export type ColorMode = "light" | "dark" | "system";
```
