# Auth Pages — Quick Start Implementation Guide

## Pre-Implementation Checklist

Before starting Phase 1, verify:

- [ ] STAGE_29_NUXT_SHELL complete (Nuxt app initialized)
- [ ] Nuxt UI (@nuxt/ui) installed and configured
- [ ] Pinia (@pinia/nuxt) installed and configured
- [ ] i18n (@nuxtjs/i18n) installed and configured
- [ ] Tailwind CSS v4 configured with RTL support
- [ ] TypeScript enabled in `tsconfig.json`
- [ ] Vitest configured for unit tests
- [ ] Playwright configured for E2E tests
- [ ] Backend API documented (or mock API ready)
- [ ] Git repository initialized with feature branch strategy

**Verification commands:**

```bash
npm ls nuxt @nuxt/ui pinia @nuxtjs/i18n
npm run test -- --help  # Verify Vitest works
npm run e2e -- --help   # Verify Playwright works
```

---

## Installation & Setup

### 1. Install Additional Dependencies

```bash
npm install vee-validate@latest zod@latest @vee-validate/zod@latest

# Development dependencies
npm install -D @testing-library/vue@latest
```

**Verify installation:**

```bash
npm ls vee-validate zod @testing-library/vue
```

---

### 2. Create Directory Structure

```bash
# Pages
mkdir -p frontend/pages/auth
mkdir -p frontend/pages/profile

# Components
mkdir -p frontend/components/auth

# Stores
mkdir -p frontend/stores

# Composables
mkdir -p frontend/composables

# Schemas
mkdir -p frontend/schemas

# Middleware
mkdir -p frontend/middleware

# Locales (if not existing)
mkdir -p frontend/locales

# Tests
mkdir -p frontend/tests/unit/{schemas,stores,composables}
mkdir -p frontend/tests/e2e
```

**Verify structure:**

```bash
find frontend -type d | grep -E "(pages|components|stores|composables|schemas|middleware|locales|tests)" | sort
```

---

### 3. Create Zod Schemas

**File:** `frontend/schemas/auth.ts`

```typescript
import { z } from "zod";

export const loginSchema = z.object({
  email: z
    .string()
    .min(1, { message: "البريد الإلكتروني مطلوب" })
    .email({ message: "البريد الإلكتروني غير صالح" }),
  password: z
    .string()
    .min(8, { message: "كلمة المرور يجب أن تكون 8 أحرف على الأقل" }),
  rememberMe: z.boolean().optional().default(false),
});

export const registerStep1Schema = z.object({
  accountType: z.enum(["customer", "contractor"], {
    message: "يجب تحديد نوع الحساب",
  }),
});

export const registerStep2Schema = z.object({
  firstName: z
    .string()
    .min(2, { message: "الاسم الأول قصير جدًا" })
    .max(50, { message: "الاسم الأول طويل جدًا" }),
  lastName: z
    .string()
    .min(2, { message: "الاسم الأخير قصير جدًا" })
    .max(50, { message: "الاسم الأخير طويل جدًا" }),
  email: z.string().email({ message: "البريد الإلكتروني غير صالح" }),
});

export const registerStep3Schema = z
  .object({
    phone: z.string().regex(/^[+0-9]{7,}$/, { message: "رقم الهاتف غير صالح" }),
    country: z.string().min(2, { message: "يجب تحديد البلد" }),
    password: z
      .string()
      .min(8, { message: "كلمة المرور يجب أن تكون 8 أحرف على الأقل" }),
    confirmPassword: z.string().min(8, { message: "تأكيد كلمة المرور مطلوب" }),
  })
  .refine((d) => d.password === d.confirmPassword, {
    message: "كلمات المرور غير متطابقة",
    path: ["confirmPassword"],
  });

export const resetPasswordSchema = z
  .object({
    password: z
      .string()
      .min(8, { message: "كلمة المرور يجب أن تكون 8 أحرف على الأقل" }),
    confirmPassword: z.string().min(8, { message: "تأكيد كلمة المرور مطلوب" }),
  })
  .refine((d) => d.password === d.confirmPassword, {
    message: "كلمات المرور غير متطابقة",
    path: ["confirmPassword"],
  });

export const profileSchema = z.object({
  firstName: z
    .string()
    .min(2, { message: "الاسم الأول قصير جدًا" })
    .max(50, { message: "الاسم الأول طويل جدًا" }),
  lastName: z
    .string()
    .min(2, { message: "الاسم الأخير قصير جدًا" })
    .max(50, { message: "الاسم الأخير طويل جدًا" }),
  email: z.string().email({ message: "البريد الإلكتروني غير صالح" }),
  phone: z.string().regex(/^[+0-9]{7,}$/, { message: "رقم الهاتف غير صالح" }),
  country: z.string().min(2, { message: "يجب تحديد البلد" }),
});

// Type exports for components
export type LoginForm = z.infer<typeof loginSchema>;
export type RegisterStep1Form = z.infer<typeof registerStep1Schema>;
export type RegisterStep2Form = z.infer<typeof registerStep2Schema>;
export type RegisterStep3Form = z.infer<typeof registerStep3Schema>;
export type ResetPasswordForm = z.infer<typeof resetPasswordSchema>;
export type ProfileForm = z.infer<typeof profileSchema>;
```

---

### 4. Create Pinia Stores

**File:** `frontend/stores/auth.ts`

```typescript
import { defineStore } from "pinia";
import { ref, computed } from "vue";

export interface User {
  id: string;
  email: string;
  firstName: string;
  lastName: string;
  accountType: "customer" | "contractor";
  phone: string;
  country: string;
  emailVerified: boolean;
  roles: string[];
  preferences?: Record<string, any>;
  createdAt?: string;
  updatedAt?: string;
}

export const useAuthStore = defineStore("auth", () => {
  // State
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem("auth_token"));
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  // Computed
  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const displayName = computed(() =>
    user.value ? `${user.value.firstName} ${user.value.lastName}` : "",
  );

  // Actions
  const login = async (email: string, password: string) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await useAuthApi().login(email, password);
      token.value = response.data.token;
      user.value = response.data.user;
      localStorage.setItem("auth_token", token.value);
    } catch (err: any) {
      error.value = err.data?.message || "حدث خطأ ما";
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  const register = async (data: any) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await useAuthApi().register(data);
      return response;
    } catch (err: any) {
      error.value = err.data?.message;
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  const logout = () => {
    user.value = null;
    token.value = null;
    error.value = null;
    localStorage.removeItem("auth_token");
    navigateTo("/auth/login");
  };

  const fetchUser = async () => {
    if (!token.value) return;
    isLoading.value = true;
    try {
      const response = await useAuthApi().getProfile();
      user.value = response.data.user;
    } catch (err: any) {
      if (err.status === 401) {
        logout();
      } else {
        error.value = err.data?.message;
      }
    } finally {
      isLoading.value = false;
    }
  };

  const hasRole = (role: string) => user.value?.roles?.includes(role) ?? false;

  return {
    user,
    token,
    isLoading,
    error,
    isAuthenticated,
    displayName,
    login,
    register,
    logout,
    fetchUser,
    hasRole,
  };
});
```

**File:** `frontend/stores/user.ts`

```typescript
import { defineStore } from "pinia";
import { ref, computed } from "vue";

export interface UserProfile {
  id: string;
  email: string;
  firstName: string;
  lastName: string;
  phone: string;
  country: string;
  profileImage?: string;
  emailVerified: boolean;
  preferences: {
    locale: "ar" | "en";
    notificationsEnabled: boolean;
    theme?: "light" | "dark";
  };
  createdAt: string;
  updatedAt: string;
}

export const useUserStore = defineStore("user", () => {
  const profile = ref<UserProfile | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const isProfileLoaded = computed(() => !!profile.value);
  const preferredLocale = computed(
    () => (profile.value?.preferences?.locale || "ar") as "ar" | "en",
  );

  const fetchProfile = async () => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await useAuthApi().getProfile();
      profile.value = response.data.user;
    } catch (err: any) {
      if (err.status === 401) {
        useAuthStore().logout();
      } else {
        error.value = err.data?.message;
      }
    } finally {
      isLoading.value = false;
    }
  };

  const updateProfile = async (data: Partial<UserProfile>) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await useAuthApi().updateProfile(data);
      profile.value = response.data.user;
      // Also update auth store
      const authStore = useAuthStore();
      if (authStore.user) {
        Object.assign(authStore.user, response.data.user);
      }
      return response;
    } catch (err: any) {
      error.value = err.data?.message;
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    profile,
    isLoading,
    error,
    isProfileLoaded,
    preferredLocale,
    fetchProfile,
    updateProfile,
  };
});
```

---

### 5. Create API Composable

**File:** `frontend/composables/useAuthApi.ts`

```typescript
export const useAuthApi = () => {
  const authStore = useAuthStore();

  const withAuth = (options: any = {}) => {
    return {
      ...options,
      headers: {
        ...options.headers,
        Authorization: `Bearer ${authStore.token}`,
      },
    };
  };

  const login = async (email: string, password: string) => {
    return $fetch("/api/v1/login", {
      method: "POST",
      body: { email, password },
    });
  };

  const register = async (data: any) => {
    return $fetch("/api/v1/register", {
      method: "POST",
      body: data,
    });
  };

  const forgotPassword = async (email: string) => {
    return $fetch("/api/v1/forgot-password", {
      method: "POST",
      body: { email },
    });
  };

  const resetPassword = async (token: string, password: string) => {
    return $fetch("/api/v1/reset-password", {
      method: "POST",
      body: { token, password },
    });
  };

  const verifyEmail = async (token: string) => {
    return $fetch("/api/v1/verify-email", {
      method: "POST",
      body: { token },
    });
  };

  const resendVerification = async (email: string) => {
    return $fetch("/api/v1/resend-verification-email", {
      method: "POST",
      body: { email },
    });
  };

  const getProfile = async () => {
    return $fetch("/api/v1/profile", withAuth());
  };

  const updateProfile = async (data: any) => {
    return $fetch("/api/v1/profile", {
      method: "PUT",
      ...withAuth(),
      body: data,
    });
  };

  return {
    login,
    register,
    forgotPassword,
    resetPassword,
    verifyEmail,
    resendVerification,
    getProfile,
    updateProfile,
  };
};
```

---

### 6. Create Auth Middleware

**File:** `frontend/middleware/auth.ts`

```typescript
export default defineRouteMiddleware((to, from) => {
  const authStore = useAuthStore();

  if (!authStore.isAuthenticated) {
    return navigateTo("/auth/login");
  }
});
```

---

### 7. Create Shared Components

**File:** `frontend/components/auth/AuthLayout.vue`

```vue
<template>
  <div
    class="min-h-screen bg-white flex items-center justify-center px-4 md:px-0"
  >
    <slot />
  </div>
</template>
```

**File:** `frontend/components/auth/AuthCard.vue`

```vue
<template>
  <div class="w-full max-w-md p-6 md:p-8">
    <div v-if="title || subtitle" class="mb-8 text-center">
      <h1 v-if="title" class="text-2xl font-semibold text-gray-900 mb-2">
        {{ title }}
      </h1>
      <p v-if="subtitle" class="text-gray-600">
        {{ subtitle }}
      </p>
    </div>

    <div
      class="rounded-md p-6 md:p-8"
      style="box-shadow: 0px 0px 0px 1px rgba(0,0,0,0.08)"
    >
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps({
  title: { type: String },
  subtitle: { type: String },
  class: { type: String },
});
</script>
```

**File:** `frontend/components/auth/PasswordStrength.vue`

```vue
<template>
  <div class="mt-3">
    <div class="flex justify-between items-center mb-2">
      <span v-if="showLabel" class="text-xs text-gray-600">
        قوة كلمة المرور: {{ strengthLabel }}
      </span>
    </div>
    <UProgress :value="percentage" :color="strengthColor" />
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps({
  password: { type: String, required: true },
  showLabel: { type: Boolean, default: true },
});

const strengthLabel = computed(() => {
  const score = calculateStrength(props.password);
  if (score < 25) return "ضعيف";
  if (score < 50) return "متوسط";
  if (score < 75) return "جيد";
  return "قوي";
});

const percentage = computed(() => calculateStrength(props.password));

const strengthColor = computed(() => {
  const score = calculateStrength(props.password);
  if (score < 25) return "red";
  if (score < 50) return "yellow";
  if (score < 75) return "yellow";
  return "green";
});

function calculateStrength(password: string): number {
  let score = 0;
  if (password.length >= 8) score += 20;
  if (password.length >= 12) score += 10;
  if (/[a-z]/.test(password)) score += 20;
  if (/[A-Z]/.test(password)) score += 20;
  if (/[0-9]/.test(password)) score += 15;
  if (/[!@#$%^&*]/.test(password)) score += 15;
  return Math.min(100, score);
}
</script>
```

---

### 8. Update Locale Files

**File:** `frontend/locales/ar.json`

```json
{
  "auth": {
    "login": {
      "title": "تسجيل الدخول",
      "email": "البريد الإلكتروني",
      "password": "كلمة المرور",
      "rememberMe": "تذكرني",
      "submit": "تسجيل الدخول",
      "forgotPassword": "هل نسيت كلمة المرور؟",
      "register": "إنشاء حساب جديد",
      "errors": {
        "invalidEmail": "البريد الإلكتروني غير صالح",
        "invalidCredentials": "بيانات الاعتماد غير صحيحة",
        "shortPassword": "كلمة المرور قصيرة جدًا"
      }
    },
    "register": {
      "title": "إنشاء حساب",
      "step1": "نوع الحساب",
      "step2": "المعلومات الشخصية",
      "step3": "معلومات الاتصال",
      "step4": "التحقق من البريد",
      "customer": "عميل",
      "contractor": "مقاول",
      "firstName": "الاسم الأول",
      "lastName": "الاسم الأخير"
    }
  }
}
```

---

## Testing Setup

### Unit Tests (Vitest)

**File:** `frontend/tests/unit/schemas/auth.spec.ts`

```typescript
import { describe, it, expect } from "vitest";
import { loginSchema, registerStep1Schema } from "@/schemas/auth";

describe("Auth Schemas", () => {
  describe("loginSchema", () => {
    it("validates correct email and password", () => {
      const result = loginSchema.safeParse({
        email: "user@example.com",
        password: "password123",
      });
      expect(result.success).toBe(true);
    });

    it("rejects invalid email", () => {
      const result = loginSchema.safeParse({
        email: "invalid",
        password: "password123",
      });
      expect(result.success).toBe(false);
      const errorMessage = result.error?.issues[0].message;
      expect(errorMessage).toContain("غير صالح");
    });

    it("rejects short password", () => {
      const result = loginSchema.safeParse({
        email: "user@example.com",
        password: "pass123",
      });
      expect(result.success).toBe(false);
    });
  });
});
```

**Run tests:**

```bash
npm run test -- frontend/tests/unit/schemas/auth.spec.ts
```

### E2E Tests (Playwright)

**File:** `frontend/tests/e2e/auth.spec.ts`

```typescript
import { test, expect } from "@playwright/test";

test.describe("Auth Pages", () => {
  test("login page loads", async ({ page }) => {
    await page.goto("/auth/login");
    await expect(page.locator("h1")).toContainText("تسجيل الدخول");
  });

  test("login with valid credentials", async ({ page }) => {
    await page.goto("/auth/login");
    await page.fill('[data-testid="email-input"]', "user@example.com");
    await page.fill('[data-testid="password-input"]', "password123");
    await page.click('[data-testid="login-button"]');

    // Wait for redirect (adjust based on your app behavior)
    await page.waitForURL("/dashboard", { timeout: 5000 });
  });
});
```

**Run E2E tests:**

```bash
npm run e2e -- frontend/tests/e2e/auth.spec.ts
```

---

## Development Workflow

### 1. Start Development Server

```bash
npm run dev
```

Server runs at `http://localhost:3000` (or configured port)

### 2. Test as You Build

**Watch mode (Vitest):**

```bash
npm run test -- --watch
```

**E2E testing (interactive):**

```bash
npm run e2e -- --ui
```

### 3. Monitor Bundle Size

```bash
npm run build
npm run analyze  # If available
```

**Expected:** <100KB (gzipped) for auth pages

### 4. Performance Check

```bash
npm run build  # Production build
# Then use Lighthouse or WebPageTest on deployed build
```

**Expected:** <2s initial load (3G throttling)

---

## Troubleshooting

### Token Not Persisting

```bash
# Check in browser DevTools Console:
localStorage.getItem('auth_token')  # Should return token or null

# Check Pinia state:
useAuthStore().token                # Should show token
useAuthStore().isAuthenticated      # Should be true/false
```

**Fix:** Verify localStorage isn't disabled, check middleware path

### API Errors Not Displaying

```typescript
// Check error response format
try {
  await login(email, password);
} catch (error) {
  console.log("Error:", error.data); // Verify StandardErrorResponse format
}
```

**Fix:** Ensure backend returns StandardErrorResponse with `success`, `data`, `message`, `errors` fields

### RTL Layout Broken

```bash
# Check HTML dir attribute:
document.documentElement.dir  # Should be "rtl" in Arabic mode

# Check logical properties applied:
getComputedStyle(element).marginInlineStart  # Check margin
```

**Fix:** Ensure using ms-, me-, ps-, pe-, text-start, text-end classes only

### Validation Not Triggering

```typescript
// Check VeeValidate config:
useForm({
  validationSchema: toTypedSchema(loginSchema),
  validateOnBlur: true, // Should be true
  validateOnChange: false, // Optional
});
```

**Fix:** Verify toTypedSchema correctly converts Zod schema

### Playwright Tests Failing

```typescript
// Ensure test IDs on elements:
<input data-testid="email-input" />

// Wait for elements to appear:
await page.waitForSelector('[data-testid="email-input"]')
await page.fill('[data-testid="email-input"]', 'test@example.com')
```

**Fix:** Add data-testid attributes to all form elements

---

## Performance Optimization Checklist

- [ ] Bundle size < 100KB (gzipped): `npm run analyze`
- [ ] Initial load < 2s (3G): DevTools Network tab
- [ ] Validation debounced (300ms): Check VeeValidate config
- [ ] Form fields touch-friendly (48px min): Inspector
- [ ] No console errors: DevTools Console
- [ ] RTL layout works: Switch locale to Arabic
- [ ] Design system colors correct: Check DESIGN.md palette
- [ ] Accessibility: Run axe DevTools extension
- [ ] All tests pass: `npm run test && npm run e2e`

---

## Deployment Checklist

Before pushing to production:

- [ ] All unit tests pass (>80% coverage)
- [ ] All E2E tests pass
- [ ] RTL layout verified on mobile devices
- [ ] Accessibility audit (WCAG 2.1 AA)
- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] Bundle size analyzed and optimized
- [ ] Performance tested on 3G throttling
- [ ] Backend API endpoints verified
- [ ] Error messages in Arabic reviewed
- [ ] Design system compliance verified
- [ ] Git commits meaningful + documented

---

## Next Steps

1. **Phase 1 (Days 1-2):** Build foundation (components, stores, schemas)
2. **Phase 2 (Days 2-3):** Implement pages (login, register, password reset)
3. **Phase 3 (Day 4):** Polish (OtpInput, error handling, responsiveness)
4. **Phase 4 (Days 4-5):** Test (unit + E2E, performance, RTL verification)
5. **Review & Deploy:** Final review, merge, deploy

---

## Resources

- **Nuxt 3:** https://nuxt.com
- **Vue 3:** https://vuejs.org
- **Nuxt UI:** https://ui.nuxt.com
- **Pinia:** https://pinia.vuejs.org
- **VeeValidate:** https://vee-validate.logaretm.com
- **Zod:** https://zod.dev
- **Vitest:** https://vitest.dev
- **Playwright:** https://playwright.dev
- **i18n:** https://v8.i18n.nuxtjs.org

---

## Support

For questions or issues:

1. Check this guide (Troubleshooting section)
2. Review spec files (plan.md, research.md, data-model.md)
3. Check API contract (contracts/api-contract.md)
4. Ask in team chat with reproduction steps
