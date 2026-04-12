# Auth Pages — API Contract & Component Interface

## API Contract

### Overview

All API endpoints follow Laravel RESTful conventions with Sanctum authentication. All responses include a wrapper object with success flag, data, message, and errors.

**Base URL:** `https://api.bunyan.local/api/v1` (via environment variable)

**Authentication:** Bearer token in Authorization header (except login/register)

```
Authorization: Bearer {token}
```

---

## Endpoint Specifications

### POST /api/v1/login

**Purpose:** Authenticate user with email and password.

**Method:** POST

**Authentication:** None required

**Request Body:**

```json
{
  "email": "user@example.com",
  "password": "SecurePassword123"
}
```

**Request Validation:**

- `email` (required): valid email format
- `password` (required): 8+ characters (backend enforces)

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c",
    "user": {
      "id": "user-123",
      "email": "user@example.com",
      "firstName": "محمد",
      "lastName": "علي",
      "accountType": "customer",
      "phone": "+966501234567",
      "country": "السعودية",
      "emailVerified": true,
      "roles": ["customer"],
      "createdAt": "2026-04-12T10:00:00Z",
      "updatedAt": "2026-04-12T10:00:00Z"
    }
  },
  "message": "تم تسجيل الدخول بنجاح"
}
```

**Error Response (401 Unauthorized):**

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

**Error Response (422 Unprocessable Entity):**

```json
{
  "success": false,
  "data": null,
  "message": "فشل التحقق من صحة البيانات",
  "errors": {
    "email": ["البريد الإلكتروني غير صالح"],
    "password": ["كلمة المرور مطلوبة"]
  }
}
```

**Rate Limiting:** 5 attempts per minute per IP

---

### POST /api/v1/register

**Purpose:** Register new user account.

**Method:** POST

**Authentication:** None required

**Request Body:**

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

**Request Validation:**

- `accountType` (required): 'customer' or 'contractor'
- `firstName` (required): 2-50 characters
- `lastName` (required): 2-50 characters
- `email` (required): valid email, must be unique
- `phone` (required): regex /^[+0-9]{7,}$/
- `country` (required): 2+ characters
- `password` (required): 8+ characters

**Success Response (201 Created):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": "user-124",
      "email": "newuser@example.com",
      "firstName": "محمد",
      "lastName": "علي",
      "accountType": "customer",
      "roles": ["customer"]
    }
  },
  "message": "تم التسجيل بنجاح. يرجى التحقق من بريدك الإلكتروني."
}
```

**Error Response (422 Validation Failed):**

```json
{
  "success": false,
  "data": null,
  "message": "فشل التحقق من صحة البيانات",
  "errors": {
    "email": ["البريد الإلكتروني مسجل بالفعل"],
    "phone": ["رقم الهاتف غير صالح"]
  }
}
```

**Side Effects:**

- User record created in DB
- Email verification link sent to email address
- User NOT automatically logged in (must verify email first or manual login)

**Rate Limiting:** 3 registrations per 24 hours per IP

---

### POST /api/v1/forgot-password

**Purpose:** Request password reset email.

**Method:** POST

**Authentication:** None required

**Request Body:**

```json
{
  "email": "user@example.com"
}
```

**Request Validation:**

- `email` (required): valid email format

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": null,
  "message": "تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني"
}
```

**Error Response (404 Not Found):**

```json
{
  "success": false,
  "data": null,
  "message": "لم يتم العثور على حساب بهذا البريد الإلكتروني",
  "errors": {}
}
```

**Side Effects:**

- Password reset link generated with expiry (24 hours typical)
- Email sent with reset link: `https://app.bunyan.local/auth/reset-password?token=xyz789`

**Rate Limiting:** 3 requests per 24 hours per email

---

### POST /api/v1/validate-reset-token

**Purpose:** Validate reset token on page load.

**Method:** POST

**Authentication:** None required

**Request Body:**

```json
{
  "token": "reset-token-from-email"
}
```

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "valid": true
  },
  "message": "الرابط صحيح"
}
```

**Error Response (400 Bad Request):**

```json
{
  "success": false,
  "data": null,
  "message": "الرابط منتهي الصلاحية أو غير صالح",
  "errors": {}
}
```

---

### POST /api/v1/reset-password

**Purpose:** Reset password with token from email link.

**Method:** POST

**Authentication:** None required

**Request Body:**

```json
{
  "token": "reset-token-from-email",
  "password": "NewPassword123"
}
```

**Request Validation:**

- `token` (required): valid reset token
- `password` (required): 8+ characters

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": null,
  "message": "تم إعادة تعيين كلمة المرور بنجاح"
}
```

**Error Response (400 Bad Request):**

```json
{
  "success": false,
  "data": null,
  "message": "الرابط منتهي الصلاحية أو غير صالح",
  "errors": {}
}
```

**Error Response (422 Validation Failed):**

```json
{
  "success": false,
  "data": null,
  "message": "فشل التحقق من صحة البيانات",
  "errors": {
    "password": ["كلمة المرور قصيرة جدًا"]
  }
}
```

**Side Effects:**

- Password updated in DB (hashed)
- Reset token invalidated

---

### POST /api/v1/verify-email

**Purpose:** Verify email with token from email link.

**Method:** POST

**Authentication:** None required

**Request Body:**

```json
{
  "token": "verify-token-from-email"
}
```

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": "user-124",
      "email": "user@example.com",
      "emailVerified": true,
      "firstName": "محمد",
      "lastName": "علي"
    }
  },
  "message": "تم التحقق من البريد الإلكتروني بنجاح"
}
```

**Error Response (400 Bad Request):**

```json
{
  "success": false,
  "data": null,
  "message": "الرابط منتهي الصلاحية أو غير صالح",
  "errors": {}
}
```

**Side Effects:**

- User record updated: `emailVerified = true`
- Verification token invalidated

---

### POST /api/v1/resend-verification-email

**Purpose:** Resend verification email.

**Method:** POST

**Authentication:** None required

**Request Body:**

```json
{
  "email": "user@example.com"
}
```

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": null,
  "message": "تم إرسال رابط التحقق إلى بريدك الإلكتروني"
}
```

**Error Response (404 Not Found):**

```json
{
  "success": false,
  "data": null,
  "message": "لم يتم العثور على حساب بهذا البريد الإلكتروني",
  "errors": {}
}
```

---

### GET /api/v1/profile

**Purpose:** Fetch current authenticated user profile.

**Method:** GET

**Authentication:** Required (Bearer token)

**Headers:**

```
Authorization: Bearer {token}
```

**Request Body:** None

**Success Response (200 OK):**

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
      "roles": ["customer"],
      "preferences": {
        "locale": "ar",
        "notificationsEnabled": true
      },
      "createdAt": "2026-04-12T10:00:00Z",
      "updatedAt": "2026-04-12T10:00:00Z"
    }
  },
  "message": "تم جلب بيانات المستخدم بنجاح"
}
```

**Error Response (401 Unauthorized):**

```json
{
  "success": false,
  "data": null,
  "message": "غير مصرح",
  "errors": {}
}
```

---

### PUT /api/v1/profile

**Purpose:** Update user profile information.

**Method:** PUT

**Authentication:** Required (Bearer token)

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
  "firstName": "محمد",
  "lastName": "محمود",
  "phone": "+966501234567",
  "country": "الإمارات"
}
```

**Request Validation:**

- `firstName` (required): 2-50 characters
- `lastName` (required): 2-50 characters
- `phone` (required): regex /^[+0-9]{7,}$/
- `country` (required): 2+ characters
- `email` (read-only, ignored if provided)

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": "user-123",
      "email": "user@example.com",
      "firstName": "محمد",
      "lastName": "محمود",
      "phone": "+966501234567",
      "country": "الإمارات",
      "emailVerified": true,
      "roles": ["customer"],
      "updatedAt": "2026-04-12T11:30:00Z"
    }
  },
  "message": "تم تحديث الملف الشخصي بنجاح"
}
```

**Error Response (401 Unauthorized):**

```json
{
  "success": false,
  "data": null,
  "message": "غير مصرح",
  "errors": {}
}
```

**Error Response (422 Validation Failed):**

```json
{
  "success": false,
  "data": null,
  "message": "فشل التحقق من صحة البيانات",
  "errors": {
    "phone": ["رقم الهاتف غير صالح"],
    "firstName": ["الاسم الأول قصير جدًا"]
  }
}
```

**Side Effects:**

- User record updated with new values
- `updatedAt` timestamp updated

---

## Error Codes Reference

| HTTP Code | Endpoint                                     | Meaning                              |
| --------- | -------------------------------------------- | ------------------------------------ |
| 200       | Any                                          | Success (OK)                         |
| 201       | POST /register                               | Success (Created)                    |
| 400       | /reset-password, /verify-email               | Invalid/expired token                |
| 401       | /profile, /profile                           | Unauthorized (token missing/invalid) |
| 404       | /forgot-password, /resend-verification-email | User/email not found                 |
| 422       | Any                                          | Validation error (see errors field)  |
| 429       | Any                                          | Rate limit exceeded                  |
| 500       | Any                                          | Server error                         |

---

## Rate Limiting

All endpoints implement rate limiting. Client should:

1. Respect X-RateLimit-\* headers in response
2. Handle 429 (Too Many Requests) gracefully
3. Display user-friendly message: "يرجى الانتظار قبل المحاولة مرة أخرى"

**Limits:**

- **Login:** 5 attempts per minute per IP
- **Register:** 3 registrations per 24 hours per IP
- **Forgot Password:** 3 requests per 24 hours per email
- **API Calls (authenticated):** 100 requests per minute per user

---

## Component Interface Contract

### AuthLayout Component

**File:** `frontend/components/auth/AuthLayout.vue`

**Purpose:** RTL-aware layout wrapper for all auth pages.

**Props:**

```typescript
interface AuthLayoutProps {
  class?: string;
}
```

**Slots:**

```
default: Page content (form, cards, etc.)
```

**Styling:**

- Min-height: 100vh
- Background: white
- Flex: center alignment (horizontally + vertically)
- Responsive padding (16px mobile, 0 desktop)
- RTL: supports dir="rtl" directionality

**Example Usage:**

```vue
<template>
  <AuthLayout>
    <AuthCard title="تسجيل الدخول">
      <!-- Form content -->
    </AuthCard>
  </AuthLayout>
</template>
```

---

### AuthCard Component

**File:** `frontend/components/auth/AuthCard.vue`

**Purpose:** Reusable card container with design system styling.

**Props:**

```typescript
interface AuthCardProps {
  title?: string; // Heading (optional)
  subtitle?: string; // Description (optional)
  class?: string; // Additional CSS classes
}
```

**Slots:**

```
default: Form/content
```

**Styling:**

- Max-width: 400px on desktop, full-width on mobile
- Shadow-as-border: `0px 0px 0px 1px rgba(0,0,0,0.08)`
- Padding: 24px (desktop), 16px (mobile)
- Border-radius: 6px
- Background: white

**Example Usage:**

```vue
<template>
  <AuthCard title="تسجيل الدخول">
    <form @submit="onSubmit">
      <!-- Form fields -->
    </form>
  </AuthCard>
</template>
```

---

### PasswordStrength Component

**File:** `frontend/components/auth/PasswordStrength.vue`

**Purpose:** Real-time password strength indicator.

**Props:**

```typescript
interface PasswordStrengthProps {
  password: string;
  showLabel?: boolean; // Default: true
}
```

**Output State:**

```typescript
// Computed internally
strength: "weak" | "fair" | "good" | "strong";
percentage: 0 - 100;
```

**Strength Calculation:**

- Weak: < 8 chars or < 25 score
- Fair: 8+ chars, no complexity
- Good: 8+ chars, mixed case + number
- Strong: 8+ chars, mixed case + number + special char

**Example Usage:**

```vue
<template>
  <PasswordStrength :password="formValues.password" />
</template>
```

---

### RoleSelector Component

**File:** `frontend/components/auth/RoleSelector.vue`

**Purpose:** Account type selector (Customer vs Contractor).

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

**Options:**

- Customer (عميل): Hiring/commissioning projects
- Contractor (مقاول): Executing projects

**Example Usage:**

```vue
<template>
  <RoleSelector v-model="accountType" @update:modelValue="onRoleChange" />
</template>
```

---

### OtpInput Component

**File:** `frontend/components/auth/OtpInput.vue`

**Purpose:** PIN/OTP input wrapper (for future 2FA).

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

**Behavior:**

- Auto-focus between input fields
- Numeric input only
- Emit 'complete' when all digits entered
- Optional auto-submit

**Example Usage:**

```vue
<template>
  <OtpInput v-model="otp" :length="6" @complete="onOtpComplete" />
</template>
```

---

## Summary

This contract document specifies:

✅ **API endpoints:** All 7 endpoints with request/response examples
✅ **Error handling:** Standardized error responses with field-level errors
✅ **Rate limiting:** Per-endpoint limits to prevent abuse
✅ **Component interfaces:** Props, events, styling for all shared components
✅ **Example usage:** Code snippets for developers
✅ **Validation rules:** Both client and server-side requirements

All API responses follow StandardErrorResponse contract with:

- `success` (boolean)
- `data` (object or null)
- `message` (Arabic text)
- `errors` (field-level errors object)

All components are RTL-aware and styled per DESIGN.md specification.
