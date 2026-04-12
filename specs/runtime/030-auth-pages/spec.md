# STAGE_30 — Auth Pages Specification

## Overview

Auth Pages implements all authentication-related frontend pages for the Bunyan platform. This stage delivers a complete user authentication experience with multi-step registration, password recovery, email verification, and profile management. All pages follow the Vercel-inspired design system (Geist fonts, shadow-as-border, achromatic palette) with **full Arabic RTL support** and form validation powered by VeeValidate + Zod.

**Key Deliverables:**

- 6 frontend pages (Login, Register, Forgot Password, Reset Password, Email Verification, Profile)
- Shared auth components (AuthLayout, AuthCard, PasswordStrength, RoleSelector, OtpInput)
- Pinia store (`useAuthStore`, `useUserStore`) with token management
- VeeValidate + Zod form validation with Arabic error messages
- Unit tests (Vitest) for schemas and store actions
- E2E tests (Playwright) for user flows and RTL layout verification

---

## Scope

### In Scope

#### Frontend Pages

| Page                   | Route                                | Purpose                                                                                        |
| ---------------------- | ------------------------------------ | ---------------------------------------------------------------------------------------------- |
| **Login**              | `/auth/login`                        | Email + password authentication with "Remember me" option                                      |
| **Register**           | `/auth/register`                     | Multi-step registration (4 steps: account type → personal info → contact → email verification) |
| **Forgot Password**    | `/auth/forgot-password`              | Email input to initiate password reset flow                                                    |
| **Reset Password**     | `/auth/reset-password?token=<token>` | New password form with strength indicator, accessed via email link                             |
| **Email Verification** | `/auth/verify-email`                 | Confirmation page after email verification link click                                          |
| **Profile**            | `/profile`                           | User profile management (name, email, phone, country) — protected route                        |

#### Shared Components

- **AuthLayout** — RTL-aware layout wrapper for all auth pages
- **AuthCard** — Reusable card container with shadow-as-border styling
- **PasswordStrength** — Visual indicator (UProgress) showing password strength
- **RoleSelector** — Account type selector (URadioGroup: Customer/Contractor)
- **OtpInput** — PIN/OTP input wrapper (UPinInput) for future 2FA

#### Form Validation

- Zod schemas in `frontend/schemas/auth.ts` with Arabic error messages
- VeeValidate real-time validation on input/blur
- Field-level error display with RTL text direction
- Password confirmation matching
- Email format validation

#### State Management

- **useAuthStore** — Login, logout, register actions, token storage, authentication state
- **useUserStore** — User profile data, roles, preferences
- Token persistence to localStorage
- Auth middleware for protected routes

#### Design System Compliance

- **Typography:** Geist Sans (400 body, 500 UI, 600 headings) with negative letter-spacing
- **Colors:** Achromatic palette (grays, blacks, whites) — no custom colors outside system
- **Shadows:** Shadow-as-border technique: `box-shadow: 0px 0px 0px 1px rgba(0,0,0,0.08)`
- **RTL Layout:** Tailwind logical properties (`ms-`, `me-`, `ps-`, `pe-`, `text-start`, `text-end`)
- **Responsiveness:** Mobile (<768px), Tablet (768-1024px), Desktop (>1024px)

#### Testing

- **Unit Tests (Vitest):**
  - Zod schemas: email validation, password strength, password match
  - Pinia store: login/logout/register actions, token storage, state cleanup
  - API error handling
- **E2E Tests (Playwright):**
  - Login with valid/invalid credentials
  - Multi-step registration completion
  - Password reset flow (email → token → new password)
  - RTL form layout verification
  - Profile update persistence

### Out of Scope

- **Backend API Implementation** — Delegated to STAGE_03_AUTHENTICATION (endpoints already defined)
- **Social Login Backend** — Social login buttons present in UI (design only), backend integration deferred
- **2FA/MFA Advanced Flows** — Optional enhancement, not in MVP scope
- **Custom Password Rules** — Standard 8-character minimum rule only
- **Admin User Management** — Not part of auth pages (handled in admin dashboard)

---

## Requirements

### Functional Requirements

#### FR1: User Login

**Description:** Users authenticate with email and password, with optional "Remember me" functionality.

**User Story:**  
As a construction customer/contractor  
I want to log in with email and password  
So that I can access my project dashboard

**Acceptance Criteria:**

- [ ] Login page accessible at `/auth/login`
- [ ] Email input accepts valid email format with Zod validation
- [ ] Password input accepts 8+ character passwords
- [ ] "Remember me" checkbox is optional
- [ ] Valid credentials trigger API call to `/api/v1/login`
- [ ] On success: token stored in localStorage, redirect to `/dashboard`
- [ ] On error: UAlert displays error message in Arabic below form
- [ ] Social login buttons present (design placeholder, no backend logic)
- [ ] Password show/hide toggle functional
- [ ] Form validates on blur, provides immediate feedback
- [ ] RTL layout verified (inputs aligned right in Arabic mode)
- [ ] Loading state on submit button during API call

---

#### FR2: User Registration (Multi-Step)

**Description:** New users complete a 4-step registration process with account type selection, personal info, contact details, and email verification.

**User Story:**  
As a new platform user  
I want to register through a guided multi-step form  
So that I can create my account with proper type and contact information

**Acceptance Criteria:**

**Step 1: Account Type Selection**

- [ ] URadioGroup shows two options: "Customer (العميل)" and "Contractor (المقاول)"
- [ ] Selection required before advancing
- [ ] Visual feedback for selected option

**Step 2: Personal Information**

- [ ] First Name input (min 2 characters, Arabic support)
- [ ] Last Name input (min 2 characters, Arabic support)
- [ ] Email input (email format validation)
- [ ] Zod schema validates all fields
- [ ] Error messages in Arabic displayed below each field

**Step 3: Contact Information**

- [ ] Phone number input (regex: `/^[+0-9]{7,}/`)
- [ ] Country selector (dropdown with common countries)
- [ ] Password input (min 8 characters)
- [ ] Confirm Password input with match validation
- [ ] PasswordStrength component shows real-time password strength
- [ ] Zod schema validates all fields with Arabic error messages

**Step 4: Email Verification Pending**

- [ ] Display message: "تحقق من بريدك الإلكتروني" (Check your email)
- [ ] Show email address used
- [ ] "Resend" button (for re-sending verification email)
- [ ] Countdown timer optional (standard: 60 seconds before resend enabled)
- [ ] Auto-redirect to `/dashboard` after email verification (webhook from backend)

**General Multi-Step Requirements:**

- [ ] USteppers component displays current step and progress
- [ ] Each step validates before advancing to next
- [ ] Back button allows navigation to previous steps
- [ ] Form data persisted (step 1-3 survive page refresh via store)
- [ ] All text in Arabic when in Arabic locale
- [ ] RTL layout verified for all step forms

---

#### FR3: Password Recovery (Forgot Password + Reset)

**Description:** Users can request a password reset via email and then set a new password using the reset link.

**User Story:**  
As a user who forgot my password  
I want to reset it via email link  
So that I can regain access to my account

**Acceptance Criteria:**

**Forgot Password Page (/auth/forgot-password):**

- [ ] Email input with validation
- [ ] Submit button sends POST request to `/api/v1/forgot-password`
- [ ] On success: UAlert displays "Check your email for reset link" (Arabic)
- [ ] On error: UAlert displays error message in Arabic
- [ ] Loading state on submit button
- [ ] RTL layout verified

**Reset Password Page (/auth/reset-password?token=<token>):**

- [ ] Extracts reset token from URL query parameter
- [ ] Validates token on page load (POST to `/api/v1/validate-reset-token`)
- [ ] If token invalid/expired: Display error page with link back to `/auth/forgot-password`
- [ ] If token valid: Display form with:
  - [ ] New Password input (min 8 characters)
  - [ ] Confirm Password input with match validation
  - [ ] PasswordStrength component showing strength real-time
  - [ ] Submit button sends POST to `/api/v1/reset-password` with token + new password
- [ ] On success: Redirect to `/auth/login` with success message
- [ ] On error: Display error in Arabic
- [ ] Zod schema validates password requirements
- [ ] RTL layout verified

---

#### FR4: Email Verification

**Description:** User receives email with verification link; clicking link confirms email ownership.

**User Story:**  
As a new user during registration  
I want to verify my email  
So that the platform confirms my account is legitimate

**Acceptance Criteria:**

- [ ] Email verification page accessible at `/auth/verify-email?token=<token>`
- [ ] Extracts verification token from URL
- [ ] On page load: Sends verification request to `/api/v1/verify-email` with token
- [ ] On success: Display "Email verified successfully" message in Arabic
- [ ] On success: Auto-redirect to `/dashboard` after 3 seconds or on button click
- [ ] On error (expired/invalid token): Display error message with "Resend email" link
- [ ] "Resend email" link triggers re-send via `/api/v1/resend-verification-email`
- [ ] Page styled with AuthCard and UAlert components

---

#### FR5: Profile Management

**Description:** Authenticated users can view and edit their profile information.

**User Story:**  
As an authenticated user  
I want to edit my profile  
So that I can keep my information current

**Acceptance Criteria:**

- [ ] Profile page accessible at `/profile` (protected by `middleware/auth.ts`)
- [ ] Unauthenticated users redirected to `/auth/login`
- [ ] Form fields pre-populated from user store:
  - [ ] First Name
  - [ ] Last Name
  - [ ] Email (read-only or separate change-email flow)
  - [ ] Phone Number
  - [ ] Country
- [ ] Form validation via Zod schema
- [ ] Save button submits PUT request to `/api/v1/profile`
- [ ] On success: UAlert displays success message in Arabic
- [ ] On success: User store updated with new data
- [ ] On error: UAlert displays error message with field-level errors
- [ ] Loading state on save button
- [ ] Cancel button or "Reset" button to revert changes
- [ ] RTL layout verified
- [ ] All form fields support Arabic input

---

### Non-Functional Requirements

#### NFR1: Accessibility (WCAG 2.1 Level AA)

- Form labels have proper `for` attributes linked to inputs
- Error messages announced to screen readers with `role="alert"`
- Keyboard navigation fully functional (Tab through form elements)
- Color contrast ratio >= 4.5:1 for all text
- Focus ring visible on all interactive elements (2px solid, Focus Blue)
- Password inputs properly labeled (not just placeholder text)

#### NFR2: Performance

- Auth pages bundled < 100KB (gzipped) total size
- Initial page load < 2 seconds (3G throttling)
- Form interactive within 1 second
- API responses cached when appropriate (e.g., country list)
- Form validation debounced to prevent excessive re-renders
- No console errors or warnings in production build

#### NFR3: RTL / Internationalization

- All UI text strings use i18n keys (supported locales: Arabic, English)
- Error messages rendered in Arabic with proper text direction
- Form placeholder text translated to Arabic
- Form field labels in Arabic in RTL mode
- Tailwind logical properties used everywhere (no `ml-`, `mr-`, `text-left`, `text-right`)
- `dir="rtl"` applied to `<html>` when locale is Arabic
- Nuxt i18n plugin auto-handles directionality switching

#### NFR4: Security

- **Client-Side Validation:** Zod schemas prevent invalid data submission
- **Server-Side Validation:** All validation enforced by backend API (not duplicated in frontend validation alone)
- **Token Storage:** Auth token stored in localStorage (httpOnly cookie recommended but deferred to backend)
- **Password Input:** Password never displayed in console logs or stored unencrypted
- **CSRF Protection:** CSRF token sent with POST requests (Laravel Sanctum handles this)
- **Input Sanitization:** Vue escapes all user input by default (no XSS risk from user content)
- **Password Reset Links:** Tokens expire server-side (enforced by backend)
- **No Hardcoded Secrets:** API URLs from environment variables only

#### NFR5: Mobile Responsiveness

- **Mobile (<768px):** Single-column layout, full-width form fields, touch-friendly button sizes (48px minimum)
- **Tablet (768-1024px):** Centered container, readable text sizes
- **Desktop (>1024px):** Max-width container (e.g., 480px for auth forms), centered on page
- All form elements touch-friendly (minimum 44px height)

#### NFR6: Browser Support

- Chrome/Chromium (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions on macOS and iOS)
- Edge (latest 2 versions)
- Mobile browsers: iOS Safari, Chrome Android

---

## Architecture

### Component Hierarchy

```
Frontend Layer (Nuxt.js)
├── Pages (routes)
│   ├── auth/
│   │   ├── login.vue                    # Email + password login
│   │   ├── register.vue                 # Multi-step registration wizard
│   │   ├── forgot-password.vue          # Email input for password reset
│   │   ├── reset-password.vue           # New password form (token-gated)
│   │   └── verify-email.vue             # Email verification confirmation
│   └── profile/
│       └── index.vue                    # Profile edit (protected)
│
├── Components (auth/)
│   ├── AuthLayout.vue                   # RTL layout wrapper (shared)
│   ├── AuthCard.vue                     # Card container with shadow-as-border
│   ├── PasswordStrength.vue             # UProgress-based strength indicator
│   ├── RoleSelector.vue                 # URadioGroup wrapper (Customer/Contractor)
│   └── OtpInput.vue                     # UPinInput wrapper (for future 2FA)
│
├── Composables
│   ├── useAuthApi.ts                    # API calls: login, register, verify, reset, profile
│   └── usePasswordStrength.ts           # Logic for password strength calculation
│
├── Stores (Pinia)
│   ├── auth.ts                          # useAuthStore: token, user, login/logout/register actions
│   └── user.ts                          # useUserStore: profile data, roles
│
├── Middleware
│   └── auth.ts                          # Redirect unauthenticated users to /auth/login
│
├── Schemas (Zod)
│   └── auth.ts                          # Validation schemas: login, register, reset, profile
│
├── Layouts
│   └── auth.vue                         # Default layout for /auth/* routes
│
└── i18n (Locales)
    ├── ar.json                          # Arabic translations
    └── en.json                          # English translations
```

### State Management Architecture (Pinia)

#### `useAuthStore`

```typescript
// frontend/stores/auth.ts
export const useAuthStore = defineStore("auth", () => {
  // State
  const user = ref(null); // { id, email, firstName, lastName, role, ... }
  const token = ref(localStorage.getItem("auth_token"));
  const isAuthenticated = computed(() => !!token.value);
  const isLoading = ref(false);
  const error = ref(null);

  // Actions
  const login = async (email, password) => {
    // POST /api/v1/login → { token, user }
    // Store token in localStorage, set user state
  };

  const logout = () => {
    // Clear token, user, localStorage
  };

  const register = async (data) => {
    // POST /api/v1/register → { token, user, verification_pending }
    // Handle multi-step form submission
  };

  const refreshToken = async () => {
    // POST /api/v1/refresh → new token (if using refresh tokens)
  };

  return {
    user,
    token,
    isAuthenticated,
    isLoading,
    error,
    login,
    logout,
    register,
    refreshToken,
  };
});
```

#### `useUserStore`

```typescript
// frontend/stores/user.ts
export const useUserStore = defineStore("user", () => {
  // State
  const profile = ref(null); // { firstName, lastName, email, phone, country, roles }
  const isLoading = ref(false);
  const error = ref(null);

  // Actions
  const fetchProfile = async () => {
    // GET /api/v1/profile → profile data
  };

  const updateProfile = async (data) => {
    // PUT /api/v1/profile → updated profile
  };

  return {
    profile,
    isLoading,
    error,
    fetchProfile,
    updateProfile,
  };
});
```

### Form Validation Architecture (VeeValidate + Zod)

#### Zod Schemas

```typescript
// frontend/schemas/auth.ts
import { z } from "zod";

export const loginSchema = z.object({
  email: z
    .string()
    .min(1, { message: "البريد الإلكتروني مطلوب" })
    .email({ message: "البريد الإلكتروني غير صالح" }),
  password: z
    .string()
    .min(8, { message: "كلمة المرور يجب أن تكون 8 أحرف على الأقل" }),
  rememberMe: z.boolean().optional(),
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
```

#### VeeValidate Integration Example

```vue
<!-- Example: Login Form -->
<script setup lang="ts">
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import { loginSchema } from "~/schemas/auth";
import { useAuthStore } from "~/stores/auth";

const authStore = useAuthStore();

const { handleSubmit, errors, values, isSubmitting } = useForm({
  validationSchema: toTypedSchema(loginSchema),
  initialValues: {
    email: "",
    password: "",
    rememberMe: false,
  },
});

const onSubmit = handleSubmit(async (values) => {
  try {
    await authStore.login(values.email, values.password);
    // Redirect to dashboard handled by store or middleware
  } catch (err) {
    // Error displayed via store error state
  }
});
</script>

<template>
  <form @submit="onSubmit">
    <!-- Email Field -->
    <UFormField label="البريد الإلكتروني" :error="errors.email">
      <UInput
        v-model="values.email"
        type="email"
        placeholder="user@example.com"
        data-testid="email-input"
      />
    </UFormField>

    <!-- Password Field with Show/Hide -->
    <UFormField label="كلمة المرور" :error="errors.password">
      <div class="relative">
        <UInput
          v-model="values.password"
          :type="showPassword ? 'text' : 'password'"
          placeholder="••••••••"
          data-testid="password-input"
        />
        <button
          type="button"
          @click="showPassword = !showPassword"
          class="absolute inset-y-0 end-3"
        >
          <!-- Eye/Eye-off icon -->
        </button>
      </div>
    </UFormField>

    <!-- Submit -->
    <UButton :loading="isSubmitting" type="submit" block>
      تسجيل الدخول
    </UButton>
  </form>
</template>
```

### API Integration Architecture

#### Auth Composable

```typescript
// frontend/composables/useAuthApi.ts
import { StandardErrorResponse } from "~/types/api";

export const useAuthApi = () => {
  const login = async (email: string, password: string) => {
    // POST /api/v1/login
    return $fetch("/api/v1/login", {
      method: "POST",
      body: { email, password },
    });
  };

  const register = async (data: RegisterPayload) => {
    // POST /api/v1/register
    return $fetch("/api/v1/register", {
      method: "POST",
      body: data,
    });
  };

  const forgotPassword = async (email: string) => {
    // POST /api/v1/forgot-password
    return $fetch("/api/v1/forgot-password", {
      method: "POST",
      body: { email },
    });
  };

  const resetPassword = async (token: string, password: string) => {
    // POST /api/v1/reset-password
    return $fetch("/api/v1/reset-password", {
      method: "POST",
      body: { token, password },
    });
  };

  const verifyEmail = async (token: string) => {
    // POST /api/v1/verify-email
    return $fetch("/api/v1/verify-email", {
      method: "POST",
      body: { token },
    });
  };

  const getProfile = async () => {
    // GET /api/v1/profile
    return $fetch("/api/v1/profile");
  };

  const updateProfile = async (data: ProfileUpdatePayload) => {
    // PUT /api/v1/profile
    return $fetch("/api/v1/profile", {
      method: "PUT",
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
  };
};
```

#### Auth Middleware

```typescript
// frontend/middleware/auth.ts
export default defineRouteMiddleware((to, from) => {
  const authStore = useAuthStore();

  if (!authStore.isAuthenticated) {
    return navigateTo("/auth/login");
  }
});
```

### Nuxt UI Component Mapping

| Form Element              | Nuxt UI Component    | Purpose                                                    |
| ------------------------- | -------------------- | ---------------------------------------------------------- |
| Login/Register container  | `UCard`              | Wrapper with shadow-as-border styling                      |
| Form field wrapper        | `UFormField`         | Label + input with error display                           |
| Text/Email/Password input | `UInput`             | Core input with type variants                              |
| Submit/Action button      | `UButton`            | Block-level button with loading state                      |
| Multi-step wizard         | `USteppers`          | Progress indicator for registration steps                  |
| Account type selector     | `URadioGroup`        | Radio option group (Customer/Contractor)                   |
| OTP/PIN input             | `UPinInput`          | PIN code input (for future 2FA)                            |
| Password strength         | `UProgress`          | Horizontal progress bar showing strength                   |
| Alert/Error message       | `UAlert`             | Dismissible message container (color="error" or "success") |
| Remember me toggle        | `UCheckbox`          | Checkbox input                                             |
| Divider                   | `UDivider`           | Horizontal line between sections                           |
| Loader/Spinner            | `UIcon` with spinner | Loading indicator during API calls                         |

---

## Pages Specification

### 1. Login Page (`/auth/login`)

**Purpose:** User authentication with email and password.

**Layout:**

- AuthLayout wrapper (RTL-aware)
- AuthCard container
- Centered on page (max-width: 400px on desktop)

**Form Fields:**

- Email (UInput, type="email")
- Password (UInput, type="password" with show/hide toggle)
- "Remember me" (UCheckbox, optional)

**Buttons:**

- Submit: "تسجيل الدخول" (Login)
- Social login buttons: "Google", "Apple" (design placeholders, no backend yet)
- Forgot password link
- Register link

**Validation:**

- Zod schema: loginSchema
- Real-time validation on blur
- Error messages displayed below fields in Arabic

**Behavior:**

- Valid submission → POST `/api/v1/login` → store token + user → redirect to `/dashboard`
- Invalid submission → display error UAlert in Arabic
- Loading state on submit button

---

### 2. Register Page (`/auth/register`)

**Purpose:** Multi-step new user registration.

**Layout:**

- AuthLayout wrapper
- AuthCard container
- USteppers showing 4 steps

**Step 1: Account Type**

- Radio options: Customer, Contractor
- Description text for each role
- Next button

**Step 2: Personal Information**

- First Name (UInput)
- Last Name (UInput)
- Email (UInput, type="email")
- Back/Next buttons

**Step 3: Contact Information**

- Phone (UInput, type="tel")
- Country (USelect/dropdown)
- Password (UInput, type="password" with show/hide)
- Confirm Password (UInput, type="password")
- PasswordStrength component below password
- Back/Submit buttons

**Step 4: Verification Pending**

- Message: "تحقق من بريدك الإلكتروني" (Check your email)
- Email address displayed
- Resend button (60s cooldown)
- Loading state while waiting for verification webhook

**Validation:**

- Each step validates before advancing
- Zod schemas: registerStep1Schema, registerStep2Schema, registerStep3Schema
- All error messages in Arabic

**Behavior:**

- Step 1-3: Validate on "Next" click, show errors below fields
- Step 3: Submit → POST `/api/v1/register` → store credentials (don't auto-login)
- Step 4: Wait for email verification (backend webhook updates frontend)
- On verification: Auto-redirect to `/dashboard` or show success

---

### 3. Forgot Password Page (`/auth/forgot-password`)

**Purpose:** Initiate password reset flow via email.

**Layout:**

- AuthLayout wrapper
- AuthCard container
- Centered form

**Form Fields:**

- Email (UInput, type="email")

**Buttons:**

- Submit: "إرسال رابط إعادة تعيين" (Send Reset Link)
- Back to login link

**Validation:**

- Zod schema: email required + valid format
- Error message in Arabic below field

**Behavior:**

- Valid submission → POST `/api/v1/forgot-password` → display success message
- Success message: "تحقق من بريدك الإلكتروني للحصول على رابط إعادة التعيين"
- Loading state on submit button
- Invalid submission → display error UAlert in Arabic

---

### 4. Reset Password Page (`/auth/reset-password?token=<token>`)

**Purpose:** Set new password using email reset link.

**Layout:**

- AuthLayout wrapper
- AuthCard container
- Centered form

**Form Fields:**

- New Password (UInput, type="password" with show/hide)
- Confirm Password (UInput, type="password" with show/hide)
- PasswordStrength component below new password

**Buttons:**

- Submit: "تحديث كلمة المرور" (Reset Password)
- Back to login link

**Validation:**

- Extract token from URL query parameter (?token=abc123)
- Validate token on page load → POST `/api/v1/validate-reset-token`
- If invalid/expired: Show error page with link back to `/auth/forgot-password`
- If valid: Show form
- Zod schema: resetPasswordSchema (password match validation)
- Error messages in Arabic

**Behavior:**

- Form submission → POST `/api/v1/reset-password` with token + password
- On success: Redirect to `/auth/login` with success message
- On error: Display error UAlert in Arabic

---

### 5. Email Verification Page (`/auth/verify-email?token=<token>`)

**Purpose:** Confirm email ownership during registration.

**Layout:**

- AuthLayout wrapper
- AuthCard container

**Content:**

- Loading spinner while verifying token
- On success: "تم التحقق من بريدك الإلكتروني بنجاح" (Email verified successfully)
- Countdown timer (3s) before auto-redirect to `/dashboard`
- Manual button: "متابعة إلى لوحة التحكم" (Continue to Dashboard)

**Error State:**

- On invalid/expired token: Show error message
- "Resend email" button → POST `/api/v1/resend-verification-email`
- Link back to `/auth/login`

**Behavior:**

- Page load: Extract token from URL, POST `/api/v1/verify-email`
- Success: Auto-redirect after 3s or on button click
- Error: Display error with resend option

---

### 6. Profile Page (`/profile`)

**Purpose:** Authenticated user profile management.

**Layout:**

- Standard app layout (not auth layout)
- Sidebar or top navigation
- Main content area with profile form

**Form Fields:**

- First Name (UInput)
- Last Name (UInput)
- Email (UInput, read-only or separate change-email flow)
- Phone (UInput, type="tel")
- Country (USelect/dropdown)

**Buttons:**

- Save: "حفظ التغييرات" (Save Changes)
- Cancel: "إلغاء" (Cancel)

**Validation:**

- Zod schema: profileSchema
- Real-time validation on blur
- Error messages in Arabic below fields

**Behavior:**

- Page load: Fetch profile from `/api/v1/profile` → populate form
- Submit: PUT `/api/v1/profile` → update user store + display success message
- Cancel: Reset form to original state
- Loading state on save button
- Error handling: Display UAlert with error details in Arabic
- Protected by `middleware/auth.ts` (unauthenticated redirect to login)

---

## Component Specifications

### AuthLayout.vue

**Purpose:** Shared RTL-aware layout for all auth pages.

**Props:**

- `class` (optional) — Additional CSS classes

**Content:** Slot for page content

**Features:**

- Full viewport height background (white)
- Responsive padding (mobile: 16px, desktop: 0)
- Center content horizontally/vertically
- RTL support: flex direction auto-reverses based on locale

**Styling:**

- Background: white
- Min-height: 100vh
- Flexbox: center alignment
- Shadow-as-border for subtle structure

---

### AuthCard.vue

**Purpose:** Reusable card container with design system compliance.

**Props:**

- `title` (optional) — Card heading
- `subtitle` (optional) — Card description
- `class` (optional) — Additional CSS classes

**Content:** Slot for form/content

**Features:**

- Max-width: 400px on desktop
- Shadow-as-border: `box-shadow: 0px 0px 0px 1px rgba(0,0,0,0.08)`
- Padding: 24px (desktop), 16px (mobile)
- Rounded: 6px
- RTL-aware text alignment

**Styling:**

- Background: white
- Border: via shadow (no CSS border)

---

### PasswordStrength.vue

**Purpose:** Real-time password strength indicator.

**Props:**

- `password` (string) — Current password value
- `showLabel` (boolean, default: true) — Show strength label

**Content:** None (pure component)

**Logic:**

- Analyzes password: length, uppercase, lowercase, numbers, special chars
- Returns strength: "Weak", "Fair", "Good", "Strong"
- Strength score: 0-4

**Features:**

- UProgress bar shows strength visually (0-100%)
- Color changes with strength: red (weak) → yellow → green (strong)
- Label text: "ضعيف" (Weak) → "قوي" (Strong)

**Styling:**

- UProgress component with color prop
- Responsive text size

---

### RoleSelector.vue

**Purpose:** Account type selection wrapper.

**Props:**

- `modelValue` (string) — Selected role ("customer" or "contractor")
- `disabled` (boolean, default: false)

**Events:**

- `update:modelValue` — Emitted when selection changes

**Content:** None (pure component)

**Features:**

- URadioGroup with two options
- Labels in Arabic: "عميل" (Customer), "مقاول" (Contractor)
- Descriptions for each role (optional)

---

### OtpInput.vue

**Purpose:** PIN/OTP input wrapper (for future 2FA).

**Props:**

- `modelValue` (string) — Current OTP value
- `length` (number, default: 6) — OTP digit count
- `disabled` (boolean, default: false)

**Events:**

- `update:modelValue` — Emitted when digits change
- `complete` — Emitted when all digits entered

**Features:**

- UPinInput component from Nuxt UI
- Auto-focus between fields
- Numeric input only
- Submit on completion (optional)

---

## User Stories & Acceptance Criteria

### US1: User Login (Construct User Story Format)

**As a** construction customer or contractor  
**I want to** log in to the platform with my email and password  
**So that** I can access my project dashboard and manage my work

**Acceptance Criteria:**

1. User navigates to `/auth/login`
2. Form displays Email field with email input type and Arabic placeholder
3. Form displays Password field with password input type and Arabic placeholder
4. Form displays "Remember me" checkbox (optional)
5. Form displays "Forgot password?" link
6. Form displays "Register" link
7. User enters valid email (e.g., customer@example.com)
8. User enters valid password (8+ characters)
9. User clicks "تسجيل الدخول" button
10. System displays loading spinner on button
11. System sends POST request to `/api/v1/login`
12. On success: System stores token in localStorage
13. On success: System sets user state in useAuthStore
14. On success: System redirects to `/dashboard`
15. On failure (invalid credentials): System displays UAlert with Arabic error message
16. On failure: System keeps user on login page with error visible
17. Verify all form text is in Arabic when locale is Arabic
18. Verify form layout is RTL (inputs right-aligned)

---

### US2: Multi-Step Registration (First Step)

**As a** new user  
**I want to** register for an account through a guided multi-step process  
**So that** I can specify my role and account type

**Acceptance Criteria:**

1. User navigates to `/auth/register`
2. System displays USteppers component showing "Step 1 of 4"
3. Step 1 displays "Select Account Type" heading in Arabic
4. Step 1 displays two radio options: "عميل" (Customer), "مقاول" (Contractor)
5. Each radio option includes a description of the role
6. User selects "عميل"
7. User clicks "Next" button
8. System validates selection (required field)
9. System stores selection in form state
10. System displays Step 2 ("Personal Information")
11. USteppers progress indicator shows "Step 2 of 4"

---

### US3: Password Reset Flow

**As a** user who forgot my password  
**I want to** reset my password via email link  
**So that** I can regain access to my account

**Acceptance Criteria:**

1. User navigates to `/auth/forgot-password`
2. Form displays Email input field
3. User enters email (e.g., user@example.com)
4. User clicks "إرسال رابط إعادة التعيين" button
5. System sends POST to `/api/v1/forgot-password`
6. On success: System displays success UAlert: "تحقق من بريدك الإلكتروني"
7. User receives email with reset link: `https://app.example.com/auth/reset-password?token=abc123`
8. User clicks reset link
9. System navigates to `/auth/reset-password?token=abc123`
10. System validates token server-side
11. If token valid: System displays form with Password + Confirm Password fields
12. User enters new password (8+ characters)
13. User enters confirmation
14. PasswordStrength component shows strength in real-time
15. User clicks "تحديث كلمة المرور"
16. System sends POST to `/api/v1/reset-password` with token + new password
17. On success: System redirects to `/auth/login` with message "تم تحديث كلمة المرور"
18. If token expired: System shows error: "انتهت صلاحية الرابط" + "Resend" link

---

## Design System Compliance Checklist

### Typography

- [ ] Geist Sans imported and applied as primary font
- [ ] Geist Mono imported for code/technical labels (if any)
- [ ] Headings (h1, h2, h3) use 600 weight
- [ ] Body text uses 400 weight
- [ ] UI elements (buttons, labels) use 500 weight
- [ ] Negative letter-spacing applied at large sizes: -2.4px (48px), -1.28px (32px), etc.
- [ ] Ligatures enabled globally: `font-feature-settings: "liga"`
- [ ] Line heights appropriate for each size: 1.00 tight (display), 1.50 relaxed (body)

### Colors

- [ ] White (`#ffffff`) background for pages and cards
- [ ] Vercel Black (`#171717`) for primary text and headings
- [ ] Neutral grays from palette: Gray 900, Gray 600, Gray 500, Gray 400, Gray 100, Gray 50
- [ ] No custom colors outside defined palette
- [ ] Error: Red (`#ff5b4f` or error token)
- [ ] Success: Green (`#00aa00` or success token)
- [ ] Focus ring: `hsla(212, 100%, 48%, 1)` (Focus Blue)

### Shadows & Borders

- [ ] Shadow-as-border used everywhere: `box-shadow: 0px 0px 0px 1px rgba(0,0,0,0.08)`
- [ ] No CSS `border` property used (only shadow-based borders)
- [ ] Multi-layer shadows on cards: border + elevation + ambient
- [ ] Border radius: 6px consistent throughout

### Layout

- [ ] Responsive breakpoints: 768px (tablet), 1024px (desktop)
- [ ] Mobile: full-width, 16px horizontal padding
- [ ] Tablet: max-width 600px, centered
- [ ] Desktop: max-width 400-600px for auth forms, centered
- [ ] Form fields: full width within container

### RTL Support

- [ ] Tailwind logical properties used: `ms-` (margin-start), `me-` (margin-end), `ps-` (padding-start), `pe-` (padding-end)
- [ ] No `ml-`, `mr-`, `pl-`, `pr-` classes in RTL contexts
- [ ] `text-start` / `text-end` instead of `text-left` / `text-right`
- [ ] `flex-row-reverse` for RTL flex layouts when needed
- [ ] `dir="rtl"` attribute on `<html>` when Arabic locale
- [ ] Form labels right-aligned in RTL mode (Nuxt UI handles this)

---

## RTL / Internationalization Specifications

### Locale Keys (i18n JSON files)

**frontend/locales/ar.json (Arabic)**

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
      "register": "إنشاء حساب جديد"
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
      "lastName": "الاسم الأخير",
      "email": "البريد الإلكتروني",
      "phone": "رقم الهاتف",
      "country": "الدولة",
      "password": "كلمة المرور",
      "confirmPassword": "تأكيد كلمة المرور"
    }
  }
}
```

**frontend/locales/en.json (English)**

```json
{
  "auth": {
    "login": {
      "title": "Sign In",
      "email": "Email",
      "password": "Password",
      "rememberMe": "Remember me",
      "submit": "Sign In",
      "forgotPassword": "Forgot password?",
      "register": "Create account"
    }
  }
}
```

### Directionality Handling

- Nuxt i18n plugin auto-applies `dir="rtl"` on `<html>` when locale is Arabic
- Form inputs right-align automatically in RTL context (Nuxt UI handles)
- Error messages inherit RTL directionality
- All text rendered via i18n composable (`{{ $t('auth.login.title') }}`)

---

## Testing Strategy

### Unit Tests (Vitest)

**Test File:** `frontend/tests/unit/schemas/auth.spec.ts`

```typescript
describe("Auth Schemas", () => {
  describe("loginSchema", () => {
    it("accepts valid email and password", () => {
      const result = loginSchema.safeParse({
        email: "user@example.com",
        password: "password123",
      });
      expect(result.success).toBe(true);
    });

    it("rejects invalid email", () => {
      const result = loginSchema.safeParse({
        email: "invalid-email",
        password: "password123",
      });
      expect(result.success).toBe(false);
      expect(result.error?.issues[0].message).toBe(
        "البريد الإلكتروني غير صالح",
      );
    });

    it("rejects short password", () => {
      const result = loginSchema.safeParse({
        email: "user@example.com",
        password: "pass123",
      });
      expect(result.success).toBe(false);
      expect(result.error?.issues[0].message).toContain("أحرف على الأقل");
    });
  });

  describe("registerStep3Schema", () => {
    it("validates password match", () => {
      const result = registerStep3Schema.safeParse({
        password: "password123",
        confirmPassword: "password123",
        phone: "+201234567890",
        country: "EG",
      });
      expect(result.success).toBe(true);
    });

    it("rejects mismatched passwords", () => {
      const result = registerStep3Schema.safeParse({
        password: "password123",
        confirmPassword: "password456",
        phone: "+201234567890",
        country: "EG",
      });
      expect(result.success).toBe(false);
      expect(result.error?.issues[0].message).toBe("كلمات المرور غير متطابقة");
    });
  });
});
```

**Test File:** `frontend/tests/unit/stores/auth.spec.ts`

```typescript
describe("useAuthStore", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("initializes with empty state", () => {
    const store = useAuthStore();
    expect(store.user).toBeNull();
    expect(store.isAuthenticated).toBe(false);
  });

  it("logs in user and stores token", async () => {
    const store = useAuthStore();
    // Mock API call
    vi.mock("~/composables/useAuthApi", () => ({
      useAuthApi: () => ({
        login: vi.fn().mockResolvedValue({
          token: "test-token",
          user: { id: 1, email: "user@example.com" },
        }),
      }),
    }));

    await store.login("user@example.com", "password123");
    expect(store.token).toBe("test-token");
    expect(store.user.email).toBe("user@example.com");
    expect(store.isAuthenticated).toBe(true);
    expect(localStorage.getItem("auth_token")).toBe("test-token");
  });

  it("logs out and clears state", async () => {
    const store = useAuthStore();
    store.token = "test-token";
    store.user = { id: 1, email: "user@example.com" };
    localStorage.setItem("auth_token", "test-token");

    store.logout();
    expect(store.token).toBeNull();
    expect(store.user).toBeNull();
    expect(store.isAuthenticated).toBe(false);
    expect(localStorage.getItem("auth_token")).toBeNull();
  });
});
```

### E2E Tests (Playwright)

**Test File:** `frontend/tests/e2e/auth.spec.ts`

```typescript
import { test, expect } from "@playwright/test";

test.describe("Auth Pages", () => {
  test.describe("Login", () => {
    test("login with valid credentials redirects to dashboard", async ({
      page,
    }) => {
      await page.goto("/auth/login");
      await page.fill('[data-testid="email-input"]', "customer@example.com");
      await page.fill('[data-testid="password-input"]', "password123");
      await page.click('[data-testid="login-button"]');
      await expect(page).toHaveURL("/dashboard");
    });

    test("login shows Arabic error on invalid credentials", async ({
      page,
    }) => {
      await page.goto("/auth/login");
      await page.fill('[data-testid="email-input"]', "wrong@example.com");
      await page.fill('[data-testid="password-input"]', "wrongpass");
      await page.click('[data-testid="login-button"]');

      const alert = page.locator('[role="alert"]');
      await expect(alert).toBeVisible();
      const text = await alert.textContent();
      expect(text).toContain("بيانات تسجيل الدخول"); // Arabic error text
    });
  });

  test.describe("Registration Multi-Step", () => {
    test("complete registration flow", async ({ page }) => {
      // Step 1: Select account type
      await page.goto("/auth/register");
      await page.click('input[value="customer"]');
      await page.click('button:has-text("التالي")');

      // Step 2: Personal info
      await page.fill('[data-testid="firstName-input"]', "محمد");
      await page.fill('[data-testid="lastName-input"]', "أحمد");
      await page.fill('[data-testid="email-input"]', "customer@example.com");
      await page.click('button:has-text("التالي")');

      // Step 3: Contact info
      await page.fill('[data-testid="phone-input"]', "+201234567890");
      await page.selectOption('[data-testid="country-select"]', "EG");
      await page.fill('[data-testid="password-input"]', "securePass123");
      await page.fill('[data-testid="confirmPassword-input"]', "securePass123");
      await page.click('button:has-text("إنشاء الحساب")');

      // Step 4: Verification pending
      const verificationMessage = page.locator("text=تحقق من بريدك الإلكتروني");
      await expect(verificationMessage).toBeVisible();
    });
  });

  test.describe("Password Reset", () => {
    test("reset password flow", async ({ page }) => {
      // Forgot password
      await page.goto("/auth/forgot-password");
      await page.fill('[data-testid="email-input"]', "user@example.com");
      await page.click('[data-testid="submit-button"]');

      const successMsg = page.locator("text=تحقق من بريدك الإلكتروني");
      await expect(successMsg).toBeVisible();

      // Simulate email link click
      await page.goto(
        "/auth/reset-password?token=valid-reset-token-from-email",
      );

      // Fill new password
      await page.fill('[data-testid="password-input"]', "newPassword123");
      await page.fill(
        '[data-testid="confirmPassword-input"]',
        "newPassword123",
      );
      await page.click('[data-testid="submit-button"]');

      // Redirect to login
      await expect(page).toHaveURL("/auth/login");
    });
  });

  test.describe("RTL Layout", () => {
    test("login form renders in RTL", async ({ page }) => {
      await page.goto("/auth/login");

      // Check HTML dir attribute
      const htmlDir = await page.locator("html").getAttribute("dir");
      expect(htmlDir).toBe("rtl");

      // Check input alignment (should be right-aligned via logical properties)
      const emailInput = page.locator('[data-testid="email-input"]');
      const dir = await emailInput.evaluate(
        (el) => window.getComputedStyle(el).direction,
      );
      expect(dir).toBe("rtl");
    });

    test("error messages render in Arabic with RTL", async ({ page }) => {
      await page.goto("/auth/login");

      // Leave email empty and try submit
      await page.fill('[data-testid="password-input"]', "password123");
      await page.click('[data-testid="login-button"]');

      // Error should be in Arabic
      const errorMsg = page.locator("text=البريد الإلكتروني");
      await expect(errorMsg).toBeVisible();
    });
  });
});
```

---

## Dependencies

### Upstream Dependencies (Must complete first)

- **STAGE_03_AUTHENTICATION** — Backend API endpoints must be implemented:
  - POST `/api/v1/login`
  - POST `/api/v1/register`
  - POST `/api/v1/forgot-password`
  - POST `/api/v1/reset-password`
  - POST `/api/v1/verify-email`
  - GET `/api/v1/profile`
  - PUT `/api/v1/profile`
  - POST `/api/v1/resend-verification-email`
  - Response format: StandardErrorResponse contract
  - Error codes: `INVALID_CREDENTIALS`, `EMAIL_ALREADY_EXISTS`, `TOKEN_EXPIRED`, etc.

- **STAGE_29_NUXT_SHELL** — Frontend app shell must be set up:
  - Nuxt 3 app initialized
  - Nuxt UI (`@nuxt/ui`) installed and configured
  - Pinia store system initialized
  - i18n (internationalization) plugin configured
  - Tailwind CSS v4 configured with RTL support
  - TypeScript enabled
  - Vitest configured for unit tests
  - Playwright configured for E2E tests

### Downstream Dependencies (Depends on this stage)

- All authenticated pages: dashboard, project management, contractor earnings, field engineer reports, profile management
- Admin user management dashboard
- Role-based dashboards (customer, contractor, architect, field engineer)

---

## Risk Assessment & Mitigation

### Risks

| Risk                              | Level  | Description                                           | Mitigation                                                   |
| --------------------------------- | ------ | ----------------------------------------------------- | ------------------------------------------------------------ |
| **Backend API Delays**            | HIGH   | Auth pages depend on backend endpoints                | Start with mock API; swap with real endpoints when ready     |
| **RTL Implementation Complexity** | MEDIUM | Ensuring all layouts work correctly in RTL            | Use Tailwind logical properties; test both LTR and RTL early |
| **Form Validation UX**            | MEDIUM | Balancing real-time validation with performance       | Debounce validation; focus on blur events                    |
| **Multi-Step State Persistence**  | MEDIUM | Losing registration data on page refresh              | Store form data in Pinia; sync with localStorage             |
| **Token Management Edge Cases**   | MEDIUM | Expired tokens, refresh logic, logout race conditions | Implement token refresh composable; handle errors gracefully |
| **Accessibility Compliance**      | MEDIUM | Ensuring WCAG 2.1 AA compliance                       | Automated tests + manual testing with screen readers         |
| **Browser Compatibility**         | LOW    | CSS logical properties not supported in old browsers  | Target modern browsers only; use PostCSS plugin if needed    |

---

## Success Criteria

### Specification Sign-Off

- [ ] All pages defined (Login, Register, Forgot Password, Reset Password, Verify Email, Profile)
- [ ] All form fields specified with Zod schema definitions
- [ ] Nuxt UI component mapping complete
- [ ] Testing strategy defined (unit + E2E)
- [ ] RTL support specifications detailed
- [ ] Design system compliance checklist provided
- [ ] Dependencies clearly documented
- [ ] No `[NEEDS CLARIFICATION]` markers remain

### Implementation Complete

- [ ] All 6 pages implemented and functional
- [ ] All shared components created (AuthLayout, AuthCard, PasswordStrength, RoleSelector, OtpInput)
- [ ] Pinia stores (`useAuthStore`, `useUserStore`) implemented
- [ ] VeeValidate + Zod validation working with Arabic error messages
- [ ] Auth middleware protecting `/profile` route
- [ ] API composable integrating with backend endpoints
- [ ] Unit tests passing (Vitest)
- [ ] E2E tests passing (Playwright)
- [ ] RTL layout verified and working
- [ ] Design system compliance verified
- [ ] Bundle size < 100KB (gzipped)
- [ ] Page load time < 2 seconds

---

## Sign-Off

**Specification Owner:** AI Agent  
**Approved by:** Pending  
**Status:** DRAFT (Ready for Clarification)  
**Last Updated:** 2026-04-12  
**Next Step:** STAGE_30_CLARIFY
