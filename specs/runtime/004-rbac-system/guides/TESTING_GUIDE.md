# Testing Guide — RBAC System

> **Phase:** 01_PLATFORM_FOUNDATION > **Generated:** 2026-04-12T12:20:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
# Configure DB (e.g. MySQL `bunyan_test`) or use phpunit.xml env
cd ../frontend && npm install
```

## Running Tests

### RBAC-focused backend tests

```bash
cd backend
php artisan test tests/Feature/Middleware/CheckRoleTest.php
php artisan test tests/Feature/Middleware/CheckPermissionTest.php
php artisan test tests/Feature/Admin/RoleEndpointTest.php
php artisan test tests/Unit/Services/RoleServiceTest.php
php artisan test tests/Feature/ErrorHandling/E2ERBACErrorTest.php
```

### RBAC-focused frontend tests

```bash
cd frontend
npm run test -- tests/unit/composables/usePermission.spec.ts
npm run test -- tests/unit/middleware/role.spec.ts
```

### Full suites (CI parity)

```bash
cd /path/to/repo
npm run lint && npm run analyze && npm run typecheck && npm run test
```

## Manual Test Scenarios

### Scenario 1 — Admin assigns role to a user

**Preconditions:**

- Seeded database (`php artisan migrate:fresh --seed`).
- Admin user from seeder (see `UserSeeder` / docs for email; default dev password typically `password` if unchanged).

**Steps:**

1. `POST /api/v1/auth/login` as admin with JSON body `{ "email": "<admin_email>", "password": "<password>" }`.
2. Copy `token` from response.
3. `GET /api/v1/admin/users` with `Authorization: Bearer <token>` — expect `200` and paginated users.
4. `POST /api/v1/admin/users/{id}/role` with body `{ "role": "contractor" }` — expect `200` and user role updated in a follow-up `GET /api/v1/auth/profile` for that user.

**Expected Result:**

- Non-admin token on step 3 returns `403` with `error.code` `RBAC_ROLE_DENIED`.

### Scenario 2 — Customer blocked from contractor route

**Preconditions:**

- Customer user and a project where `customer_id` equals that user.

**Steps:**

1. Login as customer; obtain token.
2. `POST /api/v1/projects/{projectId}/phases` with `{ "name": "Test", "budget": 1000 }`.

**Expected Result:**

- HTTP `403`, JSON `error.code` = `RBAC_ROLE_DENIED`.

### Scenario 3 — Admin product CRUD path

**Preconditions:**

- Admin token.

**Steps:**

1. `POST /api/v1/admin/products` with valid product payload (name, category, price, quantity per `ProductController` validation).
2. `PUT /api/v1/admin/products/{id}` to rename product.

**Expected Result:**

- `201` on create, `200` on update. Customer token on same URLs returns `403`.

### Scenario 4 — Nuxt admin users (browser)

**Preconditions:**

- `npm run dev` for frontend and backend API reachable; admin session.

**Steps:**

1. Sign in as admin, open `/admin/users` (or localized equivalent from `navigation.ts`).
2. Open assign-role control on a row and change role; confirm toast and refreshed list.

**Expected Result:**

- Non-admin cannot load page (middleware redirect / error UX per app config).
