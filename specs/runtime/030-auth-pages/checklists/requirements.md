# Auth Pages — Requirements Checklist

Use this checklist during development to ensure all requirements are met.

---

## Pages Implementation

### Login Page (/auth/login)

- [ ] Page accessible at `/auth/login`
- [ ] AuthLayout wrapper applied with RTL support
- [ ] AuthCard container with shadow-as-border styling
- [ ] Email input field
  - [ ] UFormField label: "البريد الإلكتروني"
  - [ ] UInput type="email"
  - [ ] Zod validation: email format
  - [ ] Error message in Arabic displayed below field
  - [ ] Arabic placeholder text
- [ ] Password input field
  - [ ] UFormField label: "كلمة المرور"
  - [ ] UInput type="password"
  - [ ] Show/hide toggle button (eye icon)
  - [ ] Zod validation: min 8 characters
  - [ ] Error message in Arabic displayed below field
  - [ ] Arabic placeholder text
- [ ] "Remember me" checkbox
  - [ ] UCheckbox component
  - [ ] Label: "تذكرني"
  - [ ] Optional (not required)
- [ ] Submit button
  - [ ] UButton with block layout
  - [ ] Text: "تسجيل الدخول"
  - [ ] Loading state during API call
  - [ ] Disabled while loading
- [ ] Additional elements
  - [ ] "Forgot password?" link → `/auth/forgot-password`
  - [ ] "Create account" link → `/auth/register`
  - [ ] Social login buttons (Google, Apple) — design placeholders, no backend
- [ ] Error handling
  - [ ] Invalid credentials → UAlert with Arabic error message
  - [ ] Network error → UAlert with Arabic error message
- [ ] Form validation
  - [ ] Real-time validation on blur
  - [ ] Red border on error fields
  - [ ] Submit disabled if form invalid
- [ ] Behavior after success
  - [ ] Token stored in localStorage
  - [ ] User data stored in useAuthStore
  - [ ] Redirect to `/dashboard`
- [ ] RTL verification
  - [ ] `dir="rtl"` on html element
  - [ ] Form inputs right-aligned
  - [ ] Error messages in Arabic, right-to-left text direction
- [ ] Design system compliance
  - [ ] Geist font applied (400 body weight)
  - [ ] Achromatic color palette (white background, gray text)
  - [ ] Shadow-as-border on card
  - [ ] 6px border-radius
- [ ] Accessibility
  - [ ] Form labels have `for` attributes
  - [ ] Focus ring visible on inputs
  - [ ] Keyboard navigation works (Tab through form)
  - [ ] Error role="alert" for screen readers
- [ ] Testing
  - [ ] Valid credentials test: redirect to dashboard
  - [ ] Invalid credentials test: error message displayed
  - [ ] RTL layout test: verify right-aligned inputs

---

### Register Page (/auth/register)

#### General Setup

- [ ] Page accessible at `/auth/register`
- [ ] AuthLayout wrapper applied
- [ ] AuthCard container
- [ ] USteppers component showing progress (e.g., "Step 1 of 4")
- [ ] Back/Next buttons with conditional display
- [ ] Multi-step form state persisted in Pinia store

#### Step 1: Account Type Selection

- [ ] Heading: "اختر نوع الحساب" (Choose Account Type)
- [ ] URadioGroup with two options
  - [ ] Option 1: "عميل" (Customer)
    - [ ] Description: "إنشاء مشاريع وإدارة الميزانية"
    - [ ] Value: "customer"
  - [ ] Option 2: "مقاول" (Contractor)
    - [ ] Description: "تنفيذ المشاريع والعمل مع فريق"
    - [ ] Value: "contractor"
- [ ] Zod validation: accountType required
- [ ] Error message in Arabic if empty on Next
- [ ] Next button advances to Step 2
- [ ] Back button (if on step > 1) goes to previous step

#### Step 2: Personal Information

- [ ] Heading: "المعلومات الشخصية" (Personal Information)
- [ ] First Name input
  - [ ] UFormField label: "الاسم الأول"
  - [ ] UInput type="text"
  - [ ] Zod validation: min 2, max 50 characters
  - [ ] Error message in Arabic
  - [ ] Arabic placeholder text
- [ ] Last Name input
  - [ ] UFormField label: "الاسم الأخير"
  - [ ] UInput type="text"
  - [ ] Zod validation: min 2, max 50 characters
  - [ ] Error message in Arabic
  - [ ] Arabic placeholder text
- [ ] Email input
  - [ ] UFormField label: "البريد الإلكتروني"
  - [ ] UInput type="email"
  - [ ] Zod validation: email format
  - [ ] Error message in Arabic
  - [ ] Arabic placeholder text
- [ ] Step 2 validation
  - [ ] All fields required
  - [ ] Email must be valid format
  - [ ] Error messages displayed below fields
- [ ] Back button returns to Step 1 (preserves Step 1 selection)
- [ ] Next button advances to Step 3

#### Step 3: Contact Information

- [ ] Heading: "معلومات الاتصال" (Contact Information)
- [ ] Phone input
  - [ ] UFormField label: "رقم الهاتف"
  - [ ] UInput type="tel"
  - [ ] Zod validation: regex `/^[+0-9]{7,}$/`
  - [ ] Error message: "رقم الهاتف غير صالح"
  - [ ] Placeholder: "+20xxxxxxxxx"
- [ ] Country selector
  - [ ] UFormField label: "الدولة"
  - [ ] USelect/dropdown with list of countries
  - [ ] Arabic country names (e.g., "مصر" for Egypt)
  - [ ] Zod validation: country required
  - [ ] Error message: "يجب تحديد البلد"
- [ ] Password input
  - [ ] UFormField label: "كلمة المرور"
  - [ ] UInput type="password"
  - [ ] Show/hide toggle
  - [ ] Zod validation: min 8 characters
  - [ ] Error message in Arabic
- [ ] Confirm Password input
  - [ ] UFormField label: "تأكيد كلمة المرور"
  - [ ] UInput type="password"
  - [ ] Show/hide toggle
  - [ ] Zod validation: matches password field
  - [ ] Error message: "كلمات المرور غير متطابقة"
- [ ] PasswordStrength component
  - [ ] Displays below password field
  - [ ] Shows strength: "ضعيف" → "قوي"
  - [ ] UProgress bar changes color (red → yellow → green)
  - [ ] Updates in real-time as user types
- [ ] Back button returns to Step 2
- [ ] Submit button (instead of Next)
  - [ ] Text: "إنشاء الحساب"
  - [ ] Submits registration data to `/api/v1/register`
  - [ ] Loading state during API call

#### Step 4: Email Verification

- [ ] Heading: "تحقق من بريدك الإلكتروني" (Verify Your Email)
- [ ] Message text: "تم إرسال رابط التحقق إلى [email address]"
- [ ] Display email address used during registration
- [ ] USteppers shows "Step 4 of 4"
- [ ] "Resend" button
  - [ ] Sends POST to `/api/v1/resend-verification-email`
  - [ ] Disabled for 60 seconds after click
  - [ ] Shows countdown timer
  - [ ] Displays success message after resend
- [ ] Auto-redirect to `/dashboard` after email verified
  - [ ] Backend webhook triggers verification
  - [ ] Frontend detects verification and redirects
- [ ] "Back to login" link if user wants to exit
- [ ] No form input fields on this step

#### Multi-Step General Requirements

- [ ] All form text in Arabic (using i18n)
- [ ] RTL layout verified (inputs right-aligned, text direction correct)
- [ ] Step progress indicator accurate
- [ ] Back button preserves previous step data
- [ ] Form data persisted in store (survives page refresh)
- [ ] Accessibility: keyboard navigation through steps
- [ ] Accessibility: focus ring visible on inputs
- [ ] Error messages displayed inline (below fields)

---

### Forgot Password Page (/auth/forgot-password)

- [ ] Page accessible at `/auth/forgot-password`
- [ ] AuthLayout wrapper
- [ ] AuthCard container
- [ ] Heading: "إعادة تعيين كلمة المرور" (Reset Password)
- [ ] Email input field
  - [ ] UFormField label: "البريد الإلكتروني"
  - [ ] UInput type="email"
  - [ ] Zod validation: email format
  - [ ] Error message in Arabic
  - [ ] Arabic placeholder text
- [ ] Submit button
  - [ ] Text: "إرسال رابط إعادة التعيين"
  - [ ] Submits POST to `/api/v1/forgot-password`
  - [ ] Loading state during API call
- [ ] Success handling
  - [ ] UAlert displays success message: "تحقق من بريدك الإلكتروني للحصول على رابط إعادة التعيين"
  - [ ] Message displayed for at least 5 seconds
- [ ] Error handling
  - [ ] Invalid email → error UAlert in Arabic
  - [ ] User not found → error UAlert in Arabic (generic message for security)
  - [ ] Network error → error UAlert in Arabic
- [ ] Links
  - [ ] "Back to login" link → `/auth/login`
- [ ] RTL verification
  - [ ] Form inputs right-aligned
  - [ ] Error/success messages in Arabic with correct text direction
- [ ] Design system compliance
  - [ ] Geist font, achromatic colors, shadow-as-border
- [ ] Accessibility
  - [ ] Form labels linked to inputs
  - [ ] Focus ring on input
  - [ ] Alert role for success/error messages
- [ ] Testing
  - [ ] Valid email → success message
  - [ ] Invalid email → error message
  - [ ] RTL layout test

---

### Reset Password Page (/auth/reset-password?token=<token>)

- [ ] Page accessible at `/auth/reset-password?token=<token>`
- [ ] Token extraction
  - [ ] URL query parameter parsed: `?token=abc123`
  - [ ] Token validated on page load
  - [ ] POST to `/api/v1/validate-reset-token` to verify
- [ ] Token validation states
  - [ ] **Valid token:** Display reset form
  - [ ] **Invalid/expired token:** Display error page with message: "انتهت صلاحية الرابط" and link to `/auth/forgot-password`
- [ ] AuthLayout wrapper
- [ ] AuthCard container
- [ ] Heading: "تعيين كلمة مرور جديدة" (Set New Password)
- [ ] Password input field
  - [ ] UFormField label: "كلمة المرور الجديدة"
  - [ ] UInput type="password"
  - [ ] Show/hide toggle
  - [ ] Zod validation: min 8 characters
  - [ ] Error message in Arabic
  - [ ] Arabic placeholder text
- [ ] Confirm Password input
  - [ ] UFormField label: "تأكيد كلمة المرور"
  - [ ] UInput type="password"
  - [ ] Show/hide toggle
  - [ ] Zod validation: matches password field
  - [ ] Error message: "كلمات المرور غير متطابقة"
- [ ] PasswordStrength component
  - [ ] Displays below password field
  - [ ] Updates in real-time
- [ ] Submit button
  - [ ] Text: "تحديث كلمة المرور"
  - [ ] Submits POST to `/api/v1/reset-password` with token + new password
  - [ ] Loading state
- [ ] Success handling
  - [ ] Redirect to `/auth/login`
  - [ ] Display message: "تم تحديث كلمة المرور بنجاح"
- [ ] Error handling
  - [ ] Token expired → error message + link to forgot-password
  - [ ] Invalid token → error message + link to forgot-password
  - [ ] API error → error UAlert in Arabic
- [ ] Links
  - [ ] "Back to login" link → `/auth/login`
- [ ] RTL verification
- [ ] Design system compliance
- [ ] Accessibility

---

### Email Verification Page (/auth/verify-email?token=<token>)

- [ ] Page accessible at `/auth/verify-email?token=<token>`
- [ ] Token extraction from URL parameter
- [ ] On page load: POST to `/api/v1/verify-email` with token
- [ ] Loading state
  - [ ] Display spinner
  - [ ] Message: "يتم التحقق من بريدك الإلكتروني..."
- [ ] Success state
  - [ ] Heading: "تم التحقق بنجاح" (Verification Successful)
  - [ ] Message: "تم تأكيد بريدك الإلكتروني بنجاح"
  - [ ] UButton: "متابعة إلى لوحة التحكم" (Continue to Dashboard)
  - [ ] Auto-redirect to `/dashboard` after 3 seconds
- [ ] Error state
  - [ ] Heading: "فشل التحقق" (Verification Failed)
  - [ ] Message: "انتهت صلاحية الرابط أو أنه غير صالح"
  - [ ] UButton: "إعادة محاولة" (Retry)
  - [ ] Link to request new verification email
- [ ] AuthLayout wrapper
- [ ] AuthCard container
- [ ] RTL verification
- [ ] Design system compliance
- [ ] Testing
  - [ ] Valid token → success + redirect
  - [ ] Invalid token → error message + resend option
  - [ ] Expired token → error message

---

### Profile Page (/profile)

- [ ] Page accessible at `/profile`
- [ ] Protected by `middleware/auth.ts` (redirect unauthenticated users to `/auth/login`)
- [ ] Standard app layout (not AuthLayout)
- [ ] Page heading: "الملف الشخصي" (Profile)
- [ ] Form fields pre-populated from user store
  - [ ] First Name
    - [ ] UFormField label: "الاسم الأول"
    - [ ] UInput type="text"
    - [ ] Pre-filled with user.firstName
    - [ ] Zod validation: min 2, max 50
  - [ ] Last Name
    - [ ] UFormField label: "الاسم الأخير"
    - [ ] UInput type="text"
    - [ ] Pre-filled with user.lastName
    - [ ] Zod validation: min 2, max 50
  - [ ] Email (read-only or separate change-email flow)
    - [ ] UFormField label: "البريد الإلكتروني"
    - [ ] UInput type="email" disabled
    - [ ] Pre-filled with user.email
  - [ ] Phone
    - [ ] UFormField label: "رقم الهاتف"
    - [ ] UInput type="tel"
    - [ ] Pre-filled with user.phone
    - [ ] Zod validation: regex `/^[+0-9]{7,}$/`
  - [ ] Country
    - [ ] UFormField label: "الدولة"
    - [ ] USelect/dropdown
    - [ ] Pre-selected with user.country
    - [ ] Zod validation: country required
- [ ] Form validation
  - [ ] Zod schema (profileSchema) validates on submit
  - [ ] Real-time validation on blur
  - [ ] Error messages in Arabic below fields
- [ ] Action buttons
  - [ ] Save button: "حفظ التغييرات"
    - [ ] Submits PUT to `/api/v1/profile`
    - [ ] Loading state
  - [ ] Cancel button: "إلغاء"
    - [ ] Reverts form to original state
- [ ] Success handling
  - [ ] UAlert displays success message: "تم حفظ التغييرات بنجاح"
  - [ ] User store updated with new profile data
- [ ] Error handling
  - [ ] API error → UAlert with error message in Arabic
  - [ ] Validation errors → displayed below fields
- [ ] RTL verification
  - [ ] Form inputs right-aligned in RTL mode
  - [ ] Error messages in Arabic
- [ ] Design system compliance
  - [ ] Geist font, achromatic colors, shadow-as-border
- [ ] Accessibility
  - [ ] Form labels linked to inputs
  - [ ] Focus ring on inputs
  - [ ] Error role="alert"
- [ ] Testing
  - [ ] Protected route: unauthenticated redirect
  - [ ] Edit and save profile → success message
  - [ ] Validation error → error message
  - [ ] RTL layout test

---

## Shared Components

### AuthLayout.vue

- [ ] Component exists: `frontend/components/auth/AuthLayout.vue`
- [ ] Wrapper element: `<div>` with full viewport height
- [ ] Background color: white
- [ ] Responsive layout
  - [ ] Mobile (<768px): 16px horizontal padding
  - [ ] Desktop (>768px): centered, no padding
- [ ] Flexbox centering: center both horizontally and vertically
- [ ] RTL support: layout adapts for dir="rtl"
- [ ] Slot for page content
- [ ] Min-height: 100vh

### AuthCard.vue

- [ ] Component exists: `frontend/components/auth/AuthCard.vue`
- [ ] Props:
  - [ ] `title` (optional, string) — Heading text
  - [ ] `subtitle` (optional, string) — Description text
  - [ ] `class` (optional, string) — Additional CSS classes
- [ ] Background: white
- [ ] Max-width: 400px (desktop)
- [ ] Shadow-as-border: `box-shadow: 0px 0px 0px 1px rgba(0,0,0,0.08)`
- [ ] Padding: 24px (desktop), 16px (mobile)
- [ ] Border-radius: 6px
- [ ] Title (if provided)
  - [ ] Heading element (h1 or h2)
  - [ ] Font: Geist, 600 weight
  - [ ] Font-size: 24px
  - [ ] Margin-bottom: 8px
- [ ] Subtitle (if provided)
  - [ ] Paragraph element
  - [ ] Font: Geist, 400 weight
  - [ ] Font-size: 16px
  - [ ] Color: gray-600
  - [ ] Margin-bottom: 24px
- [ ] Slot for content
- [ ] RTL support

### PasswordStrength.vue

- [ ] Component exists: `frontend/components/auth/PasswordStrength.vue`
- [ ] Props:
  - [ ] `password` (string) — Current password value
  - [ ] `showLabel` (boolean, default: true) — Show strength label
- [ ] Password analysis
  - [ ] Length (minimum 8 characters)
  - [ ] Uppercase letters (A-Z)
  - [ ] Lowercase letters (a-z)
  - [ ] Numbers (0-9)
  - [ ] Special characters (!@#$%^&\*)
- [ ] Strength levels
  - [ ] 0-1 points: "ضعيف" (Weak) — red
  - [ ] 2 points: "متوسط" (Fair) — orange/yellow
  - [ ] 3 points: "جيد" (Good) — green
  - [ ] 4+ points: "قوي" (Strong) — dark green
- [ ] Visual display
  - [ ] UProgress component (0-100%)
  - [ ] Color changes with strength
  - [ ] Optional label text: "قوة كلمة المرور"
- [ ] Real-time updates as user types
- [ ] No external API calls

### RoleSelector.vue

- [ ] Component exists: `frontend/components/auth/RoleSelector.vue`
- [ ] Props:
  - [ ] `modelValue` (string) — Selected role ("customer" or "contractor")
  - [ ] `disabled` (boolean, default: false) — Disable selection
- [ ] Emits:
  - [ ] `update:modelValue` — When selection changes
- [ ] Display
  - [ ] URadioGroup component
  - [ ] Two options:
    - [ ] "عميل" (Customer)
    - [ ] "مقاول" (Contractor)
  - [ ] Optional descriptions for each role
  - [ ] Visual feedback for selected option
- [ ] Accessibility
  - [ ] Proper label associations
  - [ ] Keyboard navigation (arrow keys)

### OtpInput.vue

- [ ] Component exists: `frontend/components/auth/OtpInput.vue`
- [ ] Props:
  - [ ] `modelValue` (string) — Current OTP value
  - [ ] `length` (number, default: 6) — Number of OTP digits
  - [ ] `disabled` (boolean, default: false)
- [ ] Emits:
  - [ ] `update:modelValue` — When OTP changes
  - [ ] `complete` — When all digits entered
- [ ] Display
  - [ ] UPinInput component from Nuxt UI
  - [ ] Individual input fields for each digit
  - [ ] Numeric input only
  - [ ] Auto-focus to next field on digit entry
- [ ] Behavior
  - [ ] Auto-focus between fields
  - [ ] Backspace deletes previous digit
  - [ ] Copy/paste OTP (if paste includes full OTP)
- [ ] RTL support (if needed for future use)

---

## Form Validation (Zod Schemas)

### File: `frontend/schemas/auth.ts`

#### loginSchema

- [ ] Exports `loginSchema` as default export
- [ ] Fields:
  - [ ] `email` (string)
    - [ ] Required: "البريد الإلكتروني مطلوب"
    - [ ] Email format: "البريد الإلكتروني غير صالح"
  - [ ] `password` (string)
    - [ ] Required: "كلمة المرور مطلوبة"
    - [ ] Min 8: "كلمة المرور يجب أن تكون 8 أحرف على الأقل"
  - [ ] `rememberMe` (boolean, optional)

#### registerStep1Schema

- [ ] Exports `registerStep1Schema`
- [ ] Fields:
  - [ ] `accountType` (enum: "customer", "contractor")
    - [ ] Required: "يجب تحديد نوع الحساب"

#### registerStep2Schema

- [ ] Exports `registerStep2Schema`
- [ ] Fields:
  - [ ] `firstName` (string)
    - [ ] Required
    - [ ] Min 2: "الاسم الأول قصير جدًا"
    - [ ] Max 50: "الاسم الأول طويل جدًا"
  - [ ] `lastName` (string)
    - [ ] Required
    - [ ] Min 2: "الاسم الأخير قصير جدًا"
    - [ ] Max 50: "الاسم الأخير طويل جدًا"
  - [ ] `email` (string)
    - [ ] Required
    - [ ] Email format: "البريد الإلكتروني غير صالح"

#### registerStep3Schema

- [ ] Exports `registerStep3Schema`
- [ ] Fields:
  - [ ] `phone` (string)
    - [ ] Required
    - [ ] Regex: `/^[+0-9]{7,}$/`
    - [ ] Error: "رقم الهاتف غير صالح"
  - [ ] `country` (string)
    - [ ] Required: "يجب تحديد البلد"
  - [ ] `password` (string)
    - [ ] Required
    - [ ] Min 8: "كلمة المرور يجب أن تكون 8 أحرف على الأقل"
  - [ ] `confirmPassword` (string)
    - [ ] Required
  - [ ] **Refinement**: password === confirmPassword
    - [ ] Error message: "كلمات المرور غير متطابقة"
    - [ ] Path: ["confirmPassword"]

#### resetPasswordSchema

- [ ] Exports `resetPasswordSchema`
- [ ] Fields:
  - [ ] `password` (string)
    - [ ] Required
    - [ ] Min 8: "كلمة المرور يجب أن تكون 8 أحرف على الأقل"
  - [ ] `confirmPassword` (string)
    - [ ] Required
  - [ ] **Refinement**: password === confirmPassword
    - [ ] Error: "كلمات المرور غير متطابقة"

#### profileSchema

- [ ] Exports `profileSchema`
- [ ] Fields:
  - [ ] `firstName` (string)
    - [ ] Required
    - [ ] Min 2, Max 50 with Arabic messages
  - [ ] `lastName` (string)
    - [ ] Required
    - [ ] Min 2, Max 50 with Arabic messages
  - [ ] `email` (string)
    - [ ] Required
    - [ ] Email format validation
  - [ ] `phone` (string)
    - [ ] Required
    - [ ] Regex: `/^[+0-9]{7,}$/`
  - [ ] `country` (string)
    - [ ] Required

---

## Pinia Store (State Management)

### Store: `frontend/stores/auth.ts`

#### State

- [ ] `user` (ref)
  - [ ] Stores user object: `{ id, email, firstName, lastName, role }`
  - [ ] Initially: null
  - [ ] Hydrated on login/register
  - [ ] Cleared on logout
- [ ] `token` (ref)
  - [ ] Stores JWT token
  - [ ] Loaded from localStorage on app init
  - [ ] Persisted to localStorage on login
  - [ ] Cleared from localStorage on logout
- [ ] `isLoading` (ref)
  - [ ] Boolean flag for API call loading state
  - [ ] Used by components to show spinners
- [ ] `error` (ref)
  - [ ] Stores error message (if any)
  - [ ] Cleared on successful action

#### Computed Properties

- [ ] `isAuthenticated`
  - [ ] Returns: `!!token.value`
  - [ ] Used by middleware and components

#### Actions

- [ ] `login(email, password)`
  - [ ] Sets `isLoading` to true
  - [ ] Calls `/api/v1/login` via composable
  - [ ] On success:
    - [ ] Stores token in state + localStorage
    - [ ] Stores user in state
    - [ ] Clears error
    - [ ] Sets isLoading to false
  - [ ] On error:
    - [ ] Sets error message
    - [ ] Sets isLoading to false
    - [ ] Throws error (for components to handle)
- [ ] `logout()`
  - [ ] Clears token from state
  - [ ] Clears user from state
  - [ ] Removes token from localStorage
  - [ ] Clears error
  - [ ] Optional: Call `/api/v1/logout` endpoint
- [ ] `register(data)`
  - [ ] Sets `isLoading` to true
  - [ ] Calls `/api/v1/register` with registration data
  - [ ] On success:
    - [ ] Returns success (does NOT auto-login)
    - [ ] Clears error
    - [ ] Sets isLoading to false
  - [ ] On error:
    - [ ] Sets error message
    - [ ] Sets isLoading to false
    - [ ] Throws error
- [ ] `refreshToken()`
  - [ ] Optional (for refresh token flow)
  - [ ] Calls `/api/v1/refresh`
  - [ ] Updates token if backend supports refresh

### Store: `frontend/stores/user.ts`

#### State

- [ ] `profile` (ref)
  - [ ] Stores full user profile: `{ firstName, lastName, email, phone, country, roles }`
  - [ ] Initially: null
  - [ ] Hydrated on profile fetch or login
- [ ] `isLoading` (ref)
  - [ ] Boolean flag for API call loading state
- [ ] `error` (ref)
  - [ ] Stores error message (if any)

#### Actions

- [ ] `fetchProfile()`
  - [ ] Sets `isLoading` to true
  - [ ] Calls `/api/v1/profile` to fetch current user profile
  - [ ] On success:
    - [ ] Stores profile in state
    - [ ] Clears error
    - [ ] Sets isLoading to false
  - [ ] On error:
    - [ ] Sets error message
    - [ ] Sets isLoading to false
- [ ] `updateProfile(data)`
  - [ ] Sets `isLoading` to true
  - [ ] Calls `/api/v1/profile` with PUT method
  - [ ] On success:
    - [ ] Updates profile with new data
    - [ ] Clears error
    - [ ] Sets isLoading to false
    - [ ] Returns success response
  - [ ] On error:
    - [ ] Sets error message
    - [ ] Sets isLoading to false
    - [ ] Throws error

---

## API Composable

### File: `frontend/composables/useAuthApi.ts`

- [ ] Function: `login(email, password)`
  - [ ] Endpoint: POST `/api/v1/login`
  - [ ] Request body: `{ email, password }`
  - [ ] Returns: `{ token, user }`
  - [ ] Error handling: throws StandardErrorResponse

- [ ] Function: `register(data)`
  - [ ] Endpoint: POST `/api/v1/register`
  - [ ] Request body: registration data (account type, personal info, contact info)
  - [ ] Returns: `{ token?, user?, verification_pending }`
  - [ ] Error handling: throws StandardErrorResponse

- [ ] Function: `forgotPassword(email)`
  - [ ] Endpoint: POST `/api/v1/forgot-password`
  - [ ] Request body: `{ email }`
  - [ ] Returns: `{ message }`
  - [ ] Error handling: throws StandardErrorResponse

- [ ] Function: `resetPassword(token, password)`
  - [ ] Endpoint: POST `/api/v1/reset-password`
  - [ ] Request body: `{ token, password }`
  - [ ] Returns: `{ message }`
  - [ ] Error handling: throws StandardErrorResponse

- [ ] Function: `verifyEmail(token)`
  - [ ] Endpoint: POST `/api/v1/verify-email`
  - [ ] Request body: `{ token }`
  - [ ] Returns: `{ message }`
  - [ ] Error handling: throws StandardErrorResponse

- [ ] Function: `getProfile()`
  - [ ] Endpoint: GET `/api/v1/profile`
  - [ ] No request body
  - [ ] Returns: profile object
  - [ ] Error handling: throws StandardErrorResponse

- [ ] Function: `updateProfile(data)`
  - [ ] Endpoint: PUT `/api/v1/profile`
  - [ ] Request body: profile update data
  - [ ] Returns: updated profile object
  - [ ] Error handling: throws StandardErrorResponse

- [ ] Function: `resendVerificationEmail(email)`
  - [ ] Endpoint: POST `/api/v1/resend-verification-email`
  - [ ] Request body: `{ email }`
  - [ ] Returns: `{ message }`
  - [ ] Error handling: throws StandardErrorResponse

- [ ] Error handling
  - [ ] StandardErrorResponse contract enforced
  - [ ] Errors thrown to components for display
  - [ ] Network errors caught and formatted

---

## Auth Middleware

### File: `frontend/middleware/auth.ts`

- [ ] Middleware name: "auth"
- [ ] Function signature: `defineRouteMiddleware((to, from) => { ... })`
- [ ] Logic:
  - [ ] Get `useAuthStore()`
  - [ ] Check `authStore.isAuthenticated`
  - [ ] If not authenticated: redirect to `/auth/login`
  - [ ] If authenticated: allow navigation
- [ ] Usage:
  - [ ] Applied to `/profile` route in `frontend/pages/profile/index.vue`

---

## Unit Tests (Vitest)

### Test File: `frontend/tests/unit/schemas/auth.spec.ts`

- [ ] Test suite: "Auth Schemas"
- [ ] Test group: "loginSchema"
  - [ ] Test: valid email + password
    - [ ] Input: `{ email: 'user@example.com', password: 'password123' }`
    - [ ] Expected: `success = true`
  - [ ] Test: invalid email
    - [ ] Input: `{ email: 'invalid', password: 'password123' }`
    - [ ] Expected: `success = false`, error message = "البريد الإلكتروني غير صالح"
  - [ ] Test: short password
    - [ ] Input: `{ email: 'user@example.com', password: 'pass' }`
    - [ ] Expected: `success = false`, error contains "أحرف على الأقل"
- [ ] Test group: "registerStep3Schema"
  - [ ] Test: valid data (password match)
    - [ ] Expected: success
  - [ ] Test: mismatched passwords
    - [ ] Expected: error message = "كلمات المرور غير متطابقة"

### Test File: `frontend/tests/unit/stores/auth.spec.ts`

- [ ] Test setup: `beforeEach(() => { setActivePinia(createPinia()); })`
- [ ] Test: initialization
  - [ ] `useAuthStore()` initializes with null user, no token
- [ ] Test: login action
  - [ ] Mock API call
  - [ ] Call `store.login('user@example.com', 'password123')`
  - [ ] Assert: token stored, user stored, localStorage updated
- [ ] Test: logout action
  - [ ] Set token + user
  - [ ] Call `store.logout()`
  - [ ] Assert: token cleared, user cleared, localStorage cleared
- [ ] Test: isAuthenticated computed
  - [ ] When token exists: returns true
  - [ ] When token is null: returns false
- [ ] Test: error handling
  - [ ] Mock failed API call
  - [ ] Assert: error state set, user not authenticated

### Test File: `frontend/tests/unit/stores/user.spec.ts`

- [ ] Test: fetchProfile action
  - [ ] Mock API call
  - [ ] Call `store.fetchProfile()`
  - [ ] Assert: profile stored
- [ ] Test: updateProfile action
  - [ ] Mock API call
  - [ ] Call `store.updateProfile(data)`
  - [ ] Assert: profile updated

---

## E2E Tests (Playwright)

### Test File: `frontend/tests/e2e/auth.spec.ts`

#### Login Flow Tests

- [ ] Test: "login with valid credentials redirects to dashboard"
  - [ ] Navigate to `/auth/login`
  - [ ] Fill email + password
  - [ ] Click submit
  - [ ] Assert: redirect to `/dashboard`
  - [ ] Assert: token in localStorage
- [ ] Test: "login shows Arabic error on invalid credentials"
  - [ ] Navigate to `/auth/login`
  - [ ] Fill invalid credentials
  - [ ] Click submit
  - [ ] Assert: UAlert visible with Arabic error message
- [ ] Test: "login persists user on page reload"
  - [ ] Login successfully
  - [ ] Reload page
  - [ ] Assert: still authenticated (token in localStorage)

#### Registration Flow Tests

- [ ] Test: "complete multi-step registration"
  - [ ] Step 1: Select account type → click Next
  - [ ] Step 2: Fill personal info → click Next
  - [ ] Step 3: Fill contact info + password → click Submit
  - [ ] Assert: Step 4 displayed with verification message
- [ ] Test: "registration validation prevents empty fields"
  - [ ] Try to advance without filling required fields
  - [ ] Assert: error messages displayed
- [ ] Test: "password confirmation validation"
  - [ ] Enter mismatched passwords on Step 3
  - [ ] Assert: error message "كلمات المرور غير متطابقة"

#### Password Reset Flow Tests

- [ ] Test: "forgot password flow"
  - [ ] Navigate to `/auth/forgot-password`
  - [ ] Fill email
  - [ ] Submit
  - [ ] Assert: success message displayed
- [ ] Test: "reset password with valid token"
  - [ ] Navigate to `/auth/reset-password?token=valid-token`
  - [ ] Fill new password + confirm
  - [ ] Submit
  - [ ] Assert: redirect to `/auth/login` with success message
- [ ] Test: "reset password with expired token"
  - [ ] Navigate with invalid token
  - [ ] Assert: error page displayed with "Retry" link

#### RTL Layout Tests

- [ ] Test: "login form renders in RTL"
  - [ ] Locale: Arabic
  - [ ] Navigate to `/auth/login`
  - [ ] Assert: `<html dir="rtl">`
  - [ ] Assert: form inputs right-aligned
  - [ ] Assert: error messages in Arabic
- [ ] Test: "multi-step form RTL layout"
  - [ ] Locale: Arabic
  - [ ] Navigate to `/auth/register`
  - [ ] Assert: all form elements right-aligned
- [ ] Test: "profile form RTL layout"
  - [ ] Locale: Arabic
  - [ ] Navigate to `/profile` (authenticated)
  - [ ] Assert: form inputs right-aligned, labels in Arabic

#### Protected Route Tests

- [ ] Test: "unauthenticated user redirected from /profile"
  - [ ] Clear localStorage (remove token)
  - [ ] Navigate to `/profile`
  - [ ] Assert: redirect to `/auth/login`
- [ ] Test: "authenticated user can access /profile"
  - [ ] Login first
  - [ ] Navigate to `/profile`
  - [ ] Assert: page loaded, form pre-filled

#### Profile Management Tests

- [ ] Test: "edit and save profile"
  - [ ] Navigate to `/profile` (authenticated)
  - [ ] Edit form fields
  - [ ] Click Save
  - [ ] Assert: success message displayed
  - [ ] Assert: data persisted (page reload shows updated values)
- [ ] Test: "profile validation errors"
  - [ ] Fill invalid phone number
  - [ ] Click Save
  - [ ] Assert: error message displayed

---

## Design System Compliance

### Typography Verification

- [ ] Geist Sans font imported
  - [ ] `@import url('https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&display=swap');`
  - [ ] Applied to `body, html, *`
- [ ] Geist Mono (optional for future labels)
  - [ ] Imported
  - [ ] Applied to code/technical labels
- [ ] Font weights used correctly
  - [ ] 400: Body text, descriptions
  - [ ] 500: UI labels, buttons, active states
  - [ ] 600: Headings, strong text
- [ ] Letter-spacing
  - [ ] Display sizes (48px): -2.4px to -2.88px
  - [ ] Large headings (32px): -1.28px
  - [ ] Card titles (24px): -0.96px
  - [ ] Normal text: normal (0)
- [ ] Ligatures enabled
  - [ ] `font-feature-settings: "liga";` applied globally
- [ ] Line heights
  - [ ] Display/headings: 1.00–1.25 (tight)
  - [ ] Body text: 1.50–1.80 (relaxed)

### Color Verification

- [ ] White background (`#ffffff`)
  - [ ] Used for page backgrounds, card surfaces
- [ ] Vercel Black (`#171717`)
  - [ ] Used for primary text, headings
- [ ] Neutral grays
  - [ ] Gray 900: `#171717` (primary text)
  - [ ] Gray 600: `#4d4d4d` (secondary text)
  - [ ] Gray 100: `#ebebeb` (borders, dividers)
- [ ] No custom colors outside palette
  - [ ] Color palette validated
- [ ] Error color (red)
  - [ ] Used for validation errors
- [ ] Success color (green)
  - [ ] Used for success messages
- [ ] Focus color (Focus Blue)
  - [ ] `hsla(212, 100%, 48%, 1)` on interactive elements

### Shadows & Borders Verification

- [ ] Shadow-as-border implemented
  - [ ] `box-shadow: 0px 0px 0px 1px rgba(0,0,0,0.08);` on all cards
  - [ ] No CSS `border` properties used
- [ ] Multi-layer card shadow
  - [ ] Border layer (1px) + elevation (2px) + ambient (8px)
- [ ] Border radius
  - [ ] 6px consistently used (no > 8px)

### Layout Verification

- [ ] Responsive breakpoints
  - [ ] Mobile: <768px (full width, 16px padding)
  - [ ] Tablet: 768px–1024px (max-width 600px, centered)
  - [ ] Desktop: >1024px (max-width 400-600px, centered)
- [ ] Form max-width
  - [ ] Auth forms: 400px on desktop
- [ ] Flex centering
  - [ ] Pages centered horizontally and vertically

### RTL Support Verification

- [ ] Tailwind logical properties used everywhere
  - [ ] `ms-` instead of `ml-`
  - [ ] `me-` instead of `mr-`
  - [ ] `ps-` instead of `pl-`
  - [ ] `pe-` instead of `pr-`
  - [ ] `text-start` instead of `text-left`
  - [ ] `text-end` instead of `text-right`
- [ ] No directional-specific classes in RTL contexts
  - [ ] No `ml-`, `mr-`, `pl-`, `pr-`, `text-left`, `text-right` in auth pages
- [ ] `dir="rtl"` applied on `<html>` when Arabic
  - [ ] Nuxt i18n plugin handles this automatically
- [ ] Form inputs rendered correctly in RTL
  - [ ] Right-to-left text entry
  - [ ] Labels right-aligned

---

## Accessibility (WCAG 2.1 Level AA)

- [ ] Form labels have `for` attributes
  - [ ] `<label for="email-input">` linked to `<input id="email-input">`
  - [ ] Applied to all form fields
- [ ] Error messages announced to screen readers
  - [ ] `role="alert"` on error containers
  - [ ] `aria-live="polite"` optional
- [ ] Keyboard navigation works
  - [ ] Tab key navigates through form fields
  - [ ] Shift+Tab navigates backward
  - [ ] Enter submits form
  - [ ] Arrow keys work in radio groups
- [ ] Color contrast ratio >= 4.5:1
  - [ ] Text on white background
  - [ ] Error text in red >= 4.5:1
- [ ] Focus ring visible
  - [ ] 2px solid, Focus Blue color
  - [ ] Visible on all interactive elements
  - [ ] No focus-visible removed without replacement
- [ ] Password inputs properly labeled
  - [ ] Not just placeholder text
  - [ ] Separate label element
- [ ] Buttons have accessible names
  - [ ] Text content or `aria-label`
- [ ] Icons have alt text (if any)
  - [ ] Show/hide password icon: `aria-label="Show password"` / `aria-label="Hide password"`

---

## Performance

### Bundle Size

- [ ] Auth pages bundled size < 100KB (gzipped)
  - [ ] Shared components not duplicated
  - [ ] CSS purged of unused classes
  - [ ] Tree-shake unused imports

### Page Load Time

- [ ] Initial page load < 2 seconds (3G throttling)
  - [ ] Measured via Lighthouse or similar
- [ ] Form interactive < 1 second
- [ ] API responses cached (if applicable)
  - [ ] Country list cached

### Client-Side Performance

- [ ] No console errors or warnings
- [ ] No performance-impacting JavaScript
  - [ ] Form validation debounced
  - [ ] API calls don't block UI
- [ ] Images optimized (if any)
  - [ ] Social login icons: < 5KB each

---

## Security

### Input Validation

- [ ] Client-side validation via Zod
  - [ ] Email format
  - [ ] Password length
  - [ ] Phone number format
- [ ] Server-side validation enforced (backend API)
  - [ ] Not client-only
- [ ] No XSS vulnerabilities
  - [ ] Vue escapes user input by default
  - [ ] No `v-html` used in user content

### Token Storage

- [ ] Auth token stored in localStorage
  - [ ] Only after successful login
  - [ ] Persisted across page reloads
- [ ] Token cleared on logout
  - [ ] Removed from localStorage
- [ ] Token not logged to console
  - [ ] No `console.log(token)` anywhere
- [ ] HTTPS enforced (in production)
  - [ ] Secure cookies recommended (httpOnly)

### CSRF Protection

- [ ] CSRF token sent with POST requests
  - [ ] Laravel Sanctum handles this
  - [ ] Frontend automatically includes (via Laravel API client)

### Password Security

- [ ] Password input type="password"
  - [ ] Text not visible without toggle
- [ ] Password show/hide toggle safe
  - [ ] Shows plaintext only when toggled on
- [ ] Password strength indicator
  - [ ] Visual feedback only (no external API)
- [ ] Reset links expire (backend enforced)
  - [ ] Token validation done server-side

---

## Deployment & Build

### Build Process

- [ ] `npm run build` succeeds
  - [ ] No build errors
  - [ ] Output: `.output/public/` (Nuxt production)
- [ ] `npm run lint` passes
  - [ ] No ESLint errors
  - [ ] No ESLint warnings (or justified)
- [ ] `npm run typecheck` passes
  - [ ] No TypeScript errors
- [ ] `npm run test` passes
  - [ ] All unit tests pass
  - [ ] All E2E tests pass

### Environment Variables

- [ ] `.env.example` created/updated
  - [ ] Documents all required env vars
  - [ ] Example values provided
  - [ ] No secrets in file
- [ ] API base URL configurable
  - [ ] `NUXT_PUBLIC_API_BASE_URL` env var
  - [ ] Used in fetch calls
- [ ] No hardcoded API URLs in code
- [ ] No secrets (passwords, tokens) in code

---

## Documentation

### Code Comments

- [ ] Complex business logic documented
  - [ ] Password strength algorithm explained (if custom)
  - [ ] Multi-step form state management explained
- [ ] No obvious comments
  - [ ] Comments are valuable, not narration

### README

- [ ] `frontend/README.md` includes auth pages overview
  - [ ] Links to relevant docs
  - [ ] Setup instructions (if needed)

### API Integration Docs

- [ ] Endpoints documented
  - [ ] `/api/v1/login` — request/response format
  - [ ] `/api/v1/register` — multi-step handling
  - [ ] Other endpoints
  - [ ] Error codes and messages
  - [ ] RBAC requirements (if any)

---

## Final Verification

- [ ] All pages implemented
- [ ] All components created
- [ ] All forms validate correctly
- [ ] All tests passing
- [ ] RTL layout verified
- [ ] Design system compliance verified
- [ ] Security best practices followed
- [ ] Accessibility verified
- [ ] Performance within targets
- [ ] Bundle size < 100KB
- [ ] Build succeeds
- [ ] No console errors
- [ ] Documentation complete
