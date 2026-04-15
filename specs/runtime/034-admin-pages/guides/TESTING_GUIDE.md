# Testing Guide — Admin Pages

> **Phase:** 07_FRONTEND_APPLICATION > **Generated:** 2026-04-14T14:47:30Z

## Prerequisites

```bash
# Backend
cd backend
composer install
php artisan migrate:fresh --seed

# Frontend
cd ../frontend
npm install
```

## Running Tests

### Backend Unit Tests

```bash
cd backend
php artisan test --testsuite=Unit --filter=RolePermissionTest
```

### Backend Feature Tests

```bash
cd backend
php artisan test --testsuite=Feature
```

### Frontend Tests

```bash
cd frontend
npm run test -- --filter=useAdminUsers
npm run test
```

### All Tests

```bash
cd backend
composer run lint
php artisan test

cd ../frontend
npm run lint
npm run typecheck
npm run test
```

## Manual Test Scenarios

### Scenario 1 — Admin can access admin shell

**Preconditions:**

- Have an Admin user account.
- Backend API reachable from frontend (`NUXT_PUBLIC_API_BASE_URL` set).

**Steps:**

1. Login as Admin.
2. Visit `/ar/admin`.
3. Navigate using the admin sidebar to `/ar/admin/users`, `/ar/admin/roles`, `/ar/admin/reports`.

**Expected Result:**

- Admin shell renders with sidebar.
- Pages load without unauthorized redirects.

### Scenario 2 — Non-admin is redirected away from admin routes

**Preconditions:**

- Have a non-admin user account (e.g., Customer).

**Steps:**

1. Login as non-admin.
2. Visit `/ar/admin`.

**Expected Result:**

- You are redirected to `/ar/dashboard` and see the “غير مصرح” toast.

### Scenario 3 — Admin can assign roles

**Preconditions:**

- Backend admin users endpoint is available.

**Steps:**

1. Login as Admin.
2. Go to `/ar/admin/users`.
3. Click “تعيين دور” on a row.
4. Select a different role and confirm.

**Expected Result:**

- Success toast is shown.
- Users list refreshes.

### Scenario 4 — Admin can verify suppliers

**Preconditions:**

- There is at least one supplier profile.

**Steps:**

1. Login as Admin.
2. Go to `/ar/admin/suppliers`.
3. Click “تحقق” (verify) or “تعليق” (suspend).

**Expected Result:**

- Supplier status updates after reload.

## API Test Endpoints

| Method | Endpoint                                     | Auth  | Expected Status |
| ------ | -------------------------------------------- | ----- | --------------- |
| GET    | `/api/v1/admin/users`                        | Admin | 200             |
| POST   | `/api/v1/admin/users/{user}/role`            | Admin | 200/204         |
| GET    | `/api/v1/admin/roles`                        | Admin | 200             |
| GET    | `/api/v1/admin/roles/{role}/permissions`     | Admin | 200             |
| GET    | `/api/v1/admin/suppliers`                    | Admin | 200             |
| PUT    | `/api/v1/suppliers/{supplierProfile}/verify` | Admin | 200             |
| GET    | `/api/v1/admin/activity-log`                 | Admin | 200             |

## Common Issues

| Issue                  | Cause                         | Fix                                    |
| ---------------------- | ----------------------------- | -------------------------------------- |
| Redirect loop to login | Missing/expired token         | Re-login; verify Sanctum token storage |
| 403 on admin pages     | User role not `admin`         | Assign admin role on backend; re-login |
| Reports export fails   | Missing `apiBaseUrl` or token | Verify `.env` and auth token           |
