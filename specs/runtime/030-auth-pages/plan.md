# STAGE_30 — Auth Pages Plan

## Executive Summary

Auth Pages implements a complete, production-ready authentication experience for the Bunyan platform. This includes 6 frontend pages (Login, Register, Forgot Password, Reset Password, Email Verification, Profile), 5 reusable auth components, Pinia state management with token persistence, and VeeValidate + Zod form validation with full Arabic RTL support.

The implementation follows a phased approach: **Foundation → Pages → Supporting Pages → Testing & Polish**, designed for parallel development and early validation through mocked API responses.

**Key metrics:**

- **Timeline:** 5 days (8 hours/day, 40 hours total)
- **Bundle target:** <100KB (gzipped)
- **Performance target:** <2s initial load (3G throttling)
- **Test coverage:** >80% (unit + E2E)
- **Accessibility:** WCAG 2.1 Level AA compliance

---

## Implementation Timeline

### Phase 1: Foundation (Days 1–2, 16 hours)

**Objective:** Build reusable components and state infrastructure.

#### Day 1: Components & Schemas (8 hours)

- **2h:** AuthLayout component
  - RTL-aware flex layout wrapper
  - Full viewport height background (white)
  - Responsive padding (16px mobile, 0 desktop)
  - Center content horizontally/vertically
  - Support for shadow-as-border styling

- **1h:** AuthCard component
  - Max-width: 400px on desktop
  - Shadow-as-border: `box-shadow: 0px 0px 0px 1px rgba(0,0,0,0.08)`
  - Padding: 24px desktop, 16px mobile
  - Props: title, subtitle, class
  - Slot for form content

- **1.5h:** PasswordStrength component
  - Real-time strength calculation (Weak/Fair/Good/Strong)
  - UProgress bar with color transitions
  - Analyzes: length, uppercase, lowercase, numbers, special chars
  - Arabic labels: "ضعيف" → "قوي"
  - Props: password, showLabel
  - No external dependencies (pure logic)

- **1.5h:** RoleSelector component
  - URadioGroup wrapper
  - Two options: "عميل" (Customer), "مقاول" (Contractor)
  - Optional descriptions for each role
  - v-model binding with update:modelValue emit
  - Props: modelValue, disabled

- **2h:** Zod schemas in `frontend/schemas/auth.ts`
  - loginSchema (email, password, rememberMe)
  - registerStep1Schema (accountType)
  - registerStep2Schema (firstName, lastName, email)
  - registerStep3Schema (phone, country, password, confirmPassword with match)
  - resetPasswordSchema (password, confirmPassword with match)
  - profileSchema (firstName, lastName, email, phone, country)
  - All error messages in Arabic
  - Custom validators for complex rules

#### Day 2: State Management & API (8 hours)

- **3h:** Pinia store setup
  - useAuthStore: user, token, isLoading, error, isAuthenticated
    - Actions: login, logout, register, fetchUser, refreshToken
    - Computed: isAuthenticated, displayName, hasRole
    - localStorage persistence for token (key: "auth_token")
  - useUserStore: profile, isLoading, error
    - Actions: fetchProfile, updateProfile
    - Computed: isProfileLoaded, preferredLocale

- **2h:** API composable (`frontend/composables/useAuthApi.ts`)
  - Methods: login, register, forgotPassword, resetPassword, verifyEmail, getProfile, updateProfile
  - Error handling: catch StandardErrorResponse, extract field errors
  - Request interceptor: attach auth token from localStorage
  - Response interceptor: handle 401 (expired token) → redirect to login

- **2h:** Auth middleware (`frontend/middleware/auth.ts`)
  - Protect routes requiring authentication
  - Redirect unauthenticated users to /auth/login
  - Preserve attempted URL for post-login redirect
  - Skip protection for public routes (/auth/\*, /register, etc.)

- **1h:** i18n setup & locale files
  - `frontend/locales/ar.json` — Arabic translations (auth pages section)
  - `frontend/locales/en.json` — English translations
  - Test i18n plugin integration with Nuxt

---

### Phase 2: Pages (Days 2–3, 16 hours)

**Objective:** Implement all 6 pages with form validation and API integration.

#### Day 2 PM (4 hours) + Day 3 (12 hours)

- **3h:** Login page (`frontend/pages/auth/login.vue`)
  - Form fields: email, password, rememberMe checkbox
  - Zod schema validation via VeeValidate
  - Submit → POST /api/v1/login
  - On success: store token, user state, redirect to /dashboard
  - On error: display UAlert with Arabic error message
  - Loading state on button
  - RTL-aware inputs
  - Show/hide password toggle
  - Links: "Forgot password?" and "Register"

- **4h:** Register page (`frontend/pages/auth/register.vue`) — **Multi-step complexity**
  - Step 1: Account type selection (RoleSelector component)
  - Step 2: Personal information (firstName, lastName, email)
  - Step 3: Contact information (phone, country, password, confirmPassword)
  - Step 4: Verification pending (email check message + resend button)
  - USteppers component for progress display
  - Each step validates before advancing
  - Back/Next/Submit button logic
  - Form state persistence via Pinia store (survive page refresh)
  - Step 3 submit → POST /api/v1/register → move to Step 4
  - Step 4: Await verification webhook (auto-redirect on success or manual button click)

- **2h:** Forgot Password page (`frontend/pages/auth/forgot-password.vue`)
  - Email input field
  - Form validation via Zod schema
  - Submit → POST /api/v1/forgot-password
  - On success: display UAlert "تحقق من بريدك الإلكتروني"
  - On error: display error UAlert in Arabic
  - Loading state
  - Link back to login

- **2h:** Reset Password page (`frontend/pages/auth/reset-password.vue`)
  - Extract token from URL query parameter (?token=abc123)
  - On load: validate token via POST /api/v1/validate-reset-token
  - If invalid: show error page with link to /auth/forgot-password
  - If valid: show form with password fields
  - Form fields: newPassword, confirmPassword
  - PasswordStrength component below newPassword
  - Submit → POST /api/v1/reset-password
  - On success: redirect to /auth/login with message
  - On error: display error UAlert in Arabic

- **2h:** Email Verification page (`frontend/pages/auth/verify-email.vue`)
  - Extract token from URL query parameter (?token=xyz789)
  - On load: POST /api/v1/verify-email with token
  - Loading spinner while verifying
  - On success: show success message "تم التحقق من بريدك الإلكتروني بنجاح"
  - Auto-redirect to /dashboard after 3s (or manual button click)
  - On error: show error message
  - Resend button → POST /api/v1/resend-verification-email

- **3h:** Profile page (`frontend/pages/profile/index.vue`) — **Protected route**
  - Protected by middleware/auth.ts
  - On load: GET /api/v1/profile → fetch user data into useUserStore
  - Form fields: firstName, lastName, email (read-only), phone, country
  - Form pre-populated with user data
  - Zod schema validation
  - Save button → PUT /api/v1/profile
  - Cancel button → revert to original state
  - On success: update user store + display success UAlert
  - On error: display error UAlert with field-level errors
  - Loading state on save button

---

### Phase 3: Supporting Components (Day 4, 8 hours)

**Objective:** Complete OtpInput component and layout refinements.

- **2h:** OtpInput component (`frontend/components/auth/OtpInput.vue`)
  - UPinInput wrapper
  - Props: modelValue, length (default: 6), disabled
  - Events: update:modelValue, complete
  - Auto-focus between fields
  - Numeric input only
  - Auto-submit on completion (optional)
  - Used for future 2FA (not required for MVP, but structure prepared)

- **2h:** Default auth layout (`frontend/layouts/auth.vue`)
  - Apply AuthLayout wrapper globally for /auth/\* routes
  - Ensure all auth pages use consistent styling
  - Support for sidebar/navigation if needed (deferred)

- **2h:** Error handling & UI feedback
  - Standardize error display across all forms
  - Field-level errors below inputs (red text via UFormField)
  - Form-level errors in UAlert at top
  - Server-side field error mapping
  - Structured error logging

- **2h:** Responsive design refinement
  - Mobile (<768px): full-width forms, touch-friendly sizing
  - Tablet (768–1024px): centered container, readable text
  - Desktop (>1024px): max-width 400px, centered on page
  - Form field heights: minimum 44px for touch
  - Button sizes: 48px minimum for mobile

---

### Phase 4: Testing & Polish (Days 4–5, 16 hours)

**Objective:** Comprehensive testing and performance optimization.

#### Unit Tests (Vitest) — 8 hours

- **2h:** Schema validation tests (`frontend/tests/unit/schemas/auth.spec.ts`)
  - loginSchema: valid/invalid email, password length, optional fields
  - registerStep schemas: field length, email format, password match
  - resetPasswordSchema: password match validation
  - profileSchema: field validation
  - All error messages in Arabic

- **3h:** Store tests (`frontend/tests/unit/stores/auth.spec.ts` & `user.spec.ts`)
  - useAuthStore initialization and state
  - login action: mocked API, token storage, redirect logic
  - logout action: state cleanup, localStorage clear
  - register action: multi-step form handling
  - useUserStore: profile fetch/update, state persistence
  - Error handling and edge cases

- **2h:** Composable tests (`frontend/tests/unit/composables/useAuthApi.spec.ts`)
  - API method mocking with vitest
  - Error response handling (StandardErrorResponse)
  - Field error extraction and mapping
  - Token attachment to requests
  - 401 handling (expired token)

- **1h:** Component tests (optional snapshot tests)
  - PasswordStrength: strength calculation accuracy
  - RoleSelector: v-model binding, emit events
  - AuthCard: props and styling

#### E2E Tests (Playwright) — 6 hours

- **2h:** Login flow tests (`frontend/tests/e2e/auth.spec.ts`)
  - Valid credentials → redirect to dashboard
  - Invalid credentials → error UAlert in Arabic
  - Remember me functionality
  - Links (forgot password, register)
  - Password show/hide toggle

- **2h:** Registration multi-step tests
  - Complete 4-step registration flow
  - Each step validation
  - Back button navigation
  - Form data persistence across steps
  - Step 4: verification pending state

- **1h:** Password reset flow tests
  - Forgot password email submission
  - Reset password with valid token
  - Reset password with expired token
  - Redirect to login on success

- **1h:** RTL & accessibility tests
  - HTML dir="rtl" attribute verification
  - Logical properties applied (no ml-, mr-, etc.)
  - Arabic error messages render correctly
  - Keyboard navigation functional
  - Focus ring visible on all interactive elements

#### Performance Optimization & Polish — 2 hours

- **1h:** Bundle size optimization
  - Tree-shake unused Nuxt UI components
  - Minify CSS/JS output
  - Lazy-load auth pages (default Nuxt behavior)
  - Monitor gzipped size: target <100KB total
  - Run: `npm run build && npm run analyze`

- **1h:** Load time optimization
  - Test initial page load (3G throttling): target <2s
  - Measure Time to Interactive
  - Optimize Zod schema parsing
  - Enable code splitting for multi-step forms
  - Run Lighthouse audit on all pages

#### Final Verification (2 hours)

- **1h:** Cross-browser testing
  - Chrome, Firefox, Safari, Edge (latest 2 versions)
  - Mobile browsers: iOS Safari, Chrome Android
  - RTL layout on all browsers

- **1h:** Design system compliance review
  - Typography: Geist Sans, font weights, letter-spacing
  - Colors: achromatic palette, error/success states
  - Shadows: shadow-as-border technique
  - Spacing: 4/8/12/16px grid
  - Border radius: 6px consistency
  - RTL: logical properties coverage

---

## Architecture Overview

### Frontend Structure

#### Pages Directory (`frontend/pages/`)

```
pages/
├── auth/
│   ├── login.vue                    # /auth/login
│   ├── register.vue                 # /auth/register (4-step wizard)
│   ├── forgot-password.vue          # /auth/forgot-password
│   ├── reset-password.vue           # /auth/reset-password?token=<token>
│   └── verify-email.vue             # /auth/verify-email?token=<token>
└── profile/
    └── index.vue                    # /profile (protected)
```

#### Components Directory (`frontend/components/auth/`)

```
components/auth/
├── AuthLayout.vue                   # RTL-aware layout wrapper
├── AuthCard.vue                     # Card container with shadow-as-border
├── PasswordStrength.vue             # Real-time strength indicator
├── RoleSelector.vue                 # Account type selector (URadioGroup)
└── OtpInput.vue                     # PIN input wrapper (for future 2FA)
```

#### Stores & Composables

```
stores/
├── auth.ts                          # useAuthStore
└── user.ts                          # useUserStore

composables/
├── useAuthApi.ts                    # API integration
└── usePasswordStrength.ts           # Password strength logic

middleware/
└── auth.ts                          # Route protection

schemas/
└── auth.ts                          # Zod schemas

layouts/
└── auth.vue                         # Default layout for /auth/* routes

locales/
├── ar.json                          # Arabic translations
└── en.json                          # English translations
```

### Component Hierarchy

```
Frontend Layer (Nuxt.js 3 + Vue 3)
│
├─ AuthLayout
│  └─ AuthCard
│     ├─ Form (UForm via VeeValidate)
│     │  ├─ UFormField (Email)
│     │  ├─ UFormField (Password with show/hide)
│     │  ├─ UCheckbox (Remember me)
│     │  └─ UButton (Submit)
│     └─ Links (Forgot password, Register)
│
├─ RoleSelector (URadioGroup)
│  ├─ URadio (Customer)
│  └─ URadio (Contractor)
│
├─ PasswordStrength
│  └─ UProgress (with color transitions)
│
├─ OtpInput (for 2FA)
│  └─ UPinInput (Nuxt UI component)
│
└─ USteppers (Register multi-step)
   ├─ Step 1: RoleSelector
   ├─ Step 2: Personal info form
   ├─ Step 3: Contact info + PasswordStrength
   └─ Step 4: Verification pending
```

### State Management Flow

```
┌─────────────────────────────────────────┐
│       Pinia Store (useAuthStore)        │
├─────────────────────────────────────────┤
│ State:                                  │
│  - user: User | null                    │
│  - token: string | null                 │
│  - isLoading: boolean                   │
│  - error: string | null                 │
│  - isAuthenticated: computed            │
│                                         │
│ Actions:                                │
│  - login(email, password)               │
│  - register(data)                       │
│  - logout()                             │
│  - fetchUser()                          │
│  - refreshToken()                       │
│                                         │
│ Persistence:                            │
│  - localStorage (key: "auth_token")    │
└─────────────────────────────────────────┘
           │
           ├─→ Page load: Check token
           │   ├─ If exists: fetchUser()
           │   └─ If missing: redirect to login
           │
           ├─→ Login form: login() action
           │   ├─ POST /api/v1/login
           │   ├─ Store token + user
           │   └─ Redirect to /dashboard
           │
           ├─→ Protected pages: auth middleware
           │   ├─ Check isAuthenticated
           │   └─ Redirect if false
           │
           └─→ Logout: logout() action
               ├─ Clear state
               ├─ Clear localStorage
               └─ Redirect to /auth/login
```

### Form Validation Architecture

```
┌──────────────────────────────────────────────┐
│         VeeValidate (Form State)             │
├──────────────────────────────────────────────┤
│ useForm() composable:                        │
│  - validationSchema: Zod schema              │
│  - initialValues: form data                  │
│  - handleSubmit: form submission             │
│  - errors: field-level error messages       │
│  - values: form field values                 │
│  - isSubmitting: loading state              │
└──────────────────────────────────────────────┘
           │
           ├─→ toTypedSchema(zodSchema)
           │   └─ Converts Zod to VeeValidate format
           │
           ├─→ Real-time validation
           │   ├─ Trigger: blur (default)
           │   ├─ Trigger: input (optional)
           │   └─ Debounce: 300ms
           │
           ├─→ Field error display
           │   ├─ UFormField :error prop
           │   ├─ Error text in red below input
           │   └─ All messages in Arabic
           │
           └─→ Form submission
               ├─ Validate all fields
               ├─ If valid: call API action
               ├─ If invalid: display errors
               └─ Loading state on button

┌──────────────────────────────────────────────┐
│         Zod Schema Validation                │
├──────────────────────────────────────────────┤
│ Schemas (frontend/schemas/auth.ts):          │
│  - loginSchema                               │
│  - registerStep1Schema                       │
│  - registerStep2Schema                       │
│  - registerStep3Schema                       │
│  - resetPasswordSchema                       │
│  - profileSchema                             │
│                                              │
│ Features:                                    │
│  - Email validation (RFC 5322)               │
│  - Password strength (length, complexity)    │
│  - Phone regex: /^[+0-9]{7,}$/               │
│  - Password match (refine)                   │
│  - Arabic error messages                     │
└──────────────────────────────────────────────┘
```

---

## Build Strategy

### Phase 1: Foundation (Days 1–2)

**Deliverables:**

- AuthLayout component (RTL-aware wrapper)
- AuthCard component (shadow-as-border styling)
- PasswordStrength component (real-time logic)
- RoleSelector component (URadioGroup wrapper)
- useAuthStore (Pinia store)
- useUserStore (Pinia store)
- useAuthApi composable (API integration)
- auth middleware (route protection)
- Zod schemas (all validation rules)
- i18n setup (Arabic/English translations)

**Success criteria:**

- All components render without errors
- Pinia stores initialize correctly
- API composable makes requests with token attachment
- Middleware redirects unauthenticated users
- Schemas validate correctly (unit tests pass)

### Phase 2: Pages (Days 2–3)

**Deliverables:**

- Login page (/auth/login)
- Register page (/auth/register) — 4-step wizard
- Forgot Password page (/auth/forgot-password)
- Reset Password page (/auth/reset-password?token=<token>)
- Email Verification page (/auth/verify-email?token=<token>)
- Profile page (/profile) — protected

**Success criteria:**

- All pages render without errors
- Forms validate using VeeValidate + Zod
- API calls execute with mocked responses
- Error messages display in Arabic
- RTL layout verified for all pages
- Loading states functional

### Phase 3: Supporting Components (Day 4)

**Deliverables:**

- OtpInput component (for future 2FA)
- Auth layout file (default for /auth/\* routes)
- Error handling standardization
- Responsive design refinement

**Success criteria:**

- All pages responsive on mobile/tablet/desktop
- Minimum touch target: 44px
- Error UI consistent across forms
- OtpInput component ready for integration

### Phase 4: Testing & Polish (Days 4–5)

**Deliverables:**

- Unit tests (Vitest): schemas, stores, composables
- E2E tests (Playwright): login, register, password reset, RTL
- Performance optimization
- Cross-browser testing
- Design system compliance review

**Success criteria:**

- All unit tests pass (>80% coverage)
- All E2E tests pass
- Bundle size <100KB (gzipped)
- Initial load <2s (3G throttling)
- WCAG 2.1 Level AA verified
- All design system requirements met

---

## Component Implementation Order

### Critical Path (Blocking Dependencies)

1. **AuthLayout** (Day 1, 2h)
   - Used by: All auth pages
   - Dependency: None
   - Risk: Low

2. **AuthCard** (Day 1, 1h)
   - Used by: All auth pages
   - Dependency: AuthLayout
   - Risk: Low

3. **Pinia Stores** (Day 2, 3h)
   - Used by: All pages and composables
   - Dependency: None
   - Risk: Medium (state management complexity)

4. **useAuthApi Composable** (Day 2, 2h)
   - Used by: All store actions
   - Dependency: Pinia stores
   - Risk: Medium (API contract adherence)

5. **Zod Schemas** (Day 1, 2h)
   - Used by: All forms
   - Dependency: None
   - Risk: Low

### Parallel Development

- **PasswordStrength** ↔ **RoleSelector** (can develop simultaneously)
- **Login page** ↔ **Forgot Password page** (similar structure)
- **Reset Password page** ↔ **Verify Email page** (similar token logic)

### Sequential Dependencies

- Cannot start: **Profile page** until **Auth middleware** complete
- Cannot start: **E2E tests** until all pages complete
- Cannot test: **Login redirects** until **Dashboard page** exists (mock route)

---

## Pinia Store Implementation

### useAuthStore

**File:** `frontend/stores/auth.ts`

**State:**

```typescript
const user = ref<User | null>(null);
const token = ref<string | null>(localStorage.getItem("auth_token"));
const isLoading = ref(false);
const error = ref<string | null>(null);
```

**Actions:**

```typescript
const login = async (email: string, password: string) => {
  // Validate with loginSchema
  // POST /api/v1/login
  // Store token in localStorage
  // Set user state
  // Handle errors: field-level + form-level
};

const register = async (data: RegisterData) => {
  // Validate with registerStep3Schema
  // POST /api/v1/register
  // Store credentials (for Step 4: verification pending)
  // Emit verification-pending event
  // Handle errors: field-level + form-level
};

const logout = () => {
  // Clear user and token
  // Remove from localStorage
  // Redirect to login
};

const fetchUser = async () => {
  // GET /api/v1/profile (protected route)
  // Set user state
  // Handle 401: token expired → logout + redirect
};

const refreshToken = async () => {
  // POST /api/v1/refresh (if backend supports refresh tokens)
  // Update token in state + localStorage
};
```

**Computed:**

```typescript
const isAuthenticated = computed(() => !!token.value && !!user.value);
const displayName = computed(
  () => user.value?.firstName + " " + user.value?.lastName,
);
const hasRole = (role: string) => user.value?.roles?.includes(role);
```

**Persistence:**

- Token: localStorage (key: "auth_token")
- User: Pinia state only (fetched on app load if token exists)

### useUserStore

**File:** `frontend/stores/user.ts`

**State:**

```typescript
const profile = ref<UserProfile | null>(null);
const isLoading = ref(false);
const error = ref<string | null>(null);
```

**Actions:**

```typescript
const fetchProfile = async () => {
  // GET /api/v1/profile (protected)
  // Set profile state
  // Handle 401: token expired
};

const updateProfile = async (data: Partial<UserProfile>) => {
  // Validate with profileSchema
  // PUT /api/v1/profile (protected)
  // Update profile state
  // Handle errors: field-level + form-level
};
```

**Computed:**

```typescript
const isProfileLoaded = computed(() => !!profile.value);
const preferredLocale = computed(
  () => profile.value?.preferences?.locale || "ar",
);
```

---

## API Integration Pattern

### useAuthApi Composable

**File:** `frontend/composables/useAuthApi.ts`

```typescript
export const useAuthApi = () => {
  // Helper: attach token to all requests
  const withAuth = (options = {}) => {
    const authStore = useAuthStore();
    return {
      ...options,
      headers: {
        ...options.headers,
        Authorization: `Bearer ${authStore.token}`,
      },
    };
  };

  // Helper: handle StandardErrorResponse
  const handleError = (error: any) => {
    if (error.data?.errors) {
      // Field-level errors
      return error.data.errors;
    }
    // Form-level error
    throw new Error(error.data?.message || "Unknown error");
  };

  const login = async (email: string, password: string) => {
    return $fetch("/api/v1/login", {
      method: "POST",
      body: { email, password },
    });
  };

  const register = async (data: RegisterPayload) => {
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

  const getProfile = async () => {
    return $fetch("/api/v1/profile", withAuth());
  };

  const updateProfile = async (data: ProfileUpdatePayload) => {
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
    getProfile,
    updateProfile,
    handleError,
  };
};
```

### Error Handling Pattern

```typescript
// In store actions or page components

try {
  const response = await useAuthApi().login(email, password);
  // Success: store token + user
} catch (error) {
  if (error.data?.errors) {
    // Field-level errors: map to form fields
    formErrors.value = error.data.errors;
  } else {
    // Form-level error: display in UAlert
    alertMessage.value = error.data?.message || "حدث خطأ ما";
  }
}
```

---

## Form Validation Flow

### VeeValidate + Zod Integration

**File:** `frontend/pages/auth/login.vue` (example)

```vue
<script setup lang="ts">
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import { loginSchema } from "~/schemas/auth";
import { useAuthStore } from "~/stores/auth";

const authStore = useAuthStore();
const router = useRouter();

const { handleSubmit, errors, values, isSubmitting } = useForm({
  validationSchema: toTypedSchema(loginSchema),
  initialValues: { email: "", password: "", rememberMe: false },
  validateOnBlur: true,
  validateOnChange: true,
});

const onSubmit = handleSubmit(async (values) => {
  try {
    await authStore.login(values.email, values.password);
    // Store action handles redirect
  } catch (error) {
    // Error displayed via store or caught above
  }
});
</script>

<template>
  <form @submit="onSubmit">
    <UFormField label="البريد الإلكتروني" :error="errors.email">
      <UInput v-model="values.email" type="email" data-testid="email-input" />
    </UFormField>

    <UFormField label="كلمة المرور" :error="errors.password">
      <div class="relative">
        <UInput
          v-model="values.password"
          :type="showPassword ? 'text' : 'password'"
          data-testid="password-input"
        />
        <button
          type="button"
          @click="showPassword = !showPassword"
          class="absolute inset-y-0 end-3"
        >
          <!-- Eye icon toggle -->
        </button>
      </div>
    </UFormField>

    <UCheckbox v-model="values.rememberMe" label="تذكرني" />

    <UButton :loading="isSubmitting" type="submit" block>
      تسجيل الدخول
    </UButton>
  </form>
</template>
```

### Validation Triggers

- **On Blur:** Primary validation (UX-friendly)
- **On Change:** Secondary validation (optional, can debounce)
- **On Submit:** Final validation before API call
- **Debounce:** 300ms for real-time validation

---

## Design System Implementation

### Typography

- **Font Family:** Geist Sans (imports via `@fontsource/geist-sans`)
- **Weights:**
  - Body (400): regular text
  - UI (500): buttons, labels, form fields
  - Headings (600): h1, h2, h3
- **Negative Letter-Spacing:**
  - 48px: -2.4px
  - 32px: -1.28px
  - 24px: -0.64px
  - 20px: -0.32px
- **Line Heights:**
  - Display (tight): 1.00
  - Headings: 1.25
  - Body: 1.50
  - Inputs: 1.50

### Colors

- **Background:** White (#ffffff)
- **Primary Text:** Vercel Black (#171717)
- **Secondary Text:** Gray 600
- **Tertiary Text:** Gray 400
- **Borders:** Gray 100 (via shadow-as-border)
- **Error:** Red (#ff5b4f)
- **Success:** Green (#00aa00)
- **Focus Ring:** Focus Blue (hsla(212, 100%, 48%, 1))

### Shadows & Borders

- **Shadow-as-Border:** `box-shadow: 0px 0px 0px 1px rgba(0,0,0,0.08)`
- **Border Radius:** 6px consistent
- **Never use:** CSS `border` property (only shadow-based)

### Spacing Grid

- Base unit: 4px
- Commonly used: 4, 8, 12, 16, 20, 24, 32px

### Layout

- **Mobile (<768px):** Full-width, 16px horizontal padding
- **Tablet (768–1024px):** Max-width 600px, centered
- **Desktop (>1024px):** Max-width 400px (auth forms), centered

### RTL Implementation

**Tailwind Logical Properties:**

- `ms-*` → margin-inline-start (replaces `ml-*` in RTL)
- `me-*` → margin-inline-end (replaces `mr-*` in RTL)
- `ps-*` → padding-inline-start (replaces `pl-*` in RTL)
- `pe-*` → padding-inline-end (replaces `pr-*` in RTL)
- `text-start` → text-left in LTR, text-right in RTL
- `text-end` → text-right in LTR, text-left in RTL
- `flex-row-reverse` → only when needed for RTL flex layouts

**Nuxt i18n:**

- Auto-applies `dir="rtl"` to `<html>` when locale is Arabic
- Nuxt UI components auto-detect and apply RTL styles

---

## Testing Architecture

### Unit Tests (Vitest)

**File Structure:**

```
frontend/tests/unit/
├── schemas/
│   └── auth.spec.ts           # Zod schema validation
├── stores/
│   ├── auth.spec.ts           # useAuthStore actions
│   └── user.spec.ts           # useUserStore actions
└── composables/
    └── useAuthApi.spec.ts     # API error handling
```

**Coverage Target:** >80%

**Key Test Scenarios:**

- **Schemas:**
  - Valid inputs pass validation
  - Invalid emails rejected
  - Passwords too short rejected
  - Password mismatches rejected
  - All error messages in Arabic

- **Stores:**
  - Login: token stored, user set, state updated
  - Logout: state cleared, localStorage cleared
  - Register: credentials stored, verification-pending state
  - fetchUser: profile data set, 401 handled
  - Error handling: error message stored, displayed

- **Composables:**
  - API calls made with correct URLs/methods
  - Token attached to protected requests
  - StandardErrorResponse parsed correctly
  - 401 responses trigger logout

### E2E Tests (Playwright)

**File Structure:**

```
frontend/tests/e2e/
├── auth.spec.ts               # Login, Register, Password Reset
├── profile.spec.ts            # Profile management
├── rtl.spec.ts                # RTL layout verification
└── accessibility.spec.ts      # WCAG 2.1 AA compliance
```

**Key Test Scenarios:**

- **Login:**
  - Valid credentials → redirect to dashboard
  - Invalid credentials → error UAlert in Arabic
  - Remember me checkbox works
  - Password show/hide toggle works
  - Links to forgot password and register work

- **Registration:**
  - All 4 steps complete successfully
  - Each step validates before advancing
  - Back button navigates to previous step
  - Form data persists across steps
  - Step 4: verification pending shown

- **Password Reset:**
  - Forgot password email submission
  - Email link navigation to reset page
  - Token validation on page load
  - New password submission
  - Redirect to login on success

- **RTL:**
  - HTML dir="rtl" set when Arabic
  - Inputs right-aligned (logical properties)
  - Error messages in Arabic
  - Form layout correct in RTL

- **Accessibility:**
  - Keyboard navigation (Tab through all fields)
  - Focus ring visible
  - Error messages announced to screen readers
  - Form labels properly associated with inputs
  - Color contrast >= 4.5:1

---

## Performance Optimization

### Bundle Size Optimization

- **Tree-shake Nuxt UI:** Only import used components
- **Minify output:** Enable production minification
- **Code splitting:** Auth pages lazy-loaded by default
- **CSS purge:** Remove unused Tailwind classes

**Target:** <100KB (gzipped)

### Load Time Optimization

- **Initial page load:** <2s (3G throttling)
- **Time to Interactive:** <3s
- **Validate with:** Lighthouse + WebPageTest

### Runtime Performance

- **Validation debounce:** 300ms for real-time form validation
- **Avoid N+1 renders:** Use Composition API effectively
- **Pinia selectors:** Only subscribe to needed state
- **Lazy-load components:** Register multipart forms with dynamic imports

---

## RTL/i18n Strategy

### i18n Configuration

**File:** `frontend/nuxt.config.ts`

```typescript
export default defineNuxtConfig({
  modules: ["@nuxtjs/i18n"],
  i18n: {
    locales: [
      { code: "ar", iso: "ar-SA", name: "عربي", file: "ar.json" },
      { code: "en", iso: "en-US", name: "English", file: "en.json" },
    ],
    defaultLocale: "ar",
    strategy: "prefix_except_default",
    vueI18n: "./i18n.config.ts",
  },
});
```

### Locale Keys

**File:** `frontend/locales/ar.json`

```json
{
  "auth": {
    "login": {
      "title": "تسجيل الدخول",
      "email": "البريد الإلكتروني",
      "password": "كلمة المرور",
      "submit": "تسجيل الدخول",
      "errors": {
        "invalidEmail": "البريد الإلكتروني غير صالح",
        "shortPassword": "كلمة المرور قصيرة جدًا"
      }
    }
  }
}
```

### RTL Directionality

- Nuxt i18n auto-applies `dir="rtl"` on `<html>` when locale is Arabic
- All text renders via `$t()` function (composable or global)
- Form inputs auto-align right in RTL mode (Nuxt UI)

---

## Risks & Mitigations

| Risk                         | Level  | Mitigation                                                  |
| ---------------------------- | ------ | ----------------------------------------------------------- |
| **Backend API delays**       | HIGH   | Start with mocked responses; swap real endpoints when ready |
| **RTL layout issues**        | MEDIUM | Use Tailwind logical properties; test both LTR + RTL early  |
| **Multi-step form UX**       | MEDIUM | Build USteppers wrapper early; user test with team          |
| **Token expiry edge cases**  | MEDIUM | Implement refresh token logic; handle 401 gracefully        |
| **Form validation UX**       | MEDIUM | Debounce validation; blur-first strategy                    |
| **Accessibility compliance** | MEDIUM | Automated tests + manual screen reader testing              |
| **Bundle bloat**             | LOW    | Monitor size at each phase; tree-shake CSS                  |
| **Browser compatibility**    | LOW    | Target modern browsers; PostCSS fallback if needed          |

---

## Success Criteria

### Specification & Planning

- ✅ All requirements clearly documented (no ambiguities)
- ✅ Architecture defined (pages, components, stores, middleware)
- ✅ API contract specified (endpoints, error codes)
- ✅ Test strategy documented (unit + E2E)
- ✅ Performance targets defined (<100KB, <2s)

### Implementation

- ✅ All 6 pages implemented and functional
- ✅ All 5 shared components created
- ✅ Pinia stores working with token persistence
- ✅ VeeValidate + Zod validation functional
- ✅ Form error messages in Arabic
- ✅ Auth middleware protecting protected routes
- ✅ API composable integrating with backend

### Testing

- ✅ Unit tests pass (>80% coverage, Vitest)
- ✅ E2E tests pass (all user flows, Playwright)
- ✅ RTL tests pass (layout, directionality)
- ✅ Accessibility tests pass (WCAG 2.1 AA)

### Quality

- ✅ Bundle size < 100KB (gzipped)
- ✅ Initial load < 2s (3G throttling)
- ✅ Design system compliance verified
- ✅ Cross-browser testing passed
- ✅ RTL layout fully functional
- ✅ No console errors/warnings

---

## Next Steps

1. **Review & approval** of this plan
2. **Create detailed task list** (TASKS_REPORT.md)
3. **Execute Phase 1** (Foundation: components, stores, schemas)
4. **Parallel development** (Phase 2 pages once Phase 1 complete)
5. **Continuous testing** (unit tests as code written)
6. **Performance monitoring** (check bundle/load time regularly)
7. **User feedback** (share progress with stakeholders)
8. **Final verification** (cross-browser, RTL, accessibility)
