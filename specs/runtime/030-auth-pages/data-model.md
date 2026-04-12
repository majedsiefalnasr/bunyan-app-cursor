# Auth Pages — Data Model & State Schema

## Pinia Store: useAuthStore

### State Interface

```typescript
interface AuthState {
  user: User | null;
  token: string | null;
  isLoading: boolean;
  error: string | null;
}

interface User {
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
```

### Actions

#### login(email: string, password: string)

**Purpose:** Authenticate user and store token.

**Logic:**

1. Set `isLoading = true`
2. Call `useAuthApi().login(email, password)`
3. On success:
   - Store `token` in state
   - Store `user` in state
   - Save token to `localStorage` (key: "auth_token")
   - Clear `error`
   - Redirect to `/dashboard` (or return for page to handle)
4. On error:
   - Store error message in `error`
   - Do NOT redirect
   - Return error for page to display in UAlert

**Example:**

```typescript
const login = async (email: string, password: string) => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await useAuthApi().login(email, password);
    token.value = response.data.token;
    user.value = response.data.user;
    localStorage.setItem("auth_token", token.value);
  } catch (err) {
    error.value = err.data?.message || "حدث خطأ ما";
    throw err;
  } finally {
    isLoading.value = false;
  }
};
```

**Returns:** Promise (resolves on success, rejects on error)

#### register(data: RegisterData)

**Purpose:** Register new user account.

**Logic:**

1. Set `isLoading = true`
2. Call `useAuthApi().register(data)`
3. On success:
   - Store user info in state (but DO NOT auto-login)
   - Set "verification pending" state
   - Return response for page to show Step 4
4. On error:
   - Store error message
   - Return error for page to display

**Example:**

```typescript
const register = async (data: RegisterData) => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await useAuthApi().register(data);
    // Don't set token/user yet (email not verified)
    // Store registration info for Step 4
    return response;
  } catch (err) {
    error.value = err.data?.message;
    throw err;
  } finally {
    isLoading.value = false;
  }
};
```

**Returns:** Promise (resolves with response, rejects on error)

#### logout()

**Purpose:** Clear authentication state and redirect to login.

**Logic:**

1. Set `user = null`
2. Set `token = null`
3. Clear `error`
4. Remove token from `localStorage`
5. Redirect to `/auth/login`

**Example:**

```typescript
const logout = () => {
  user.value = null;
  token.value = null;
  error.value = null;
  localStorage.removeItem("auth_token");
  navigateTo("/auth/login");
};
```

#### fetchUser()

**Purpose:** Fetch current user data (called on app load if token exists).

**Logic:**

1. Set `isLoading = true`
2. Call `useAuthApi().getProfile()` (protected endpoint)
3. On success:
   - Set `user` state with fetched data
   - Clear `error`
4. On 401 (token expired):
   - Call `logout()`
   - Redirect to login
5. On other error:
   - Store error message
   - Do NOT logout (user can retry)

**Example:**

```typescript
const fetchUser = async () => {
  if (!token.value) return;
  isLoading.value = true;
  try {
    const response = await useAuthApi().getProfile();
    user.value = response.data.user;
  } catch (err) {
    if (err.status === 401) {
      logout();
    } else {
      error.value = err.data?.message;
    }
  } finally {
    isLoading.value = false;
  }
};
```

#### refreshToken()

**Purpose:** Refresh authentication token (future enhancement).

**Logic:**

1. Call backend refresh endpoint
2. Update token in state + localStorage
3. Handle expiry (redirect to login)

**Example (deferred to Phase 2):**

```typescript
const refreshToken = async () => {
  try {
    const response = await useAuthApi().refreshToken();
    token.value = response.data.token;
    localStorage.setItem("auth_token", token.value);
  } catch (err) {
    logout();
  }
};
```

### Computed Properties

#### isAuthenticated

```typescript
const isAuthenticated = computed(() => !!token.value && !!user.value);
```

**Purpose:** Quick check if user is authenticated.

**Returns:** Boolean

#### displayName

```typescript
const displayName = computed(() =>
  user.value ? `${user.value.firstName} ${user.value.lastName}` : "",
);
```

**Purpose:** Get formatted user display name.

**Returns:** String

#### hasRole(role: string)

```typescript
const hasRole = (role: string) => user.value?.roles?.includes(role) ?? false;
```

**Purpose:** Check if user has specific role (for conditional rendering).

**Returns:** Boolean

### Persistence

- **Token:** `localStorage` with key `"auth_token"`
- **User:** Pinia state only (fetched on app load)
- **Init on mount:** Check if token exists in localStorage, fetch user if token valid

**Example (in app.vue or layout):**

```typescript
const authStore = useAuthStore();

onMounted(async () => {
  // Check if token persisted
  if (authStore.token && !authStore.user) {
    // Fetch user data
    await authStore.fetchUser();
  }
});
```

---

## Pinia Store: useUserStore

### State Interface

```typescript
interface UserState {
  profile: UserProfile | null;
  isLoading: boolean;
  error: string | null;
}

interface UserProfile {
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
```

### Actions

#### fetchProfile()

**Purpose:** Fetch current user profile (called on profile page load).

**Logic:**

1. Set `isLoading = true`
2. Call `useAuthApi().getProfile()`
3. On success: Set `profile` state, clear `error`
4. On 401: Call `useAuthStore().logout()`
5. On error: Store error message

**Example:**

```typescript
const fetchProfile = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await useAuthApi().getProfile();
    profile.value = response.data.user;
  } catch (err) {
    if (err.status === 401) {
      useAuthStore().logout();
    } else {
      error.value = err.data?.message;
    }
  } finally {
    isLoading.value = false;
  }
};
```

#### updateProfile(data: Partial<UserProfile>)

**Purpose:** Update user profile information.

**Logic:**

1. Set `isLoading = true`
2. Call `useAuthApi().updateProfile(data)`
3. On success:
   - Update `profile` state
   - Update `useAuthStore().user` state
   - Clear `error`
   - Return response
4. On error:
   - Store error message
   - Throw error for page to handle

**Example:**

```typescript
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
  } catch (err) {
    error.value = err.data?.message;
    throw err;
  } finally {
    isLoading.value = false;
  }
};
```

### Computed Properties

#### isProfileLoaded

```typescript
const isProfileLoaded = computed(() => !!profile.value);
```

**Purpose:** Check if profile data has been fetched.

**Returns:** Boolean

#### preferredLocale

```typescript
const preferredLocale = computed(
  () => profile.value?.preferences?.locale || "ar",
);
```

**Purpose:** Get user's preferred locale.

**Returns:** 'ar' | 'en'

---

## Form Data Models

### LoginFormData

**Schema validation:** `loginSchema`

```typescript
interface LoginFormData {
  email: string; // Required: valid email format
  password: string; // Required: min 8 characters
  rememberMe?: boolean; // Optional: default false
}
```

**Zod Schema:**

```typescript
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
```

### RegisterFormData

**Schema validation:** Three separate schemas (one per step)

#### Step 1: Account Type Selection

```typescript
interface RegisterStep1Data {
  accountType: "customer" | "contractor"; // Required
}
```

**Zod Schema:**

```typescript
export const registerStep1Schema = z.object({
  accountType: z.enum(["customer", "contractor"], {
    message: "يجب تحديد نوع الحساب",
  }),
});
```

#### Step 2: Personal Information

```typescript
interface RegisterStep2Data {
  firstName: string; // Required: min 2 chars, max 50
  lastName: string; // Required: min 2 chars, max 50
  email: string; // Required: valid email format
}
```

**Zod Schema:**

```typescript
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
```

#### Step 3: Contact Information & Password

```typescript
interface RegisterStep3Data {
  phone: string; // Required: regex /^[+0-9]{7,}$/
  country: string; // Required: min 2 chars
  password: string; // Required: min 8 chars
  confirmPassword: string; // Required: match password
}
```

**Zod Schema:**

```typescript
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
```

### ForgotPasswordFormData

**Schema validation:** `forgotPasswordSchema`

```typescript
interface ForgotPasswordFormData {
  email: string; // Required: valid email format
}
```

**Zod Schema:**

```typescript
export const forgotPasswordSchema = z.object({
  email: z
    .string()
    .min(1, { message: "البريد الإلكتروني مطلوب" })
    .email({ message: "البريد الإلكتروني غير صالح" }),
});
```

### ResetPasswordFormData

**Schema validation:** `resetPasswordSchema`

```typescript
interface ResetPasswordFormData {
  token: string; // From URL parameter (?token=abc)
  password: string; // Required: min 8 chars
  confirmPassword: string; // Required: match password
}
```

**Zod Schema:**

```typescript
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
```

**Note:** Token is extracted from URL, not form data.

### ProfileFormData

**Schema validation:** `profileSchema`

```typescript
interface ProfileFormData {
  firstName: string; // Required: min 2 chars, max 50
  lastName: string; // Required: min 2 chars, max 50
  email: string; // Required: valid email (read-only display)
  phone: string; // Required: regex /^[+0-9]{7,}$/
  country: string; // Required: min 2 chars
}
```

**Zod Schema:**

```typescript
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

---

## API Response Models

### SuccessResponse

**Generic wrapper for all success responses:**

```typescript
interface SuccessResponse<T> {
  success: true;
  data: T;
  message: string; // User-friendly message (Arabic)
}
```

### LoginResponse

```typescript
interface LoginResponse extends SuccessResponse<LoginResponseData> {}

interface LoginResponseData {
  token: string; // JWT token for Authorization header
  user: User;
}
```

**Example:**

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

### RegisterResponse

```typescript
interface RegisterResponse extends SuccessResponse<RegisterResponseData> {}

interface RegisterResponseData {
  user: {
    id: string;
    email: string;
    firstName: string;
    lastName: string;
  };
}
```

**Example:**

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

### ProfileResponse

```typescript
interface ProfileResponse extends SuccessResponse<ProfileResponseData> {}

interface ProfileResponseData {
  user: UserProfile;
}
```

### ErrorResponse

**StandardErrorResponse (used by all error cases):**

```typescript
interface ErrorResponse {
  success: false;
  data: null;
  message: string; // Form-level error message (Arabic)
  errors: Record<string, string[]>; // Field-level errors (Arabic)
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

---

## Validation Rules Detail

### Email Validation

**Zod:**

```typescript
z.string().email("البريد الإلكتروني غير صالح");
```

**RFC 5322 Compliant:** Nuxt/Zod handles proper email validation

**Examples (valid):**

- user@example.com
- user.name+tag@example.co.uk
- user_123@subdomain.example.com

**Examples (invalid):**

- user@
- @example.com
- user@.com
- user name@example.com

### Password Validation

**Zod (basic MVP):**

```typescript
z.string().min(8, "كلمة المرور يجب أن تكون 8 أحرف على الأقل");
```

**Character requirements:**

- Minimum 8 characters (enforced by Zod)
- No complexity requirements (e.g., uppercase, numbers, special chars) for MVP
- Backend can enforce stricter rules

**Examples (valid):**

- "password123"
- "MyPassword!"
- "abcdefgh"

**Examples (invalid):**

- "pass123" (7 chars)
- "" (empty)

### Phone Validation

**Zod:**

```typescript
z.string().regex(/^[+0-9]{7,}$/, "رقم الهاتف غير صالح");
```

**Format:**

- Optional `+` prefix (international)
- Followed by 7+ digits
- No spaces or hyphens in final value

**Examples (valid):**

- "+966501234567" (Saudi Arabia)
- "201234567890" (Egypt)
- "+11234567890" (USA)
- "1234567" (7 digits minimum)

**Examples (invalid):**

- "+966 50 1234567" (contains spaces)
- "966-501234567" (contains hyphen)
- "50123456" (6 digits, too short)

### Country Validation

**Zod:**

```typescript
z.string().min(2, "يجب تحديد البلد");
```

**Format:**

- Minimum 2 characters (country code or name)
- Examples: "SA" (code), "السعودية" (Arabic name), "Saudi Arabia"

### Confirm Password Matching

**Zod (with refine):**

```typescript
z.object({
  password: z.string(),
  confirmPassword: z.string(),
}).refine((d) => d.password === d.confirmPassword, {
  message: "كلمات المرور غير متطابقة",
  path: ["confirmPassword"],
});
```

**Logic:**

1. Parse both password fields
2. Compare strings for exact match
3. If mismatch: error on confirmPassword field

---

## Component Props & Events

### AuthCard Component

**Props:**

```typescript
interface AuthCardProps {
  title?: string; // Card heading
  subtitle?: string; // Card description
  class?: string; // Additional CSS classes
}
```

**Events:** None (purely presentational)

**Example:**

```vue
<AuthCard title="تسجيل الدخول" subtitle="ادخل بيانات حسابك">
  <!-- Form content slot -->
</AuthCard>
```

### PasswordStrength Component

**Props:**

```typescript
interface PasswordStrengthProps {
  password: string; // Current password value
  showLabel?: boolean; // Show strength label (default: true)
}
```

**Events:** None (purely reactive)

**Local State:**

```typescript
const strength = computed(() => {
  // Analyze password
  // Return: 'weak' | 'fair' | 'good' | 'strong'
});

const percentage = computed(() => {
  // 0-100% based on strength
});

const color = computed(() => {
  // 'red' (weak) | 'yellow' (fair) | 'green' (good/strong)
});
```

**Example:**

```vue
<PasswordStrength :password="formValues.password" />
```

### RoleSelector Component

**Props:**

```typescript
interface RoleSelectorProps {
  modelValue: "customer" | "contractor";
  disabled?: boolean;
}
```

**Events:**

```typescript
emit('update:modelValue', value: 'customer' | 'contractor')
```

**Example:**

```vue
<RoleSelector v-model="accountType" @update:modelValue="onRoleChange" />
```

### OtpInput Component

**Props:**

```typescript
interface OtpInputProps {
  modelValue: string;
  length?: number; // Default: 6
  disabled?: boolean;
}
```

**Events:**

```typescript
emit('update:modelValue', value: string)
emit('complete', value: string)
```

**Example:**

```vue
<OtpInput v-model="otp" :length="6" @complete="onOtpComplete" />
```

---

## Middleware State

### Auth Middleware

**File:** `frontend/middleware/auth.ts`

```typescript
export default defineRouteMiddleware((to, from) => {
  const authStore = useAuthStore();

  if (!authStore.isAuthenticated) {
    // Redirect unauthenticated users to login
    return navigateTo("/auth/login");
  }

  // Continue to requested route
});
```

**Routes protected by middleware:**

- `/profile`
- `/dashboard`
- `/projects/*`
- `/contractor/earnings`
- `/field-engineer/reports`
- `/admin/*`

**Usage in page:**

```vue
<script setup lang="ts">
definePageMeta({
  middleware: "auth",
});
</script>
```

---

## Session Management

### Token Lifecycle

1. **Login:**
   - Backend generates JWT token
   - Frontend stores in localStorage (key: "auth_token")
   - Token attached to all subsequent requests

2. **API Calls:**
   - Frontend includes: `Authorization: Bearer <token>`
   - Backend validates token signature
   - On 401: Token expired or invalid

3. **Token Expiry:**
   - Backend returns 401 status
   - Frontend catches 401
   - Calls `useAuthStore().logout()`
   - Redirects to `/auth/login`
   - User prompted to login again

4. **Logout:**
   - Frontend clears token and user from state
   - Removes token from localStorage
   - Redirects to `/auth/login`

5. **App Load (Cold Start):**
   - Check `localStorage` for token
   - If exists: Call `useAuthStore().fetchUser()`
   - If fetch succeeds: User authenticated
   - If fetch fails (401): Clear token, redirect to login

### User State Lifecycle

```
User not logged in (isAuthenticated = false)
        ↓
User navigates to /auth/login
        ↓
User enters credentials & submits
        ↓
API responds with token + user data
        ↓
useAuthStore stores token + user (isAuthenticated = true)
        ↓
Redirect to /dashboard
        ↓
Dashboard page loads
        ↓
Token included in all API requests
        ↓
User navigates to /profile
        ↓
Profile page protected by middleware (auth check passes)
        ↓
Profile fetched via GET /api/v1/profile
        ↓
User updates profile
        ↓
PUT /api/v1/profile called with token
        ↓
useUserStore updated with new data
        ↓
User clicks logout
        ↓
useAuthStore.logout() called
        ↓
Token cleared, user cleared
        ↓
Redirect to /auth/login
        ↓
User not logged in (isAuthenticated = false)
```

---

## Error State Model

### Validation Errors (Client-Side)

**Source:** Zod schema validation via VeeValidate

```typescript
interface ValidationError {
  field: string; // Form field name
  message: string; // Error message (Arabic)
  type: "required" | "format" | "min_length" | "mismatch" | "custom";
}
```

**Display:**

- Location: Below input field
- Style: Red text (UFormField :error prop)
- Trigger: After blur or on submit

**Example:**

```vue
<UFormField label="البريد الإلكتروني" :error="errors.email">
  <UInput v-model="values.email" type="email" />
</UFormField>

<!-- Displays: "البريد الإلكتروني غير صالح" in red below input -->
```

### API Errors (Server-Side)

**Source:** StandardErrorResponse from backend

```typescript
interface ApiError {
  code: string; // Error code (e.g., 'INVALID_CREDENTIALS')
  message: string; // User-facing message (Arabic)
  statusCode: number; // HTTP status code (401, 422, 500, etc.)
  fieldErrors?: Record<string, string[]>; // Field-level errors
  details?: Record<string, any>;
}
```

**Display:**

- **Field-level errors:** Mapped to form fields below inputs
- **Form-level errors:** Shown in UAlert at top of form
- **Style:** Red background with error icon (UAlert color="error")

**Example (field-level):**

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

**Example (form-level):**

```json
{
  "success": false,
  "data": null,
  "message": "بيانات الاعتماد غير صحيحة",
  "errors": {}
}
```

### UI Error Display

```typescript
// In page component
try {
  await authStore.login(email, password);
} catch (error) {
  // Determine error type
  if (error.data?.errors && Object.keys(error.data.errors).length > 0) {
    // Field-level errors: populate form errors (VeeValidate handles display)
    formErrors.value = error.data.errors;
  } else if (error.data?.message) {
    // Form-level error: show in UAlert
    alertMessage.value = error.data.message;
  } else {
    // Unknown error
    alertMessage.value = "حدث خطأ ما. يرجى المحاولة لاحقًا.";
  }
}
```

---

## Type Safety Strategy

### TypeScript Interfaces

All data flows through typed interfaces:

1. **Form Data:** LoginFormData, RegisterFormData, etc.
2. **State:** AuthState, UserState
3. **API Responses:** LoginResponse, RegisterResponse, ErrorResponse
4. **Zod Schemas:** Inferred types from schemas

**Example:**

```typescript
// From Zod schema, infer TypeScript type
type LoginForm = z.infer<typeof loginSchema>;

// Use in component
const form = ref<LoginForm>({
  email: "",
  password: "",
  rememberMe: false,
});
```

### Validation Pipeline

```
User Input
    ↓
Zod Schema (client-side UX validation)
    ↓
VeeValidate Form State (track errors)
    ↓
UFormField (display errors)
    ↓
User Corrects Input
    ↓
Form Valid
    ↓
API Call (useAuthApi)
    ↓
Backend Validation (server-side security)
    ↓
StandardErrorResponse (if validation fails)
    ↓
Map field errors back to form
    ↓
User Corrects Input (server feedback)
```

---

## Summary

This data model provides:

✅ **Type safety:** Full TypeScript support end-to-end
✅ **Separation of concerns:** Stores, composables, components
✅ **Error handling:** Client + server validation
✅ **Persistence:** Token in localStorage, user in Pinia state
✅ **Scalability:** Easy to add new stores, schemas, validation rules
✅ **Testing:** All types testable with unit tests
✅ **Accessibility:** All data shapes support RTL, Arabic
