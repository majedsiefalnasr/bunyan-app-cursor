# STAGE_01: Project Initialization — Framework & Technology Research

**Phase:** 01_PLATFORM_FOUNDATION  
**Status:** PLANNING  
**Date:** 2026-04-10  
**Audience:** Engineering team, architects, decision makers

---

## Executive Summary

This research document provides deep dives into the core technologies selected for Bunyan: **Laravel 11.x**, **Nuxt.js 3**, **PHPUnit + Pest**, **Vitest + Playwright**, and **GitHub Actions**. Each section includes rationale, setup patterns, known limitations, and best practices to guide implementation.

---

## 1. Laravel 11.x Backend Deep Dive

### 1.1 Why Laravel 11.x?

**Laravel Version Selection:**

- ✅ **Laravel 11.x** (Latest as of 2026): Modern features, PHP 8.3 native types, improved bootstrapping
- ✅ **Long-Term Support (LTS):** Laravel 10 reached EOL in 2024; Laravel 11 is current stable
- ⚠️ **PHP 8.2+ Requirement:** Laravel 11 requires PHP 8.2+ (we'll use PHP 8.3)
- ✅ **Sanctum Authentication:** Built-in, no external dependencies

**Alternative Considered:** Symfony 7.x

- More modular but steeper learning curve
- Overkill for this project scope
- Laravel's ORM (Eloquent) faster to develop with

### 1.2 Eloquent ORM Patterns

#### Best Practices

```php
// ✅ GOOD: Use scopes for query logic
class User extends Model {
    public function scopeCustomers($query) {
        return $query->where('role', 'customer');
    }
}

$customers = User::customers()->get();

// ❌ AVOID: Direct where clauses in controllers
if ($role == 'customer') { ... }
```

#### Relationship Performance

| Relationship Type  | Use Case               | N+1 Risk? | Solution                                 |
| ------------------ | ---------------------- | --------- | ---------------------------------------- |
| `belongsTo()`      | Child → Parent         | Yes       | Use `with('parent')` eager loading       |
| `hasMany()`        | Parent → Many children | Yes       | Use `with('children')` eager loading     |
| `belongsToMany()`  | Many-to-many via pivot | Yes       | Use `with('related')` + pivot loading    |
| `hasManyThrough()` | Nested relationships   | Yes       | Use `with('through.related')`            |
| Polymorphic        | Entity types           | Highest   | Cache entity types, use separate queries |

**Critical Pattern:** Always use `with()` in controllers/services to prevent N+1 queries.

```php
// Load project with all related data
$project = Project::with([
    'customer',
    'contractor',
    'phases',
    'phases.tasks',
    'phases.tasks.reports',
    'transactions'
])->find($id);
```

#### Eloquent Casts

```php
// PHP 8.1+ Attributes for typed properties
#[Attribute]
class UserRole extends Enum {
    case Customer;
    case Contractor;
}

class User extends Model {
    protected $casts = [
        'role' => UserRole::class,  // Auto-cast to enum
        'created_at' => 'datetime',
        'metadata' => 'json',
    ];
}
```

### 1.3 Sanctum Authentication

#### Token-Based Flow

```
1. POST /api/v1/auth/login (email, password)
   ↓
2. Backend generates token: $user->createToken('api-token')
   ↓
3. Response: { "access_token": "...", "token_type": "Bearer" }
   ↓
4. Frontend stores token in localStorage
   ↓
5. Subsequent requests: Authorization: Bearer <token>
   ↓
6. Middleware: auth:sanctum verifies token
```

#### Sanctum Configuration

```php
// config/sanctum.php
return [
    'stateful' => ['localhost:3000'],  // SPA origin
    'guard' => 'sanctum',
    'expiration' => 7 * 24 * 60,  // 7 days in minutes
    'token_prefix' => '',
];
```

#### Token Expiration & Refresh

- Default: 7 days
- Strategy: Use refresh token endpoint to extend session
- Pattern: Store refresh token separately, rotate on each request (optional)

### 1.4 Service Layer Pattern

#### Requirements

1. **Constructor Injection Only**

   ```php
   class ProjectService {
       public function __construct(
           private ProjectRepository $projects,
           private WorkflowService $workflow
       ) {}
   }
   ```

2. **No Eloquent Direct Queries**

   ```php
   // ❌ WRONG
   public function create($data) {
       $project = Project::create($data);  // Direct Eloquent!
   }

   // ✅ CORRECT
   public function create($data) {
       $project = $this->projects->create($data);  // Via repository
   }
   ```

3. **Database Transactions for Multi-Step Operations**

   ```php
   public function createProjectWithPhase($projectData, $phaseData) {
       return DB::transaction(function () use ($projectData, $phaseData) {
           $project = $this->projects->create($projectData);
           $this->phases->create([...$phaseData, 'project_id' => $project->id]);
           return $project;
       });
   }
   ```

4. **Exception Handling**
   ```php
   try {
       $project = $this->create($data);
   } catch (ValidationException $e) {
       throw ProjectValidationException::invalidBudget($data['budget']);
   } catch (Exception $e) {
       Log::error('Project creation failed', ['error' => $e]);
       throw ProjectException::createFailed();
   }
   ```

### 1.5 Repository Pattern

#### Structure

```php
interface ProjectRepositoryInterface {
    public function create(array $data): Project;
    public function find(int $id): ?Project;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByCustomer(int $customerId): Collection;
}

class ProjectRepository implements ProjectRepositoryInterface {
    public function getByCustomer(int $customerId): Collection {
        return Project::where('customer_id', $customerId)
            ->with('phases', 'tasks', 'transactions')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

#### Benefits

1. **Testability:** Mock repository in service tests
2. **Query Reusability:** Scopes + methods centralized
3. **Type Safety:** Return types enforced
4. **Auditability:** All DB access in one place

### 1.6 Policy Authorization

#### Pattern

```php
class ProjectPolicy {
    public function update(User $user, Project $project): bool {
        // Customer can update own projects
        if ($user->role === UserRole::Customer) {
            return $user->id === $project->customer_id;
        }

        // Admin can update any project
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return false;
    }
}

// In controller
Route::patch('/projects/{project}', function (Project $project) {
    $this->authorize('update', $project);  // Calls policy
    // ...
});
```

#### Testing Policies

```php
class ProjectPolicyTest extends TestCase {
    public function test_customer_can_update_own_project() {
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->customer($customer)->create();

        $this->assertTrue(
            (new ProjectPolicy())->update($customer, $project)
        );
    }

    public function test_contractor_cannot_update_customer_project() {
        $customer = User::factory()->customer()->create();
        $contractor = User::factory()->contractor()->create();
        $project = Project::factory()->customer($customer)->create();

        $this->assertFalse(
            (new ProjectPolicy())->update($contractor, $project)
        );
    }
}
```

### 1.7 Testing Strategy

#### PHPUnit vs. Pest

| Feature          | PHPUnit               | Pest                       |
| ---------------- | --------------------- | -------------------------- |
| Syntax           | OOP class-based       | DSL-based, fluent          |
| Learning Curve   | Moderate              | Low                        |
| Test Readability | Higher (more verbose) | Higher (DSL is expressive) |
| Fixtures         | setUp/tearDown        | Closures + datasets        |
| Assertion Count  | Large                 | Smaller, more readable     |
| Community        | Larger                | Growing, modern            |

**Decision:** Use **PHPUnit 11.x** (Laravel default) for familiarity and stability. Pest can be added later if preferred.

#### Test Categories

1. **Unit Tests:** Business logic, utilities (isolated, mocked dependencies)
2. **Feature Tests:** API endpoints, full HTTP request/response
3. **Integration Tests:** End-to-end database transactions

```php
// Unit test: Service in isolation
class ProjectServiceTest extends TestCase {
    public function test_create_sets_workflow_config() {
        $repo = Mockery::mock(ProjectRepository::class);
        $repo->shouldReceive('create')->andReturn(new Project());

        $service = new ProjectService($repo, ...);
        $project = $service->create($data);

        $this->assertNotNull($project->workflow_config_id);
    }
}

// Feature test: Full HTTP request
class CreateProjectTest extends TestCase {
    public function test_customer_can_create_project() {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)
            ->postJson('/api/v1/projects', [
                'title' => 'New Project',
                'budget' => 50000,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.title', 'New Project');
    }
}
```

#### Coverage Targets

- **Services:** ≥85% (critical business logic)
- **Repositories:** ≥80% (query accuracy)
- **Controllers:** ≥70% (auth + policy checks)
- **Overall:** ≥80% new code

---

## 2. Nuxt.js 3 + Vue 3 Deep Dive

### 2.1 Why Nuxt.js 3?

**Nuxt 3 Advantages:**

- ✅ **Vue 3 Composition API:** Modern, reactive, composable logic
- ✅ **File-based Routing:** Automatic route generation from pages/
- ✅ **Built-in i18n Integration:** @nuxtjs/i18n module
- ✅ **Auto-imports:** Composables, components auto-imported (zero boilerplate)
- ✅ **Server-Side Rendering Ready:** Can add SSR later without refactor
- ✅ **TypeScript Native:** First-class TS support

**Alternative Considered:** Vite + Vue 3 (SPA only)

- More lightweight but more boilerplate
- No routing, state management, i18n out of box
- Nuxt abstracts these concerns away

### 2.2 Composition API Patterns

#### Setup Script (Recommended)

```vue
<script setup lang="ts">
// Auto-imported, no need to import
const route = useRoute();
const router = useRouter();
const { $fetch } = useNuxtApp();

// Reactive state
const projects = ref<Project[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

// Computed
const projectCount = computed(() => projects.value.length);

// Lifecycle
onMounted(async () => {
  loading.value = true;
  try {
    projects.value = await $fetch("/api/v1/projects");
  } catch (e) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
});

// Methods
const createProject = async (data) => {
  // ...
};

// Expose to template (auto-exposed in setup scripts)
</script>

<template>
  <div v-if="loading" class="skeleton">Loading...</div>
  <div v-else-if="error" class="alert alert-error">{{ error }}</div>
  <UCard v-for="project in projects" :key="project.id">
    {{ project.title }}
  </UCard>
</template>
```

#### Composable Extraction

```typescript
// composables/useProjects.ts
export const useProjects = () => {
  const projects = ref<Project[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const { $fetch } = useNuxtApp();

  const fetchProjects = async () => {
    loading.value = true;
    try {
      projects.value = await $fetch("/api/v1/projects");
    } catch (e) {
      error.value = e.message;
    } finally {
      loading.value = false;
    }
  };

  const createProject = async (data: CreateProjectRequest) => {
    // ...
  };

  return {
    projects: readonly(projects),
    loading: readonly(loading),
    error: readonly(error),
    fetchProjects,
    createProject,
  };
};

// Usage in component
const { projects, loading, fetchProjects } = useProjects();
onMounted(() => fetchProjects());
```

### 2.3 Pinia State Management

#### Store Pattern

```typescript
// stores/project.ts
export const useProjectStore = defineStore("project", () => {
  // State
  const projects = ref<Project[]>([]);
  const activeProject = ref<Project | null>(null);
  const filters = ref({
    status: null,
    page: 1,
  });

  // Getters (computed)
  const projectCount = computed(() => projects.value.length);
  const activeProjectPhases = computed(() => activeProject.value?.phases || []);

  // Actions
  const setProjects = (data: Project[]) => {
    projects.value = data;
  };

  const setActiveProject = (project: Project) => {
    activeProject.value = project;
  };

  const createProject = async (data: CreateProjectRequest) => {
    const { $fetch } = useNuxtApp();
    const project = await $fetch("/api/v1/projects", {
      method: "POST",
      body: data,
    });
    projects.value.push(project);
    return project;
  };

  // Return public API
  return {
    projects,
    activeProject,
    filters,
    projectCount,
    activeProjectPhases,
    setProjects,
    setActiveProject,
    createProject,
  };
});
```

#### Store Usage in Components

```vue
<script setup lang="ts">
const projectStore = useProjectStore();

// Access state
const { projects, activeProject } = storeToRefs(projectStore);

// Dispatch actions
await projectStore.createProject(formData);

// Computed from store
const phaseCount = computed(() => activeProject.value?.phases.length ?? 0);
</script>
```

#### Best Practices

1. **Keep stores lean:** Only global state, not page-level state
2. **Use composables for reusable logic:** Separate concerns
3. **No side effects in getters:** Getters are computed properties
4. **Type your stores:** Full TypeScript support

### 2.4 Nuxt UI Component Library

#### Available Components

**Layout Components:**

- `UContainer` — Max-width container with responsive padding
- `UHeader`, `UFooter` — Layout sections
- `UNav` — Navigation menu

**Form Components:**

- `UForm` — Form wrapper with validation
- `UInput` — Text/email/password input
- `USelect` — Dropdown select
- `UCheckbox`, `URadio`, `UToggle` — Form controls
- `UTextarea` — Multi-line text

**Data Components:**

- `UTable` — Data table with sorting, pagination
- `UPagination` — Pagination control
- `UBadge` — Status/tag badges

**Feedback Components:**

- `UAlert` — Info, warning, error alerts
- `UCard` — Container card
- `UButton` — Call-to-action buttons
- `UDropdown` — Dropdown menus
- `UModal` — Dialog/modal
- `UDivider` — Section divider
- `USkeleton` — Loading skeleton

#### Theming (Vercel-Inspired)

```typescript
// nuxt.config.ts
export default defineNuxtConfig({
  modules: ["@nuxt/ui"],
  ui: {
    colors: {
      primary: "slate", // Primary action color
      gray: "slate", // Neutral color
    },
    theme: {
      // Override defaults
      override: true,
    },
  },
  tailwind: {
    // Add custom colors
    config: {
      theme: {
        extend: {
          colors: {
            // Vercel-inspired
            "geist-black": "#171717",
            "geist-white": "#ffffff",
          },
        },
      },
    },
  },
});
```

### 2.5 i18n & RTL Support

#### Configuration

```typescript
// nuxt.config.ts
export default defineNuxtConfig({
  modules: ["@nuxtjs/i18n"],
  i18n: {
    locales: [
      {
        code: "ar",
        name: "العربية",
        dir: "rtl",
        file: "locales/ar.json",
      },
      {
        code: "en",
        name: "English",
        dir: "ltr",
        file: "locales/en.json",
      },
    ],
    defaultLocale: "ar",
    strategy: "prefix_except_default", // URLs: /en/... (not /ar/...)
    fallbackLocale: "ar",
  },
});
```

#### Translation Keys

```json
{
  "common": {
    "save": "حفظ",
    "cancel": "إلغاء",
    "delete": "حذف"
  },
  "auth": {
    "login": "تسجيل الدخول",
    "email": "البريد الإلكتروني",
    "emailPlaceholder": "أدخل بريدك الإلكتروني"
  },
  "validation": {
    "required": "هذا الحقل مطلوب",
    "emailInvalid": "البريد الإلكتروني غير صحيح"
  }
}
```

#### Component Usage

```vue
<script setup lang="ts">
const { t, locale } = useI18n();
const router = useRouter();

const switchLocale = async (newLocale: string) => {
  await router.push(localePath("/", newLocale));
};
</script>

<template>
  <div :dir="locale === 'ar' ? 'rtl' : 'ltr'">
    <button @click="switchLocale('en')">English</button>
    <button @click="switchLocale('ar')">العربية</button>

    <h1>{{ t("common.welcome") }}</h1>
    <UButton>{{ t("common.save") }}</UButton>
  </div>
</template>
```

#### Tailwind Logical Properties

Instead of directional classes (ml, mr, pl, pr), use logical properties:

| Directional | Logical                        | Behavior              |
| ----------- | ------------------------------ | --------------------- |
| `ml-4`      | `ms-4` (margin-inline-start)   | LTR: left, RTL: right |
| `mr-4`      | `me-4` (margin-inline-end)     | LTR: right, RTL: left |
| `pl-6`      | `ps-6` (padding-inline-start)  | LTR: left, RTL: right |
| `pr-6`      | `pe-6` (padding-inline-end)    | LTR: right, RTL: left |
| `left-0`    | `start-0` (inset-inline-start) | LTR: left, RTL: right |
| `right-0`   | `end-0` (inset-inline-end)     | LTR: right, RTL: left |

```vue
<template>
  <!-- ❌ WRONG: Will not flip in RTL -->
  <div class="ml-4 mr-8 pl-6">Content</div>

  <!-- ✅ CORRECT: Will flip automatically -->
  <div class="ms-4 me-8 ps-6">Content</div>
</template>
```

### 2.6 Nuxt Testing

#### Unit Tests (Vitest)

```typescript
// tests/unit/composables/useProjects.test.ts
import { describe, it, expect, beforeEach, vi } from "vitest";
import { useProjects } from "~/composables/useProjects";

describe("useProjects", () => {
  it("should fetch projects", async () => {
    const { $fetch } = useNuxtApp();
    vi.mocked($fetch).mockResolvedValue([{ id: 1, title: "Project 1" }]);

    const { projects, fetchProjects } = useProjects();
    await fetchProjects();

    expect(projects.value).toHaveLength(1);
    expect(projects.value[0].title).toBe("Project 1");
  });
});
```

#### Component Tests (Vitest + Vue Test Utils)

```typescript
// tests/components/LoginForm.test.ts
import { describe, it, expect } from "vitest";
import { mount } from "@vue/test-utils";
import LoginForm from "~/components/LoginForm.vue";

describe("LoginForm", () => {
  it("renders email and password inputs", () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('input[type="email"]').exists()).toBe(true);
    expect(wrapper.find('input[type="password"]').exists()).toBe(true);
  });

  it("disables submit button when form empty", () => {
    const wrapper = mount(LoginForm);
    expect(
      wrapper.find('button[type="submit"]').attributes("disabled"),
    ).toBeDefined();
  });

  it("emits submit event with form data", async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="email"]').setValue("test@example.com");
    await wrapper.find('input[type="password"]').setValue("password");
    await wrapper.find("form").trigger("submit");

    expect(wrapper.emitted("submit")).toBeTruthy();
  });
});
```

---

## 3. Testing Frameworks Comparison

### 3.1 Backend: PHPUnit vs. Pest

| Factor                  | PHPUnit                    | Pest                          |
| ----------------------- | -------------------------- | ----------------------------- |
| **Syntax**              | OOP: `$this->assert*(...)` | DSL: `expect(...)->toBe(...)` |
| **Test Discovery**      | By convention              | By convention                 |
| **Fixtures**            | setUp/tearDown             | Closures, global setup        |
| **Parallel Testing**    | Via plugin                 | Built-in                      |
| **Laravel Integration** | Native (Laravel default)   | Wrapper around PHPUnit        |
| **Community**           | Larger, mature             | Growing, modern               |
| **Setup for Bunyan**    | Lower (already in Laravel) | Higher (wrapper)              |

**Decision:** **PHPUnit 11.x** (default in Laravel 11) — No setup cost, mature ecosystem

### 3.2 Frontend: Vitest vs. Jest

| Factor          | Vitest                       | Jest                      |
| --------------- | ---------------------------- | ------------------------- |
| **Speed**       | Very fast (Vite-native)      | Moderate (slower startup) |
| **Config**      | Minimal (Vite reuses config) | More setup required       |
| **Vue Support** | Native (Vite ecosystem)      | Requires setup            |
| **ESM Support** | Full native                  | Via workarounds           |
| **Community**   | Growing                      | Mature, larger            |
| **Coverage**    | Via c8                       | Built-in                  |

**Decision:** **Vitest** (faster, Vite-native, less config)

### 3.3 E2E Testing: Playwright

**Why Playwright?**

- ✅ **Modern API:** Async/await, easy readability
- ✅ **Multi-browser:** Chromium, Firefox, WebKit
- ✅ **Parallel Execution:** Built-in
- ✅ **Screenshots/Videos:** Debugging support
- ✅ **Network Control:** Mock API responses

**Configuration:**

```typescript
// playwright.config.ts
export default defineConfig({
  testDir: "./tests/e2e",
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: "html",
  use: {
    baseURL: "http://localhost:3000",
    trace: "on-first-retry",
    screenshot: "only-on-failure",
  },
});
```

---

## 4. CI/CD Platform: GitHub Actions

### 4.1 Why GitHub Actions?

**Advantages:**

- ✅ **Free for Public Repos:** No per-minute costs
- ✅ **Native to GitHub:** Repo-integrated, no external setup
- ✅ **Matrix Builds:** Test across multiple Node/PHP versions
- ✅ **Artifact Storage:** Built-in cache, artifact upload
- ✅ **Secrets Management:** Environment variables encrypted

**Alternative Considered:** GitLab CI, CircleCI

- GitLab CI: Good but overkill for this project
- CircleCI: Paid, unnecessary for open source

### 4.2 Pre-Commit Guard Workflow

**Goal:** Validate all code before merge

```yaml
# .github/workflows/pre-commit-guard.yml
name: Pre-Commit Guard
on:
  pull_request:
    branches: [develop, main]
  push:
    branches: [develop, main]

jobs:
  backend-lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
      - run: composer install --no-interaction --no-progress
      - run: ./vendor/bin/pint --test

  backend-analyze:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
      - run: composer install --no-interaction --no-progress
      - run: ./vendor/bin/phpstan analyse --memory-limit=512M

  backend-test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: bunyan
          MYSQL_ROOT_PASSWORD: root
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
      - run: cp backend/ci.env backend/.env
      - run: cd backend && composer install
      - run: cd backend && php artisan migrate
      - run: cd backend && php artisan test --coverage

  frontend-lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: "20"
      - run: cd frontend && npm install
      - run: cd frontend && npm run lint

  frontend-typecheck:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: "20"
      - run: cd frontend && npm install
      - run: cd frontend && npm run typecheck

  frontend-test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: "20"
      - run: cd frontend && npm install
      - run: cd frontend && npm run test

  e2e-test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: bunyan
          MYSQL_ROOT_PASSWORD: root
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: "20"
      - uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
      - run: cd backend && composer install && php artisan migrate
      - run: cd frontend && npm install
      - run: cd backend && php artisan serve &
      - run: cd frontend && npm run dev &
      - run: sleep 5
      - run: cd frontend && npm run test:e2e
```

### 4.3 Artifact & Caching Strategy

**Cache Dependencies:**

```yaml
- uses: actions/cache@v4
  with:
    path: |
      backend/vendor
      frontend/node_modules
    key: ${{ runner.os }}-composer-${{ hashFiles('backend/composer.lock') }}-npm-${{ hashFiles('frontend/package-lock.json') }}
```

**Upload Coverage Reports:**

```yaml
- name: Upload coverage to Codecov
  uses: codecov/codecov-action@v3
  with:
    files: ./backend/coverage.xml,./frontend/coverage/coverage-final.json
    flags: backend,frontend
```

---

## 5. Docker Compose Service Orchestration

### 5.1 Local Development Stack

```yaml
# docker-compose.yml
version: "3.8"

services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: bunyan
      MYSQL_ROOT_PASSWORD: root
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      timeout: 5s
      retries: 5

  redis:
    image: redis:7
    ports:
      - "6379:6379"
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      timeout: 5s
      retries: 5

volumes:
  mysql_data:
```

### 5.2 Development Workflow

**With Docker:**

```bash
# Start services
docker-compose up -d

# Run Laravel
docker-compose exec php php artisan serve

# Run Nuxt
docker-compose exec node npm run dev

# Run migrations
docker-compose exec php php artisan migrate
```

**Without Docker (Local Setup):**

```bash
# Install MySQL locally
brew install mysql@8.0

# Install Redis locally
brew install redis

# Start services
mysql.server start
redis-server

# Run Laravel
php artisan serve

# Run Nuxt
npm run dev
```

---

## 6. Recommended Dependency Versions

### Backend (Laravel 11.x)

| Package             | Version | Purpose                                       |
| ------------------- | ------- | --------------------------------------------- |
| `laravel/framework` | ^11.0   | Core framework                                |
| `laravel/sanctum`   | ^4.0    | API authentication                            |
| `laravel/tinker`    | ^2.0    | REPL                                          |
| `phpunit/phpunit`   | ^11.0   | Testing                                       |
| `phpstan/phpstan`   | ^1.0    | Static analysis                               |
| `laravel/pint`      | ^1.14   | Code formatting (Laravel preset; `pint.json`) |
| `laravel/pint`      | ^1.0    | Code styling                                  |

### Frontend (Nuxt 3)

| Package            | Version | Purpose              |
| ------------------ | ------- | -------------------- |
| `nuxt`             | ^3.12   | Core framework       |
| `vue`              | ^3.0    | Reactive framework   |
| `@nuxt/ui`         | ^2.0    | Component library    |
| `@nuxtjs/i18n`     | ^8.0    | Internationalization |
| `pinia`            | ^2.0    | State management     |
| `tailwindcss`      | ^4.0    | Utility CSS          |
| `vitest`           | ^1.0    | Testing framework    |
| `@vue/test-utils`  | ^2.0    | Component testing    |
| `@playwright/test` | ^1.0    | E2E testing          |

---

## 7. Known Limitations & Workarounds

### Laravel

**Limitation:** Eloquent ORM lazy-loads by default (N+1 queries)  
**Workaround:** Always use `with()` eager loading in queries

**Limitation:** Migrations are forward-only (can't modify after migration)  
**Workaround:** Create new migration for schema changes, keep old migrations

### Nuxt

**Limitation:** Auto-imports can cause naming conflicts  
**Workaround:** Use explicit imports, or namespace auto-imports in nuxt.config

**Limitation:** RTL layouts require logical CSS properties (not all CSS supports them)  
**Workaround:** Use Tailwind logical properties, test both LTR and RTL renders

### GitHub Actions

**Limitation:** Free tier has ~6000 minutes/month per user  
**Workaround:** Cache dependencies, run tests in parallel

---

## 8. Performance Benchmarks

### Backend Targets

| Metric                                   | Target | Current Baseline |
| ---------------------------------------- | ------ | ---------------- |
| API response time (GET /api/v1/projects) | <200ms | TBD              |
| Auth endpoint (POST /api/v1/auth/login)  | <300ms | TBD              |
| Database query time (N+1 fixed)          | <50ms  | TBD              |
| Test suite duration (full)               | <3 min | TBD              |

### Frontend Targets

| Metric                | Target | Current Baseline |
| --------------------- | ------ | ---------------- |
| First Paint           | <1.5s  | TBD              |
| Time to Interactive   | <3s    | TBD              |
| Bundle size (gzipped) | <200KB | TBD              |
| Test suite duration   | <1 min | TBD              |

---

**Generated by:** PLAN Step  
**Status:** READY FOR REFERENCE  
**Last Updated:** 2026-04-10
