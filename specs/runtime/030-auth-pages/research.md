# Auth Pages — Research & Dependencies

## Frontend Framework Choices

### Nuxt.js 3 + Vue 3 + TypeScript

**Decision:** Use entire established tech stack (STAGE_29_NUXT_SHELL prerequisite).

**Why Nuxt.js 3:**

- File-based routing (auto-generates route definitions)
- Built-in SSR support (future enhancement)
- Auto-imports for composables, components, stores
- Native support for middleware (route protection)
- Excellent TypeScript support
- Large ecosystem + documentation

**Why Vue 3 Composition API:**

- Modern, reactive patterns
- Better code organization than Options API
- Easier testing and reusability
- Type-safe (works with TypeScript)
- Better performance (tree-shakeable)

**Why TypeScript:**

- Type safety for form validation schemas
- Better IDE autocomplete and refactoring
- Catch errors at compile time
- Documentation through types

**Prerequisite Status:** STAGE_29_NUXT_SHELL must complete first.

---

## Form Validation Approach

### VeeValidate v4 + Zod

**Decision:** Dual-layer validation (client-side Zod for UX, server-side backend for security).

#### Why VeeValidate?

- **Form State Management:** Handles values, errors, touched fields, submission state
- **Real-time Validation:** Blur, change, or submit triggers (configurable)
- **Framework Integration:** Native Vue 3 Composition API support
- **Error Display:** Built-in field error mapping
- **UX Control:** Can show errors after blur (avoid aggressive validation)

**Example:**

```typescript
const { handleSubmit, errors, values, isSubmitting } = useForm({
  validationSchema: toTypedSchema(loginSchema),
  validateOnBlur: true, // Validate on blur (UX-friendly)
  validateOnChange: false, // Don't validate on every keystroke
});
```

#### Why Zod?

- **Type-Safe Schemas:** TypeScript-first schema validation
- **Composable Rules:** Combine simple validators into complex schemas
- **Custom Validators:** Extend with `.refine()` for password match, unique email, etc.
- **Error Messages:** Built-in message customization (Arabic support)
- **Small Bundle:** ~8KB minified

**Example:**

```typescript
const passwordSchema = z
  .object({
    password: z.string().min(8, "كلمة المرور قصيرة جدًا"),
    confirmPassword: z.string(),
  })
  .refine((d) => d.password === d.confirmPassword, {
    message: "كلمات المرور غير متطابقة",
    path: ["confirmPassword"],
  });
```

#### Integration Pattern

```typescript
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";

const { handleSubmit, errors, values } = useForm({
  validationSchema: toTypedSchema(loginSchema), // Zod → VeeValidate
});
```

**Validation Pipeline:**

1. User types in field
2. On blur: Zod validates field
3. Error mapped to VeeValidate errors object
4. UFormField displays error below input
5. On submit: All fields validated
6. If valid: Call API action
7. If API error: Map to fields (server-side validation enforcement)

#### Alternatives Considered

| Library               | Pros                                                | Cons                                    |
| --------------------- | --------------------------------------------------- | --------------------------------------- |
| **VeeValidate + Zod** | Type-safe, composable, Arabic support, small bundle | Requires learning both                  |
| **Zod only**          | Same type-safety, no extra lib                      | No form state management                |
| **Yup**               | Similar to Zod, more popular                        | Larger bundle, less TS-first            |
| **Joi**               | Powerful, flexible                                  | Too heavy for frontend, backend-focused |
| **Custom validation** | Lightweight, control                                | Error-prone, hard to maintain           |

**Decision rationale:** VeeValidate handles form state (values, touched, errors, submitting), while Zod handles validation logic. Together they provide best UX + type safety.

---

## State Management

### Pinia v2

**Decision:** Use Pinia for all state management (STAGE_29_NUXT_SHELL prerequisite).

**Why Pinia:**

- **Composition API:** Composables-first approach, matches Vue 3 modern patterns
- **Type Safety:** Full TypeScript support, excellent IDE autocomplete
- **Modular Stores:** Separate stores for auth, user, etc.
- **Devtools:** Built-in dev tools for time-travel debugging
- **Small Bundle:** ~3KB minified
- **Persistence:** Easy to persist to localStorage

**Example Store Structure:**

```typescript
export const useAuthStore = defineStore("auth", () => {
  // State
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem("auth_token"));
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  // Computed
  const isAuthenticated = computed(() => !!token.value);

  // Actions
  const login = async (email: string, password: string) => {
    isLoading.value = true;
    try {
      const response = await useAuthApi().login(email, password);
      token.value = response.data.token;
      user.value = response.data.user;
      localStorage.setItem("auth_token", token.value);
    } catch (err) {
      error.value = err.data?.message;
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  return { user, token, isLoading, error, isAuthenticated, login };
});
```

**Alternatives Considered:**

| Library                            | Pros                                       | Cons                                  |
| ---------------------------------- | ------------------------------------------ | ------------------------------------- |
| **Pinia**                          | Composition API, type-safe, small, modular | Requires setup (done in STAGE_29)     |
| **Vuex**                           | More mature, official Redux-like           | Verbose, less modern, less TS support |
| **Composition API + localStorage** | Minimal, no extra lib                      | Hard to scale, no devtools            |
| **Redux**                          | Powerful, ecosystem                        | Overkill for SPA, complex setup       |

**Decision rationale:** Pinia provides optimal balance of power, simplicity, and type safety for authentication state.

---

## Component Library

### Nuxt UI (@nuxt/ui)

**Decision:** Use Nuxt UI for all form components (STAGE_29_NUXT_SHELL prerequisite).

**Why Nuxt UI:**

- **Pre-styled:** All components follow design system
- **Accessibility-first:** WCAG 2.1 AA compliant by default
- **RTL support:** Built-in directionality support
- **Headless UI:** Composable, themeable, customizable
- **Tailwind-based:** Integrates with Tailwind CSS v4
- **Vue 3 native:** Full TypeScript support
- **Small bundle:** Tree-shakeable, only import used components

**Component Mapping:**

| Form Element        | Nuxt UI Component | Use Case                |
| ------------------- | ----------------- | ----------------------- |
| Container           | `UCard`           | Auth form wrapper       |
| Label + Input       | `UFormField`      | Form field wrapper      |
| Text/Email/Password | `UInput`          | Basic inputs            |
| Submit/Action       | `UButton`         | Buttons                 |
| Multi-step wizard   | `USteppers`       | Registration steps      |
| Radio group         | `URadioGroup`     | Account type selector   |
| Checkbox            | `UCheckbox`       | Remember me, agreements |
| Alert/Error         | `UAlert`          | Error display, messages |
| Progress bar        | `UProgress`       | Password strength       |
| Pin/OTP input       | `UPinInput`       | 2FA (future)            |

**Installation & Import:**

```bash
npm install @nuxt/ui
npx nuxi@latest module add ui
```

**Usage Example:**

```vue
<template>
  <UCard title="تسجيل الدخول" class="w-full max-w-md">
    <UForm @submit="onSubmit" :schema="loginSchema" class="space-y-4">
      <UFormField label="البريد الإلكتروني" name="email">
        <UInput type="email" placeholder="user@example.com" />
      </UFormField>

      <UFormField label="كلمة المرور" name="password">
        <UInput type="password" placeholder="••••••••" />
      </UFormField>

      <UButton type="submit" block>تسجيل الدخول</UButton>
    </UForm>
  </UCard>
</template>
```

**RTL Support:**

- Nuxt UI components auto-detect `dir="rtl"` on `<html>`
- Automatically flip layouts, text alignment, margins
- Logical properties used throughout (no hardcoded ml-, mr-, etc.)

**Alternatives Considered:**

| Library                    | Pros                               | Cons                               |
| -------------------------- | ---------------------------------- | ---------------------------------- |
| **Nuxt UI**                | Pre-styled, accessible, RTL, small | Requires Nuxt                      |
| **Shadcn UI**              | Flexible, modern, good docs        | Larger bundle, manual updates      |
| **PrimeVue**               | Feature-rich, professional         | Heavy, complex, less RTL support   |
| **VuetifyUI**              | Material design, components        | Heavy (180KB+), overly complex     |
| **Headless UI**            | Minimal, accessible, flexible      | Requires custom styling (Tailwind) |
| **Custom (Tailwind only)** | Lightweight, full control          | Time-consuming, error-prone        |

**Decision rationale:** Nuxt UI provides best balance of pre-styled, accessible, and RTL-ready components without bloat.

---

## API Contract

### Backend Endpoints (from STAGE_03_AUTHENTICATION)

All endpoints follow Laravel RESTful conventions + Sanctum authentication.

**Base URL:** `https://api.bunyan.local/api/v1` (or environment variable)

#### POST `/api/v1/login`

**Purpose:** Authenticate user with email and password.

**Request:**

```json
{
  "email": "user@example.com",
  "password": "SecurePassword123"
}
```

**Success Response (200):**

```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": "user-123",
      "email": "user@example.com",
      "firstName": "محمد",
      "lastName": "علي",
      "accountType": "customer",
      "roles": ["customer"]
    }
  },
  "message": "تم تسجيل الدخول بنجاح"
}
```

**Error Response (401/422):**

```json
{
  "success": false,
  "data": null,
  "message": "بيانات الاعتماد غير صحيحة",
  "errors": {
    "email": ["المستخدم غير موجود"],
    "password": ["كلمة المرور غير صحيحة"]
  }
}
```

#### POST `/api/v1/register`

**Purpose:** Register new user account.

**Request:**

```json
{
  "accountType": "customer",
  "firstName": "محمد",
  "lastName": "علي",
  "email": "newuser@example.com",
  "phone": "+966501234567",
  "country": "السعودية",
  "password": "SecurePassword123"
}
```

**Success Response (201):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": "user-124",
      "email": "newuser@example.com",
      "firstName": "محمد",
      "lastName": "علي"
    }
  },
  "message": "تم التسجيل بنجاح. يرجى التحقق من بريدك الإلكتروني."
}
```

#### POST `/api/v1/forgot-password`

**Purpose:** Request password reset email.

**Request:**

```json
{
  "email": "user@example.com"
}
```

**Success Response (200):**

```json
{
  "success": true,
  "data": null,
  "message": "تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني"
}
```

#### POST `/api/v1/reset-password`

**Purpose:** Reset password with token.

**Request:**

```json
{
  "token": "reset-token-from-email",
  "password": "NewPassword123"
}
```

**Success Response (200):**

```json
{
  "success": true,
  "data": null,
  "message": "تم إعادة تعيين كلمة المرور بنجاح"
}
```

#### POST `/api/v1/validate-reset-token`

**Purpose:** Validate reset token on page load.

**Request:**

```json
{
  "token": "reset-token-from-email"
}
```

**Success Response (200):**

```json
{
  "success": true,
  "data": { "valid": true },
  "message": "Token is valid"
}
```

**Error Response (400):**

```json
{
  "success": false,
  "data": null,
  "message": "الرابط منتهي الصلاحية أو غير صالح"
}
```

#### GET `/api/v1/verify-email?token=<token>`

**Purpose:** Verify email with token from email link.

**Headers:**

```
Authorization: Bearer <token>
```

**Success Response (200):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": "user-124",
      "email": "user@example.com",
      "emailVerified": true
    }
  },
  "message": "تم التحقق من البريد الإلكتروني بنجاح"
}
```

#### POST `/api/v1/resend-verification-email`

**Purpose:** Resend verification email.

**Request:**

```json
{
  "email": "user@example.com"
}
```

**Success Response (200):**

```json
{
  "success": true,
  "data": null,
  "message": "تم إرسال رابط التحقق إلى بريدك الإلكتروني"
}
```

#### GET `/api/v1/profile` (Protected)

**Purpose:** Fetch current authenticated user profile.

**Headers:**

```
Authorization: Bearer <token>
```

**Success Response (200):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": "user-123",
      "email": "user@example.com",
      "firstName": "محمد",
      "lastName": "علي",
      "phone": "+966501234567",
      "country": "السعودية",
      "emailVerified": true,
      "roles": ["customer"]
    }
  }
}
```

**Error Response (401):**

```json
{
  "success": false,
  "data": null,
  "message": "Unauthorized"
}
```

#### PUT `/api/v1/profile` (Protected)

**Purpose:** Update user profile.

**Headers:**

```
Authorization: Bearer <token>
```

**Request:**

```json
{
  "firstName": "محمد",
  "lastName": "محمود",
  "phone": "+966501234567",
  "country": "الإمارات"
}
```

**Success Response (200):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": "user-123",
      "firstName": "محمد",
      "lastName": "محمود",
      "phone": "+966501234567",
      "country": "الإمارات"
    }
  },
  "message": "تم تحديث الملف الشخصي بنجاح"
}
```

### StandardErrorResponse Contract

All error responses follow this format:

```typescript
interface StandardErrorResponse {
  success: false;
  data: null;
  message: string; // Human-readable error message (Arabic)
  errors: Record<string, string[]>; // Field-level errors
}
```

**Example:**

```json
{
  "success": false,
  "data": null,
  "message": "فشل التحقق من صحة البيانات",
  "errors": {
    "email": ["البريد الإلكتروني غير صالح"],
    "password": ["كلمة المرور قصيرة جدًا"]
  }
}
```

### Error Codes

| Code                 | HTTP | Message (English)                    | Arabic Message                    |
| -------------------- | ---- | ------------------------------------ | --------------------------------- |
| INVALID_CREDENTIALS  | 401  | Invalid email or password            | بيانات الاعتماد غير صحيحة         |
| USER_NOT_FOUND       | 404  | User account not found               | لم يتم العثور على حساب المستخدم   |
| EMAIL_ALREADY_EXISTS | 422  | Email already registered             | البريد الإلكتروني مسجل بالفعل     |
| INVALID_TOKEN        | 400  | Reset/verify token invalid/expired   | الرابط منتهي الصلاحية أو غير صالح |
| UNAUTHORIZED         | 401  | Authentication token missing/invalid | غير مصرح                          |
| VALIDATION_ERROR     | 422  | Request validation failed            | فشل التحقق من صحة البيانات        |
| SERVER_ERROR         | 500  | Internal server error                | حدث خطأ في الخادم                 |

### Rate Limiting

Backend should enforce:

- **Login:** 5 attempts per minute per IP
- **Register:** 3 registrations per 24 hours per IP
- **Forgot Password:** 3 requests per 24 hours per email
- **API Calls:** 100 requests per minute per user (authenticated)

Frontend should respect 429 (Too Many Requests) response and display message.

---

## RTL Implementation

### Tailwind Logical Properties

**Problem:** CSS properties like `margin-left`, `text-left` assume LTR directionality. In RTL, they need to flip.

**Solution:** Use Tailwind logical properties (CSS Logical Properties & Values):

| Direction      | Traditional  | Logical (LTR) | Logical (RTL)                    |
| -------------- | ------------ | ------------- | -------------------------------- |
| Left margin    | `ml-4`       | `ms-4`        | `ms-4` → right margin            |
| Right margin   | `mr-4`       | `me-4`        | `me-4` → left margin             |
| Left padding   | `pl-4`       | `ps-4`        | `ps-4` → right padding           |
| Right padding  | `pr-4`       | `pe-4`        | `pe-4` → left padding            |
| Left text      | `text-left`  | `text-start`  | `text-start` → right             |
| Right text     | `text-right` | `text-end`    | `text-end` → left                |
| Flex direction | `flex-row`   | `flex-row`    | `flex-row-reverse` (when needed) |

**Examples:**

```vue
<!-- WRONG (assumes LTR) -->
<div class="ml-4 mr-2 text-left">Content</div>

<!-- CORRECT (works in both LTR and RTL) -->
<div class="ms-4 me-2 text-start">Content</div>
```

### Nuxt i18n Plugin Setup

**File:** `frontend/nuxt.config.ts`

```typescript
export default defineNuxtConfig({
  modules: ["@nuxtjs/i18n"],
  i18n: {
    locales: [
      {
        code: "ar",
        iso: "ar-SA",
        name: "عربي",
        file: "ar.json",
        dir: "rtl", // Mark as RTL
      },
      {
        code: "en",
        iso: "en-US",
        name: "English",
        file: "en.json",
        dir: "ltr", // Mark as LTR
      },
    ],
    defaultLocale: "ar",
    strategy: "prefix_except_default",
    vueI18n: "./i18n.config.ts",
  },
});
```

**File:** `frontend/i18n.config.ts`

```typescript
export default defineI18nConfig(() => ({
  legacy: false,
  locale: "ar",
  fallbackLocale: "en",
}));
```

**Auto-directionality:**

- Nuxt i18n plugin auto-applies `dir="rtl"` to `<html>` when locale is Arabic
- Auto-applies `dir="ltr"` for English
- Form inputs auto-align right in RTL context

### Translation Keys Example

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
        "shortPassword": "كلمة المرور يجب أن تكون 8 أحرف على الأقل"
      }
    }
  }
}
```

**Usage in components:**

```vue
<template>
  <h1>{{ $t("auth.login.title") }}</h1>
  <label>{{ $t("auth.login.email") }}</label>
</template>
```

---

## Performance Considerations

### Bundle Size Analysis

**Current estimate (before optimization):**

- Vue 3 + Nuxt: ~50KB
- Nuxt UI components: ~40KB
- VeeValidate + Zod: ~15KB
- i18n: ~8KB
- Auth pages (code-split): ~20KB
- **Total (gzipped): ~133KB**

**Target:** <100KB (gzipped)

**Optimization strategies:**

1. **Tree-shake Nuxt UI:**

   ```typescript
   // Only import used components
   import { UButton, UInput, UFormField } from "#ui/components";
   ```

2. **Tree-shake Zod:**
   - Only use schemas needed (not entire library)
   - Zod is already tiny (~8KB)

3. **Code splitting:**
   - Auth pages lazy-loaded by default (Nuxt feature)
   - Register wizard: split into per-step components

4. **CSS minification:**
   - Production build auto-minifies
   - Tailwind purges unused classes

5. **Compress images:**
   - If any icons/images, optimize + compress

**Monitor size with:**

```bash
npm run build && npm run analyze
```

### Runtime Performance

**Initial page load target:** <2s (3G throttling)

**Optimizations:**

- Debounce form validation: 300ms (prevent excessive re-renders)
- Use Composition API effectively (avoid unnecessary reactivity)
- Pinia selectors: only subscribe to needed state
- Lazy-load multi-step forms with dynamic imports

**Measure with:**

- Lighthouse audit
- WebPageTest
- Chrome DevTools performance tab

### Code Splitting

**Automatic (Nuxt):**

- Auth pages lazy-loaded by route
- Components auto-imported (no duplication)

**Manual:**

- Split register wizard into per-step components (if needed)

---

## Dependencies & Versions

### Production Dependencies

```json
{
  "@nuxt/ui": "^2.10", // Pre-built components
  "@pinia/nuxt": "^0.4", // Pinia for Nuxt
  "pinia": "^2.1", // State management
  "vee-validate": "^4.11", // Form validation
  "zod": "^3.22", // Schema validation
  "@nuxtjs/i18n": "^8.0", // Internationalization
  "nuxt": "^3.8", // Nuxt framework
  "vue": "^3.3" // Vue framework
}
```

### Development Dependencies

```json
{
  "vitest": "^1.0", // Unit test runner
  "@testing-library/vue": "^8.0", // Component testing utilities
  "@playwright/test": "^1.40", // E2E test runner
  "typescript": "^5.2", // TypeScript compiler
  "@types/node": "^20.0", // Node.js types
  "@nuxt/devtools": "^1.0" // Nuxt dev tools
}
```

### Peer Dependencies

- **From STAGE_29_NUXT_SHELL:**
  - `@vee-validate/zod` (bridge between VeeValidate + Zod)
  - `@nuxt/devtools` (optional, for development)

### Version Strategy

- **Major versions:** Lock to major (^3.0)
- **Minor/patch:** Allow updates (auto-update minor/patch)
- **Security:** Update immediately for vulnerabilities

---

## Upstream Dependencies (Must Complete First)

### STAGE_03_AUTHENTICATION (Backend)

**Required:**

- All API endpoints implemented (login, register, forgot-password, reset-password, verify-email, profile)
- StandardErrorResponse format finalized
- Rate limiting configured
- Email sending service configured (forgot-password, verify-email)
- Token generation + validation logic
- Error code standardization

**Impact:** Auth pages cannot function without backend API. Frontend will use mocked API responses during development.

**Mock API Strategy:**

```typescript
// frontend/composables/useAuthApi.ts
const useAuthApi = () => {
  const isMocked = process.env.NUXT_PUBLIC_MOCK_API === "true";

  const login = async (email, password) => {
    if (isMocked) {
      // Simulate API delay
      await new Promise((resolve) => setTimeout(resolve, 1000));
      // Return mock response
      return {
        data: {
          token: "mock-token-" + Date.now(),
          user: { id: 1, email, firstName: "محمد", lastName: "علي" },
        },
      };
    }
    // Real API call
    return $fetch("/api/v1/login", {
      method: "POST",
      body: { email, password },
    });
  };
};
```

### STAGE_29_NUXT_SHELL (Frontend)

**Required:**

- Nuxt 3 app initialized
- Nuxt UI (@nuxt/ui) installed + configured
- Pinia (@pinia/nuxt) installed + configured
- i18n (@nuxtjs/i18n) installed + configured
- Tailwind CSS v4 configured with RTL support
- TypeScript enabled
- Vitest configured for unit tests
- Playwright configured for E2E tests

**Verification:**

```bash
# Check Nuxt version
npm ls nuxt

# Check Nuxt UI available
npm ls @nuxt/ui

# Check Pinia available
npm ls pinia

# Run test suite
npm run test
npm run e2e
```

**Impact:** Auth pages depend entirely on Nuxt shell setup. Cannot start Phase 1 until STAGE_29 complete.

---

## Downstream Dependencies (Depends on This Stage)

### Pages that Depend on Auth Pages

- **Dashboard pages** (/dashboard) — Protected by auth middleware
- **Project management** (/projects/\*) — Require authenticated user
- **Contractor earnings** (/contractor/earnings) — Role-based
- **Field engineer reports** (/field-engineer/reports) — Role-based
- **Admin dashboard** (/admin/\*) — Admin role required
- **Notifications** (/notifications) — Authenticated only

### Data Dependencies

- **User roles:** Defined in auth response (customer, contractor, supervising_architect, field_engineer, admin)
- **User preferences:** Stored in user profile (locale, notification settings)
- **Permissions:** Determined by roles (RBAC)

---

## Testing Libraries & Strategies

### Unit Testing (Vitest)

**Why Vitest:**

- Fast (ESM-native, parallel execution)
- Vue 3 component testing support
- Minimal configuration
- Great IDE integration
- Small footprint

**Setup:**

```bash
npm install -D vitest @vitest/ui @testing-library/vue happy-dom
```

**Example test:**

```typescript
// tests/unit/schemas/auth.spec.ts
import { describe, it, expect } from "vitest";
import { loginSchema } from "@/schemas/auth";

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
    expect(result.error?.issues[0].message).toContain("غير صالح");
  });
});
```

**Coverage target:** >80% (statements + branches)

### E2E Testing (Playwright)

**Why Playwright:**

- Cross-browser testing (Chrome, Firefox, Safari, Edge)
- Real browser automation (not headless emulation)
- Good RTL support
- Fast, parallel execution
- Built-in debugging tools

**Setup:**

```bash
npm install -D @playwright/test
```

**Example test:**

```typescript
// tests/e2e/auth.spec.ts
import { test, expect } from "@playwright/test";

test("login with valid credentials", async ({ page }) => {
  await page.goto("/auth/login");
  await page.fill('[data-testid="email-input"]', "user@example.com");
  await page.fill('[data-testid="password-input"]', "password123");
  await page.click('[data-testid="login-button"]');
  await expect(page).toHaveURL("/dashboard");
});
```

**Browsers tested:**

- Chrome (Chromium)
- Firefox
- WebKit (Safari)
- Mobile Chrome (Android)
- Mobile Safari (iOS)

---

## Design System Reference

### Visual Language

**Source:** `DESIGN.md` (Vercel-inspired)

- **Typography:** Geist Sans (proportional), Geist Mono (code)
- **Colors:** Achromatic palette (grays, blacks, whites)
- **Shadows:** Shadow-as-border technique
- **Spacing:** 4px grid
- **Border radius:** 6px consistency

### Accessibility Standards

**WCAG 2.1 Level AA** (minimum):

- Color contrast: 4.5:1 for text
- Focus ring: 2px solid, visible on all interactive elements
- Keyboard navigation: Tab through all form fields
- Form labels: Properly associated with inputs via `for` attribute
- Error messages: Announced to screen readers with `role="alert"`

---

## Security Considerations

### Token Storage

**Current approach:** localStorage (simpler for MVP)

**Alternative:** httpOnly cookies (more secure, deferred to Phase 2)

**Why localStorage for MVP:**

- Simpler to implement
- Frontend can control token expiry logic
- Easier to debug (accessible in DevTools)
- Sufficient for MVP (backend enforces security)

**Limitations:**

- Vulnerable to XSS (mitigated by Vue's automatic escaping)
- Cannot use httpOnly (frontend must access token)

**Mitigation:**

- Vue escapes all user input by default
- Content Security Policy (CSP) configured
- HTTPS enforced in production
- Token refresh logic implemented

### Client-Side Validation

**Purpose:** UX feedback only, not security enforcement

**Security guarantee:** None (backend validates everything)

**Zod schemas provide:**

- Format validation (email format, password length)
- Type checking (string, number, boolean)
- Custom validators (password match, unique email)

**Backend enforcement:** All validation must be re-enforced server-side

### Password Security

- Never logged in console (avoid `console.log(password)`)
- Never stored unencrypted (backend hashes)
- Show/hide toggle safe (Vue escapes by default)
- Validation rules: 8+ characters (backend can enforce stronger)

### CSRF Protection

**Laravel Sanctum** handles CSRF automatically:

- CSRF token sent with login response
- Frontend includes CSRF token in subsequent requests
- Nuxt middleware can auto-attach (if configured)

### Input Sanitization

**Vue 3 auto-escapes:**

- Template interpolation: `{{ userInput }}`
- v-bind attributes: `:title="userInput"`
- v-html: NOT auto-escaped (don't use for user input)

**No manual sanitization needed** (Vue handles it)

---

## Documentation Requirements

### API Integration Guide

Document for developers:

- How to use `useAuthApi` composable
- Error handling patterns
- Token attachment/refresh logic
- StandardErrorResponse format

### Component Usage Examples

For each shared component:

- Props documentation
- Events documentation
- Usage examples (code snippets)
- RTL behavior notes

### Form Validation Examples

Guide developers on:

- Creating Zod schemas
- Integrating with VeeValidate
- Displaying errors
- Custom validators

### Testing Guide (Manual + Automated)

- Unit test setup (Vitest)
- E2E test setup (Playwright)
- Manual testing checklist
- RTL testing guide

### Troubleshooting Guide

Common issues + solutions:

- Token not persisting (localStorage check)
- API errors not displaying (StandardErrorResponse format)
- RTL layout broken (logical properties verification)
- Validation not triggering (VeeValidate configuration)
- Playwright tests failing (test ID attributes)

---

## Alternative Approaches Considered

### Form Validation

| Approach              | Pros                      | Cons                                 |
| --------------------- | ------------------------- | ------------------------------------ |
| **VeeValidate + Zod** | Best UX + type safety     | Requires learning both               |
| **Only Zod**          | Type-safe, lightweight    | No form state management (harder UX) |
| **HTML5 validation**  | Built-in, simple          | Limited, inconsistent cross-browser  |
| **Custom validation** | Full control, lightweight | Error-prone, unmaintainable          |

**Decision:** VeeValidate + Zod (best overall)

### State Management

| Approach                           | Pros                          | Cons                 |
| ---------------------------------- | ----------------------------- | -------------------- |
| **Pinia**                          | Modern, composable, type-safe | Requires setup       |
| **Vuex**                           | Mature, predictable           | Verbose, less modern |
| **Composition API + localStorage** | Minimal, no extra lib         | Doesn't scale well   |

**Decision:** Pinia (recommended for Nuxt 3)

### Token Storage

| Approach             | Pros                        | Cons                              |
| -------------------- | --------------------------- | --------------------------------- |
| **localStorage**     | Frontend can access, simple | XSS vulnerability (Vue mitigates) |
| **httpOnly cookies** | More secure, automatic      | Cannot access from JS, CSRF risk  |
| **Memory only**      | Simplest                    | Lost on page refresh              |

**Decision:** localStorage (MVP), httpOnly (future enhancement)

### RTL Support

| Approach                        | Pros                       | Cons                                    |
| ------------------------------- | -------------------------- | --------------------------------------- |
| **Tailwind logical properties** | Works everywhere, standard | Requires discipline to use consistently |
| **CSS custom properties**       | Flexible, composable       | More complex, potential duplication     |
| **Manual CSS media queries**    | Full control               | Error-prone, hard to maintain           |

**Decision:** Tailwind logical properties (standard, simple, effective)

---

## Conclusion

The proposed tech stack (Nuxt 3 + Vue 3 + VeeValidate + Zod + Pinia + Nuxt UI + Tailwind + i18n) provides:

- ✅ **Type safety** (TypeScript + Zod)
- ✅ **Modern patterns** (Composition API, Pinia)
- ✅ **Accessibility** (Nuxt UI, WCAG 2.1 AA)
- ✅ **RTL support** (Tailwind logical properties, i18n)
- ✅ **Performance** (<100KB bundle, <2s load)
- ✅ **Maintainability** (modular, well-tested, documented)
- ✅ **Scalability** (ready for future features like 2FA, refresh tokens)

All prerequisites (STAGE_29_NUXT_SHELL, STAGE_03_AUTHENTICATION) must complete before starting Phase 1 implementation.
