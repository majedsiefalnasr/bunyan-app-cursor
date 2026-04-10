# STAGE_01: Project Initialization — Detailed Specification

**Phase:** 01_PLATFORM_FOUNDATION  
**Stage File:** `specs/phases/01_PLATFORM_FOUNDATION/STAGE_01_PROJECT_INITIALIZATION.md`  
**Generated:** 2026-04-10  
**Status:** SPECIFYING

---

## Executive Summary

This specification details the complete initialization of the Bunyan platform: a monorepo containing Laravel 8.2+ backend, Nuxt.js 3 frontend, MySQL database, and integrated CI/CD pipeline. All deliverables must follow the Bunyan governance contracts (AGENTS.md, DESIGN.md, ADRs) and enforce RBAC, clean architecture layering, and error contract standardization.

---

## 1. BACKEND INITIALIZATION (Laravel 8.2+)

### 1.1 Objective

Establish a production-ready Laravel application with:
- RESTful API foundation (`/api/v1/`)
- Laravel Sanctum authentication
- Role-based access control (RBAC) middleware
- Service + Repository layering
- PHPUnit + Pest testing
- Static analysis (PHPStan)
- Code formatting (PHP-CS-Fixer)

### 1.2 Scope

#### 1.2.1 Project Structure & Configuration

| Item | Path | Description |
|------|------|-------------|
| Laravel app | `backend/` | Monorepo subdirectory |
| App namespace | `backend/app/` | All application code |
| Routes | `backend/routes/api.php` | Versioned API routes |
| Env template | `backend/.env.example` | Development template |
| CI env | `backend/.env.ci` | GitHub Actions environment |
| Config | `backend/config/` | Laravel configurations |

#### 1.2.2 Eloquent Models & Database Layer

**Models (Database Entities):**
- User (multi-role)
- Role (enum-based: customer, contractor, supervising_architect, field_engineer, admin)
- Project
- Phase
- Task (optional sub-unit of Phase)
- WorkflowConfiguration
- ApprovalRule
- Report
- Transaction
- Product
- Order

**Repositories (Data Access Layer):**
- Each model has a repository in `backend/app/Repositories/`
- Example: `UserRepository`, `ProjectRepository`, `PhaseRepository`
- All database queries go through repositories (no direct Eloquent in controllers)
- Repositories use Eloquent scopes and relationships

**Migrations (Forward-Only):**
- Location: `backend/database/migrations/`
- Naming: `YYYY_MM_DD_HHMMSS_create_<table>_table.php`
- Must include rollback (`down()` method)
- All tables use `utf8mb4` charset
- Foreign keys enforced with `onDelete('cascade')` or `onDelete('restrict')`

#### 1.2.3 API Controllers & HTTP Layer

**Structure:**
```
backend/app/Http/
├── Controllers/
│   ├── Api/
│   │   ├── V1/
│   │   │   ├── AuthController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── PhaseController.php
│   │   │   ├── TaskController.php
│   │   │   ├── ReportController.php
│   │   │   └── ...
│   │   └── BaseController.php
├── Middleware/
│   ├── VerifyApiToken.php (Sanctum)
│   ├── CheckRole.php (RBAC)
│   └── ...
├── Requests/
│   ├── Auth/
│   │   ├── LoginRequest.php
│   │   ├── RegisterRequest.php
│   │   └── ...
│   ├── Project/
│   │   ├── StoreProjectRequest.php
│   │   ├── UpdateProjectRequest.php
│   │   └── ...
│   └── ...
└── Resources/
    ├── UserResource.php
    ├── ProjectResource.php
    ├── PhaseResource.php
    └── ...
```

**BaseController Pattern:**
- All API controllers extend `BaseController`
- BaseController provides `sendSuccess()` and `sendError()` methods
- All responses follow error contract

#### 1.2.4 Services & Business Logic Layer

**Structure:**
```
backend/app/Services/
├── AuthService.php
├── ProjectService.php
├── PhaseService.php
├── TaskService.php
├── ReportService.php
├── WorkflowService.php (status transitions, approvals)
├── TransactionService.php
└── ...
```

**Rules:**
- Services contain all business logic
- Services use repositories for data access (dependency injection)
- Services handle events and jobs
- Services NEVER query Eloquent directly
- Services NEVER handle HTTP concerns
- Services use try/catch with custom exceptions

#### 1.2.5 Authentication & Authorization

**Sanctum Configuration:**
- File: `backend/config/sanctum.php`
- Token expiration: 7 days (configurable)
- Middleware: `auth:sanctum` on all protected routes

**Policies (Authorization):**
```
backend/app/Policies/
├── ProjectPolicy.php (viewAny, view, create, update, delete, approve)
├── PhasePolicy.php (similar)
├── TaskPolicy.php (similar)
├── ReportPolicy.php (similar)
└── ...
```

**Routes Protection:**
- All protected routes use `auth:sanctum` middleware
- All routes that modify data also use `can:` policy check
- RBAC enforced on all endpoints (no exceptions)
- Example:
  ```php
  Route::middleware(['auth:sanctum'])->group(function () {
      Route::post('/projects', [ProjectController::class, 'store'])
          ->middleware('can:create,App\Models\Project');
  });
  ```

#### 1.2.6 Error Handling & Response Contract

**Global Exception Handler:**
- File: `backend/app/Exceptions/Handler.php`
- All exceptions render to JSON with contract
- Error codes mapped in `backend/app/Enums/ErrorCode.php`

**Response Contract (All Endpoints):**
```json
{
  "success": true,
  "data": {},
  "message": "Operation successful",
  "errors": {}
}
```

**Error Response:**
```json
{
  "success": false,
  "data": null,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

#### 1.2.7 Form Requests (Validation)

**Structure:**
```
backend/app/Http/Requests/
├── Auth/
│   ├── LoginRequest.php
│   └── RegisterRequest.php
├── Project/
│   ├── StoreProjectRequest.php
│   ├── UpdateProjectRequest.php
│   └── ...
└── ...
```

**Anticipated Form Requests:**
- `Auth\LoginRequest` — email, password
- `Auth\RegisterRequest` — name, email, password, password_confirmation, phone, role
- `Project\StoreProjectRequest` — title, description, budget, customer_id, contractor_id
- `Project\UpdateProjectRequest` — title, description, budget
- `Phase\StorePhaseRequest` — name, description, budget, start_date, end_date, status
- `Task\StoreTaskRequest` — name, description, budget, assigned_to, status
- `Report\StoreReportRequest` — text, photos (file array), videos (file array)
- `Transaction\StoreTransactionRequest` — amount, type (payment/withdrawal), project_id
- `Product\StoreProductRequest` — name, description, price, category, sku
- `Order\StoreOrderRequest` — customer_id, items (array of product_id + quantity)

#### 1.2.8 Testing Configuration

**Unit Tests:**
- Location: `backend/tests/Unit/`
- Tool: PHPUnit
- Coverage: ≥80% for new code
- Scope: Services, repositories, utility functions

**Feature Tests:**
- Location: `backend/tests/Feature/`
- Tool: PHPUnit with Laravel test traits
- Scope: API endpoints, RBAC enforcement, database transactions
- Patterns: Test each controller action with auth + policy checks

**Test Example Structure:**
```php
// tests/Feature/Projects/CreateProjectTest.php
class CreateProjectTest extends TestCase {
    public function test_customer_can_create_project() { }
    public function test_contractor_cannot_create_project() { }
    public function test_unauthenticated_user_cannot_create_project() { }
    public function test_validation_fails_on_missing_budget() { }
}
```

**Database Seeding for Tests:**
- File: `backend/database/seeders/DatabaseSeeder.php`
- Seeders for: users (multi-role), projects, phases, tasks
- Use factories for randomized test data

### 1.3 Deliverables (Backend)

#### 1.3.1 Configuration Files

| File | Purpose |
|------|---------|
| `backend/.env.example` | Development env template |
| `backend/.env.ci` | CI environment variables |
| `backend/config/sanctum.php` | Sanctum auth config |
| `backend/config/app.php` | (Laravel default) |
| `backend/phpunit.xml` | PHPUnit test config |
| `backend/phpstan.neon` | PHPStan static analysis |
| `backend/.php-cs-fixer.php` | PHP-CS-Fixer config |
| `backend/pint.json` | Laravel Pint config (optional) |

#### 1.3.2 Application Code

| Item | Count | Location |
|------|-------|----------|
| Eloquent Models | 10 | `backend/app/Models/` |
| Repositories | 10 | `backend/app/Repositories/` |
| Services | 8 | `backend/app/Services/` |
| Controllers | 8 | `backend/app/Http/Controllers/Api/V1/` |
| Form Requests | 15 | `backend/app/Http/Requests/` |
| Resources (API) | 10 | `backend/app/Http/Resources/` |
| Policies | 8 | `backend/app/Policies/` |
| Exceptions | 3 | `backend/app/Exceptions/` |
| Enums | 5 | `backend/app/Enums/` |

#### 1.3.3 Migrations & Database

| Item | Files |
|------|-------|
| Migrations | 12 (users, roles, projects, phases, tasks, reports, transactions, products, orders, workflow configs, approval rules, audit log) |
| Seeders | 2 (DatabaseSeeder, RoleSeeder) |
| Factories | 10 (User, Project, Phase, Task, Report, Transaction, Product, Order) |

#### 1.3.4 Tests

| Test Suite | Count | Tool |
|------------|-------|------|
| Unit tests | 20+ | PHPUnit |
| Feature tests | 30+ | PHPUnit Feature |
| Total coverage target | ≥80% | |

#### 1.3.5 Scripts (composer.json)

```json
{
  "scripts": {
    "lint": "php-cs-fixer fix --dry-run --diff",
    "lint:fix": "php-cs-fixer fix",
    "analyze": "phpstan analyse --memory-limit=512M",
    "test": "php artisan test",
    "test:coverage": "php artisan test --coverage",
    "dev": "php artisan serve"
  }
}
```

### 1.4 Acceptance Criteria (Backend)

- [ ] Laravel app runs without errors on `php artisan serve`
- [ ] Sanctum configured and API auth endpoints functional
- [ ] All models have repositories with basic CRUD operations
- [ ] All RBAC-protected routes enforce policies server-side
- [ ] Error responses conform to contract on all endpoints
- [ ] All Form Requests validate input correctly
- [ ] All Services receive 100% dependency injection (no `new` in services)
- [ ] Controllers are thin (≤15 lines, all logic in services)
- [ ] `composer run lint` passes with no violations
- [ ] `composer run analyze` passes with no errors
- [ ] `composer run test` passes with ≥80% coverage on new code
- [ ] All migrations have rollback methods
- [ ] Database seeders populate all roles and base data
- [ ] No direct Eloquent queries in controllers or repositories
- [ ] All business logic in services only

---

## 2. FRONTEND INITIALIZATION (Nuxt.js 3)

### 2.1 Objective

Establish a production-ready Nuxt.js 3 frontend with:
- Nuxt UI component library (`@nuxt/ui`)
- Tailwind CSS v4 with RTL support
- Pinia state management
- i18n (Arabic + English)
- Vitest unit testing
- Playwright E2E testing
- TypeScript support
- ESLint + Prettier

### 2.2 Scope

#### 2.2.1 Project Structure

```
frontend/
├── app.vue                    # Root layout entry
├── components/
│   ├── Layout/
│   │   ├── Header.vue
│   │   ├── Sidebar.vue
│   │   └── Footer.vue
│   ├── Forms/
│   │   ├── LoginForm.vue
│   │   ├── ProjectForm.vue
│   │   └── ...
│   ├── Cards/
│   │   ├── ProjectCard.vue
│   │   ├── PhaseCard.vue
│   │   └── ...
│   └── Common/
│       ├── LoadingSpinner.vue
│       ├── ErrorBoundary.vue
│       └── ...
├── pages/
│   ├── index.vue              # Home / dashboard
│   ├── auth/
│   │   ├── login.vue
│   │   ├── register.vue
│   │   └── forgot-password.vue
│   ├── dashboard/
│   │   ├── index.vue          # Role-based dashboard
│   │   ├── projects/
│   │   │   ├── index.vue      # List projects
│   │   │   ├── [id].vue       # View project
│   │   │   └── create.vue     # Create project
│   │   └── ...
│   └── admin/
│       ├── index.vue
│       ├── users.vue
│       └── settings.vue
├── layouts/
│   ├── default.vue            # Main app layout
│   ├── auth.vue               # Auth flow layout
│   └── admin.vue              # Admin layout
├── composables/
│   ├── useAuth.ts
│   ├── useProject.ts
│   ├── useApi.ts
│   └── ...
├── stores/
│   ├── auth.ts                # Pinia store
│   ├── project.ts
│   ├── ui.ts
│   └── ...
├── middleware/
│   ├── auth.ts
│   ├── admin.ts
│   └── ...
├── plugins/
│   ├── i18n.ts
│   ├── api.ts
│   └── ...
├── locales/
│   ├── ar.json                # Arabic translations
│   └── en.json                # English translations
├── types/
│   ├── index.ts
│   ├── api.ts
│   └── models.ts
├── utils/
│   ├── api.ts
│   ├── formatting.ts
│   └── validation.ts
├── assets/
│   ├── images/
│   └── styles/
├── tests/
│   ├── unit/
│   ├── components/
│   └── e2e/
├── nuxt.config.ts             # Nuxt configuration
├── tsconfig.json              # TypeScript config
├── tailwind.config.js         # Tailwind CSS v4
├── eslintrc.js                # ESLint config
├── vitest.config.ts           # Vitest config
├── playwright.config.ts       # Playwright config
└── package.json
```

#### 2.2.2 Nuxt UI Components & Design System

**Theme Configuration:**
- Vercel-inspired design system (DESIGN.md compliance)
- Geist fonts (primary), Geist Mono (code)
- Shadow-as-border technique: `box-shadow 0px 0px 0px 1px`
- Color palette: `#171717` (text), `#ffffff` (bg), workflow accents
- RTL support via Tailwind logical properties + `dir="rtl"` on `<html>`

**Anticipated Nuxt UI Components:**
- `UButton` — primary actions
- `UCard` — feature sections, data containers
- `UForm` — form wrapper with validation
- `UInput` — text input fields
- `USelect` — dropdown selections
- `UTable` — data tables (projects, phases, tasks)
- `UModal` — dialogs (confirm, create, edit)
- `UBadge` — status indicators (pending, in-progress, complete)
- `UAlert` — error/success/info messages
- `UDropdown` — user menu, role selector
- `UPagination` — table/list pagination
- `UCheckbox`, `URadio`, `UToggle` — form controls
- `UTextarea` — multi-line text (reports, descriptions)
- `UDivider` — section separators
- `USkeleton` — loading placeholders

#### 2.2.3 State Management (Pinia)

**Stores:**
```
frontend/stores/
├── auth.ts          # User, token, role
├── project.ts       # Active project, project list
├── phase.ts         # Active phase, phase list
├── task.ts          # Active task, task list
├── ui.ts            # Theme, sidebar state, modals
└── ...
```

**Store Pattern (Composition API):**
```typescript
// stores/auth.ts
import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(null);
  const role = ref<UserRole | null>(null);

  const login = async (email: string, password: string) => {
    // Call API service
    // Set user, token, role
    // Redirect to dashboard
  };

  return { user, token, role, login };
});
```

#### 2.2.4 API Client Integration

**Composable:**
```
frontend/composables/useApi.ts
```

**Usage:**
```typescript
// Inside component
const { $api } = useNuxtApp();
const projects = await $api.get('/api/v1/projects');
```

**Patterns:**
- Error handling: catch API errors and display toasts
- Auth: auto-attach Sanctum token to headers
- Retry logic: retry failed requests with exponential backoff
- Loading states: track pending requests in Pinia store

#### 2.2.5 Forms & Validation

**Validation Libraries:**
- VeeValidate + Zod for schema validation
- Example:
  ```typescript
  const schema = z.object({
    email: z.string().email(),
    password: z.string().min(8),
  });
  ```

**Form Component Pattern:**
```vue
<template>
  <UForm :schema="schema" @submit="onSubmit">
    <UFormGroup label="Email" name="email">
      <UInput v-model="form.email" type="email" />
    </UFormGroup>
    <UButton type="submit">Login</UButton>
  </UForm>
</template>
```

#### 2.2.6 Internationalization (i18n)

**Setup:**
- Module: `@nuxtjs/i18n`
- Locales: Arabic (`ar`) + English (`en`)
- Default locale: Arabic (Arabic-first platform)
- RTL: Automatic via `<html dir="rtl">` for Arabic

**Translation Keys:**
```json
{
  "common": {
    "save": "حفظ",
    "cancel": "إلغاء",
    "delete": "حذف"
  },
  "auth": {
    "login": "تسجيل الدخول",
    "email": "البريد الإلكتروني"
  }
}
```

**Usage in Components:**
```vue
<template>
  <button>{{ $t('common.save') }}</button>
</template>
```

#### 2.2.7 RTL Support & Layouts

**CSS Logical Properties:**
- Use `ms` (margin-inline-start) instead of `ml` (margin-left)
- Use `pe` (padding-inline-end) instead of `pr` (padding-right)
- Tailwind v4 supports logical properties natively

**HTML Structure:**
```html
<html dir="rtl" lang="ar">
  <body class="bg-white text-gray-900">
    <!-- Content -->
  </body>
</html>
```

**Component Patterns:**
- Nuxt UI handles RTL automatically
- Flex and grid layouts respond to `dir` attribute
- Test both LTR (English) and RTL (Arabic) renders

#### 2.2.8 Testing Configuration

**Unit Tests (Vitest):**
- Location: `frontend/tests/unit/`
- Tool: Vitest + Vue Test Utils
- Scope: Composables, utilities, store logic

**Component Tests (Vitest + Vue Test Utils):**
- Location: `frontend/tests/components/`
- Scope: Component rendering, user interactions, prop changes

**E2E Tests (Playwright):**
- Location: `frontend/tests/e2e/`
- Tool: Playwright + `@nuxt/test-utils`
- Scope: Critical user journeys (login, create project, submit report)

**Test Examples:**
```typescript
// tests/unit/composables/useAuth.test.ts
describe('useAuth', () => {
  it('should store token on login', async () => {
    const { login } = useAuth();
    await login('test@example.com', 'password');
    expect(useAuthStore().token).toBeTruthy();
  });
});

// tests/e2e/auth.spec.ts
test('user can login', async ({ page }) => {
  await page.goto('/auth/login');
  await page.fill('input[name="email"]', 'test@example.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL('/dashboard');
});
```

### 2.3 Deliverables (Frontend)

#### 2.3.1 Configuration Files

| File | Purpose |
|------|---------|
| `frontend/nuxt.config.ts` | Nuxt.js main config |
| `frontend/tsconfig.json` | TypeScript config |
| `frontend/tailwind.config.js` | Tailwind CSS v4 config |
| `frontend/.eslintrc.js` | ESLint config |
| `frontend/.prettierrc.json` | Prettier config |
| `frontend/vitest.config.ts` | Vitest test config |
| `frontend/playwright.config.ts` | Playwright test config |

#### 2.3.2 Components & Pages

| Item | Count | Location |
|------|-------|----------|
| Layout components | 3 | `frontend/components/Layout/` |
| Form components | 8 | `frontend/components/Forms/` |
| Card components | 6 | `frontend/components/Cards/` |
| Common components | 5 | `frontend/components/Common/` |
| Pages | 15+ | `frontend/pages/` |
| Layouts | 3 | `frontend/layouts/` |

#### 2.3.3 State & Logic

| Item | Count | Location |
|------|-------|----------|
| Pinia stores | 6 | `frontend/stores/` |
| Composables | 8 | `frontend/composables/` |
| Middleware | 3 | `frontend/middleware/` |
| Plugins | 3 | `frontend/plugins/` |
| Utilities | 5 | `frontend/utils/` |
| Types | 3 | `frontend/types/` |

#### 2.3.4 Tests

| Test Suite | Count | Tool |
|------------|-------|------|
| Unit tests (composables) | 15+ | Vitest |
| Component tests | 20+ | Vitest + Vue Test Utils |
| E2E tests | 10+ | Playwright |
| Total coverage target | ≥70% | |

#### 2.3.5 Localization

| Item | Files |
|------|-------|
| Arabic translations | 1 | `frontend/locales/ar.json` |
| English translations | 1 | `frontend/locales/en.json` |
| Translation keys | 100+ | Split across pages/components |

#### 2.3.6 Scripts (package.json)

```json
{
  "scripts": {
    "dev": "nuxt dev",
    "build": "nuxt build",
    "preview": "nuxt preview",
    "lint": "eslint .",
    "lint:fix": "eslint . --fix",
    "format": "prettier --write \"./**/*.{vue,ts,js,json,css}\"",
    "typecheck": "nuxt typecheck",
    "test": "vitest run",
    "test:watch": "vitest",
    "test:e2e": "playwright test",
    "test:e2e:ui": "playwright test --ui"
  }
}
```

### 2.4 Acceptance Criteria (Frontend)

- [ ] Nuxt app runs without errors on `npm run dev`
- [ ] All pages render without 404 errors
- [ ] Nuxt UI components display with correct Vercel-inspired styling
- [ ] i18n configured with Arabic (default) + English
- [ ] RTL layout works correctly for Arabic locale
- [ ] Geist fonts load and apply with correct letter-spacing
- [ ] All protected pages redirect to login if unauthenticated
- [ ] Role-based pages (admin) enforce correct access
- [ ] Forms validate input correctly with Zod schemas
- [ ] API client composable handles auth and error states
- [ ] Pinia stores dispatch actions correctly
- [ ] `npm run lint` passes with no violations
- [ ] `npm run typecheck` passes with no errors
- [ ] `npm run test` passes with ≥70% coverage
- [ ] `npm run test:e2e` runs critical user journeys
- [ ] All components support RTL (logical properties used)
- [ ] Toast/notification system handles API errors
- [ ] Loading states (skeletons, spinners) display during data fetch
- [ ] No console errors or warnings in dev mode

---

## 3. TESTING FRAMEWORKS

### 3.1 Backend Testing (PHPUnit + Pest)

**Configuration:**
- File: `backend/phpunit.xml`
- Database: SQLite in-memory for tests
- Traits: `RefreshDatabase`, `WithFaker`, `ActingAs` (user impersonation)

**Test Structure:**
```php
namespace Tests\Feature\Projects;

use Tests\TestCase;
use App\Models\User;

class CreateProjectTest extends TestCase {
    public function test_authenticated_customer_can_create_project() {
        $user = User::factory()->customer()->create();
        $response = $this->actingAs($user)
            ->postJson('/api/v1/projects', [
                'title' => 'New Project',
                'budget' => 100000,
            ]);
        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'data', 'message']);
    }
}
```

### 3.2 Frontend Testing (Vitest + Playwright)

**Configuration:**
- File: `frontend/vitest.config.ts`
- File: `frontend/playwright.config.ts`
- Headless: true for CI, headed mode for local development

### 3.3 Coverage Requirements

| Layer | Minimum Coverage | Tool |
|-------|------------------|------|
| Backend services | 80% | PHPUnit |
| Backend controllers | 70% | PHPUnit Feature |
| Frontend composables | 70% | Vitest |
| Frontend components | 60% | Vitest + VTU |
| E2E critical flows | 90% pass rate | Playwright |

---

## 4. CI/CD PIPELINE FOUNDATION

### 4.1 GitHub Actions Workflows

**File:** `.github/workflows/pre-commit-guard.yml`

**Jobs:**
1. **Backend Lint** — `php-cs-fixer --dry-run` (fail on violations)
2. **Backend Static Analysis** — `phpstan analyse` (fail on errors)
3. **Backend Tests** — `php artisan test` (fail on failures)
4. **Frontend Lint** — `eslint .` (fail on violations)
5. **Frontend Format** — `prettier --check` (fail on diffs)
6. **Frontend TypeCheck** — `npx nuxi typecheck` (fail on errors)
7. **Frontend Tests** — `npm run test` (fail on failures)
8. **E2E Tests** — `npm run test:e2e` (fail on failures)

**Triggers:**
- On pull request to `develop`
- On push to `develop` or `main`
- Manual trigger

### 4.2 Local Pre-Commit Hooks

**Files:**
- `.husky/pre-commit` — Runs linting and tests before commit
- `.lintstagedrc.json` — Incremental validation (changed files only)

**Enforcement:**
- Pre-commit hooks block commits if violations found
- CI repeats checks (defense in depth)
- No force-push to `main` or `develop` (branch protection)

---

## 5. ENVIRONMENT CONFIGURATION

### 5.1 Backend (.env.example)

```env
APP_NAME=Bunyan
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bunyan
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=cookie

SANCTUM_STATEFUL_DOMAINS=localhost:3000

MAIL_MAILER=log
```

### 5.2 Frontend (.env.example)

```env
VITE_API_BASE_URL=http://localhost:8000
VITE_API_VERSION=v1
```

### 5.3 CI Environment (.env.ci)

```env
# Backend
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array

# Frontend
VITE_API_BASE_URL=http://localhost:8000/api
```

---

## 6. MONOREPO STRUCTURE & TOOLS

### 6.1 Root Directory

```
bunyan-app-cursor/
├── backend/                    # Laravel application
├── frontend/                   # Nuxt.js application
├── docs/                       # Documentation
├── specs/                      # Specification documents
├── .github/
│   └── workflows/              # GitHub Actions
├── .husky/                     # Pre-commit hooks
├── docker-compose.yml          # Local dev stack
├── .lintstagedrc.json          # lint-staged config
├── .env.ci                     # CI environment
├── package.json                # Root npm scripts
├── composer.json               # (in backend/)
├── README.md                   # Project documentation
└── CONTRIBUTING.md             # Contribution guide
```

### 6.2 Root package.json

```json
{
  "name": "bunyan-app",
  "version": "1.0.0",
  "description": "Arabic construction services marketplace",
  "scripts": {
    "install:all": "npm install && cd backend && composer install && cd ../frontend && npm install",
    "lint": "cd backend && composer lint && cd ../frontend && npm run lint",
    "lint:fix": "cd backend && composer lint:fix && cd ../frontend && npm run lint:fix",
    "test": "cd backend && composer test && cd ../frontend && npm run test",
    "dev": "concurrently \"npm run dev:backend\" \"npm run dev:frontend\"",
    "dev:backend": "cd backend && php artisan serve",
    "dev:frontend": "cd frontend && npm run dev"
  },
  "devDependencies": {
    "husky": "^9.0.0",
    "lint-staged": "^15.0.0",
    "concurrently": "^8.0.0"
  }
}
```

---

## 7. DOCKER COMPOSE (Local Development)

**File:** `docker-compose.yml`

**Services:**
- **MySQL 8.0** — Database (port 3306)
- **Redis 7** — Cache & queue (port 6379)
- **Node 20** — Frontend dev watcher (port 3000)
- **PHP 8.2-fpm** — Backend runtime (port 8000 via Laravel Artisan)

**Volumes:**
- `backend/` mounted to `/app/backend`
- `frontend/` mounted to `/app/frontend`
- Database persistence: `mysql_data` volume

---

## 8. KEY DELIVERABLES SUMMARY

### 8.1 Backend Deliverables

| Category | Count | Files |
|----------|-------|-------|
| Models | 10 | `backend/app/Models/` |
| Repositories | 10 | `backend/app/Repositories/` |
| Services | 8 | `backend/app/Services/` |
| Controllers | 8 | `backend/app/Http/Controllers/Api/V1/` |
| Form Requests | 15 | `backend/app/Http/Requests/` |
| Migrations | 12 | `backend/database/migrations/` |
| Tests (Unit + Feature) | 50+ | `backend/tests/` |
| Config Files | 8 | `backend/` root + `backend/config/` |

### 8.2 Frontend Deliverables

| Category | Count | Files |
|----------|-------|-------|
| Pages | 15+ | `frontend/pages/` |
| Components | 22+ | `frontend/components/` |
| Stores (Pinia) | 6 | `frontend/stores/` |
| Composables | 8 | `frontend/composables/` |
| Tests (Unit + Component + E2E) | 45+ | `frontend/tests/` |
| Config Files | 7 | `frontend/` root |
| Locales | 2 | `frontend/locales/` |

### 8.3 Infrastructure Deliverables

| Item | Files |
|------|-------|
| GitHub Actions workflows | 1 | `.github/workflows/pre-commit-guard.yml` |
| Pre-commit hooks | 2 | `.husky/pre-commit`, `.lintstagedrc.json` |
| Docker Compose | 1 | `docker-compose.yml` |
| Configuration | 2 | `.env.example`, `.env.ci` |

---

## 9. CLARIFICATIONS

### Session 2026-04-10

#### 1. PHP Version & Laravel Version Specificity

**Question:** Section 1.1 states "Laravel 8.2+" but Laravel 8.2 doesn't exist. Did you mean:
- [ ] Laravel 8.x (latest 8.x) with PHP 8.2+?
- [ ] Laravel 10.x (current LTS) with PHP 8.1+?
- [ ] Laravel 11.x (latest) with PHP 8.2+?

**Impact:** Affects Sanctum API, migration patterns, attribute casting syntax, and dependency versions.

**Recommended Decision:** Use **Laravel 11.x with PHP 8.2+** for modern features (value objects, pest, lazy collections, strict mode support).

---

#### 2. Database Connection Pooling & Redis Caching Strategy

**Question:** Section 5.1 specifies Redis for CACHE_DRIVER and QUEUE_CONNECTION, but doesn't clarify:
- [ ] Should we implement connection pooling for MySQL?
- [ ] Is Redis required for local development (docker-compose.yml), or fallback to in-memory?
- [ ] What's the expected load? (Affects pool size: 5-10 dev, 20-50 production)
- [ ] Should we use Redis for sessions or stick with cookie-based?

**Impact:** Affects docker-compose.yml services, .env configuration, memory usage, and horizontal scaling readiness.

**Recommended Decision:** 
- Use **Redis for cache + queue** (production-ready, required in docker-compose)
- Use **no connection pooling** for MySQL in initial phase (Eloquent connection pooling can be added in Phase 02 if needed)
- Use **cookie-based sessions** (stateless API, Sanctum tokens)
- Redis pool: `REDIS_POOL_MAX=10` for development

---

#### 3. RBAC Middleware Ordering & Priority Conflicts

**Question:** Section 1.2.5 applies both `auth:sanctum` and `can:` middleware on routes. What's the execution order?
- [ ] Should `auth:sanctum` fail first (401) if token invalid, **then** check `can:` (403)?
- [ ] Are there conflicts if a route has `can:create,App\Models\Project` but policy doesn't exist?
- [ ] Should there be a global `CheckRole` middleware to catch missing `can:` checks?

**Impact:** Affects error responses (401 vs 403 ordering), security posture, and developer experience.

**Recommended Decision:**
- Use **middleware ordering: `['auth:sanctum', 'can:action,resource']`** (auth first, then policy)
- Return **401 Unauthorized** if token missing/invalid
- Return **403 Forbidden** if authenticated user lacks permission
- Policies are **mandatory** for all protected routes (enforce via code review)

---

#### 4. Testing Coverage Thresholds — Exact vs. Aspirational?

**Question:** Section 3.3 specifies coverage as "minimum" but acceptance criteria (1.4, 2.4) state "≥80% backend / ≥70% frontend". Are these:
- [ ] Hard gates (PR blocked if not met)?
- [ ] Aspirational targets (warn but allow)?
- [ ] Per-file or aggregate coverage?
- [ ] Should we exclude migrations, config files, seeders from coverage?

**Impact:** Affects CI/CD (phpunit.xml phpstan neon threshold), time-to-merge, and testing discipline.

**Recommended Decision:**
- Use **aggregate coverage thresholds: ≥80% backend / ≥70% frontend**
- **Hard gate in CI** (fail builds below threshold)
- Exclude: `migrations/`, `config/`, `resources/`, database `seeders/` from coverage
- Per-package: Services ≥85%, Repositories ≥80%, Controllers ≥70%

---

#### 5. Docker Compose Redis vs. In-Memory Cache for Local Development

**Question:** Section 7 specifies Redis in docker-compose.yml, but:
- [ ] Should we support local development without Docker? (In-memory fallback?)
- [ ] Is Redis mandatory or optional for SPECIFY phase (STAGE_01)?
- [ ] Should we generate `.env.docker` for docker-compose users vs. `.env.example` for local?
- [ ] What happens if Redis service fails—should app gracefully degrade?

**Impact:** Affects developer onboarding, docker-compose.yml design, .env strategy, and resilience.

**Recommended Decision:**
- **Redis is required** in docker-compose.yml (production-like setup)
- Generate **two env files**: `.env.example` (for local setup with array cache), `.env.docker` (for Docker with Redis)
- Support **both**: Local dev with `CACHE_DRIVER=array`, Docker dev with `CACHE_DRIVER=redis`
- Add **circuit breaker** in services if Redis becomes optional (mark as Phase 02 enhancement)

---

## 10. CONFIRMATION: Clarifications Documented

All 5 clarifications have been appended to **Section 9 (Clarifications)** with:
- Question statement
- Impact analysis  
- Recommended decision

These clarifications will be addressed in the **PLAN step** where a human decision-maker chooses the path forward. The IMPLEMENT step will proceed with recommended decisions as defaults, unless overridden.

---

## 10. CONSTRAINTS & ENFORCEMENT

### 10.1 Non-Negotiable Rules

1. **RBAC on All Protected Routes:** Every endpoint modifying data must enforce role-based policies server-side.
2. **Service Layer Mandatory:** No business logic in controllers or repositories.
3. **Repository Pattern:** All database queries go through repositories.
4. **Error Contract:** All API responses conform to `{success, data, message, errors}` contract.
5. **Thin Controllers:** Controllers ≤15 lines, delegate to services.
6. **Dependency Injection:** Services receive dependencies via constructor, no `new` keyword.
7. **Eloquent ORM Only:** No raw SQL queries; use Eloquent scopes and relationships.
8. **Forward-Only Migrations:** Never modify existing migration files; add new migrations for changes.
9. **Arabic-First Frontend:** All UI components support RTL, translations in Arabic + English.
10. **Design System Compliance:** Vercel-inspired (Geist fonts, shadow-as-border, color palette from DESIGN.md).

### 10.2 Validation Pipeline (CI)

```bash
# All PRs must pass this pipeline
composer run lint && composer run analyze && composer run test && \
npm run lint && npm run typecheck && npm run test
```

### 10.3 Code Quality Gates

- **Linting:** Zero violations (php-cs-fixer, eslint)
- **Static Analysis:** Zero errors (phpstan, typescript)
- **Testing:** ≥80% backend coverage, ≥70% frontend coverage
- **E2E:** Critical flows pass 100% (playwright)

---

## 11. SUCCESS CRITERIA

**All of the following must be true to consider SPECIFY step complete:**

1. ✅ This spec.md file generated with detailed, unambiguous sections
2. ✅ `checklists/requirements.md` generated with RBAC, Form Requests, relationships, services, RTL checklists
3. ✅ Backend structure templates created (Models, Controllers, Services, Repositories, Policies, Exceptions)
4. ✅ Frontend structure templates created (Pages, Components, Stores, Composables, Middleware)
5. ✅ All 50+ backend deliverables itemized with paths and purposes
6. ✅ All 45+ frontend deliverables itemized with paths and purposes
7. ✅ Testing frameworks configured (PHPUnit, Vitest, Playwright)
8. ✅ CI/CD pipeline foundation defined (.github/workflows/, .husky/)
9. ✅ Docker Compose for local dev specified
10. ✅ Error contract and response format standardized
11. ✅ i18n and RTL support detailed with code examples
12. ✅ All constraints from AGENTS.md, DESIGN.md, ADRs enforced in spec

---

**Generated by:** SPECIFY Step  
**Specification Date:** 2026-04-10  
**Status:** COMPLETE
