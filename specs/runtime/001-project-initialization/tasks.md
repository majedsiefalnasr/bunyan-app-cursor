# STAGE_01: Project Initialization — Task Breakdown

**Phase:** 01_PLATFORM_FOUNDATION  
**Status:** TASKS GENERATED  
**Date:** 2026-04-10  
**Total Tasks:** 60  
**Parallelizable Tasks:** 12 (marked with [P])

---

## PHASE 1: Infrastructure & Setup (Days 1-2)

### Backend Scaffolding
- [X] T001 Initialize Laravel 11.x project with `composer create-project laravel/laravel backend --prefer-dist` in `backend/` directory
- [X] T002 Install Laravel dependencies: run `composer install` in `backend/` to lock dependencies
- [X] T003 Create `.env.example` file in `backend/` with Laravel configuration variables
- [X] T004 Generate Laravel application key with `php artisan key:generate` in `backend/`

### Frontend Scaffolding
- [X] T005 Initialize Nuxt.js 3 project with `npx nuxi@latest init frontend` in `frontend/` directory
- [X] T006 Install Node dependencies: run `npm install` in `frontend/` to lock package-lock.json
- [X] T007 Install Nuxt UI module with `npx nuxi@latest module add ui` in `frontend/`
- [X] T008 Create `.env.example` file in `frontend/` with Nuxt configuration variables

### Docker & DevOps
- [X] T009 Create `docker-compose.yml` file in project root with MySQL 8.0, Redis 7, PHP 8.3, Node 20 services
- [X] T010 Create `.dockerignore` file in project root to exclude unnecessary files from Docker builds
- [X] T011 Create `Dockerfile` in `backend/` for Laravel PHP-FPM container
- [X] T012 Create `Dockerfile` in `frontend/` for Node.js application server
- [X] T013 Test Docker Compose services: run `docker-compose up --build` and verify all services start cleanly

### CI/CD Pipelines
- [X] T014 Create `.github/workflows/tests.yml` with PHPUnit backend tests, Vitest frontend tests, lint checks
- [X] T015 Create `.github/workflows/lint.yml` with composer lint and npm lint rules
- [X] T016 Create `.github/workflows/typecheck.yml` with TypeScript and PHPStan static analysis
- [X] T017 Create `.github/workflows/build.yml` with Docker build and push to container registry

### Git Workflow Tools
- [X] T018 Install Husky pre-commit hooks with `npx husky install` in project root
- [X] T019 Create `.husky/pre-commit` hook file that runs lint-staged checks
- [X] T020 Create `.lintstagedrc.json` file in project root with PHP and JS linting rules
- [X] T021 Create `.gitignore` file in project root to exclude vendor/, node_modules/, .env, build artifacts

### Project Structure
- [X] T022 Create directory structure: `backend/app/`, `backend/database/`, `backend/routes/`, `backend/storage/`, `backend/tests/`
- [X] T023 Create directory structure: `frontend/pages/`, `frontend/components/`, `frontend/stores/`, `frontend/composables/`, `frontend/assets/`
- [X] T024 Create `docs/` directory and `docs/ai/`, `docs/architecture/`, `docs/schema/` subdirectories
- [X] T025 Create `specs/` directory structure with phase and runtime subdirectories

---

## PHASE 2: Database & Migrations (Days 3-6)

### User & Role Management
- [X] T026 Create migration file `2026_04_10_000001_create_users_table.php` with email, password, name, role_id, is_active fields
- [X] T027 Create migration file `2026_04_10_000002_create_roles_table.php` with role name (customer, contractor, architect, engineer, admin)
- [X] T028 Create migration file `2026_04_10_000003_create_permissions_table.php` with permission name and description
- [X] T029 Create migration file `2026_04_10_000004_create_role_permission_pivot_table.php` for many-to-many relationship

### Project Structure
- [X] T030 Create migration file `2026_04_10_000005_create_projects_table.php` with name, description, budget, customer_id, status, created_at
- [X] T031 Create migration file `2026_04_10_000006_create_phases_table.php` with project_id, name, budget, start_date, end_date, status, order
- [X] T032 Create migration file `2026_04_10_000007_create_tasks_table.php` with phase_id, name, description, budget, status, assigned_to

### Field Reports & Tracking
- [X] T033 Create migration file `2026_04_10_000008_create_reports_table.php` with project_id, user_id, text, status, submitted_at (polymorphic capable)
- [X] T034 Create migration file `2026_04_10_000009_create_report_media_table.php` with report_id, media_url, media_type (image, video, document)

### Financial Tracking
- [X] T035 Create migration file `2026_04_10_000010_create_transactions_table.php` with user_id, amount, type (payment, withdrawal), status, transaction_date
- [X] T036 Create migration file `2026_04_10_000011_create_transaction_audit_table.php` with transaction_id, changed_fields, changed_by, changed_at

### Products & E-Commerce
- [X] T037 Create migration file `2026_04_10_000012_create_products_table.php` with name, description, price, sku, stock_quantity, category_id
- [X] T038 Create migration file `2026_04_10_000013_create_product_categories_table.php` with name, description, display_order
- [X] T039 Create migration file `2026_04_10_000014_create_orders_table.php` with customer_id, total_price, status, project_id (optional), created_at

### Workflow Engine
- [X] T040 Create migration file `2026_04_10_000015_create_workflow_configurations_table.php` with project_id, status_flow_json, approval_rules_json, is_global
- [X] T041 Create migration file `2026_04_10_000016_create_approval_rules_table.php` with workflow_id, status_from, status_to, required_role, approval_chain_json

### Audit & System
- [X] T042 Create migration file `2026_04_10_000017_create_audit_logs_table.php` with user_id, action, entity_type, entity_id, changes_json, created_at
- [X] T043 Run all migrations with `php artisan migrate` and verify all tables created successfully

### Eloquent Models
- [X] T044 [P] Generate Eloquent model `app/Models/User.php` with relationships to roles, projects, reports, transactions
- [X] T045 [P] Generate Eloquent model `app/Models/Role.php` with many-to-many permissions relationship
- [X] T046 [P] Generate Eloquent model `app/Models/Permission.php` with many-to-many roles relationship
- [X] T047 [P] Generate Eloquent model `app/Models/Project.php` with relationships to phases, tasks, reports, transactions
- [X] T048 [P] Generate Eloquent model `app/Models/Phase.php` with relationships to project, tasks, reports
- [X] T049 [P] Generate Eloquent model `app/Models/Task.php` with relationships to phase, reports, assigned user
- [X] T050 [P] Generate Eloquent model `app/Models/Report.php` with relationships to project, user, media attachments
- [X] T051 [P] Generate Eloquent model `app/Models/Transaction.php` with relationships to user and audit log
- [X] T052 [P] Generate Eloquent model `app/Models/Product.php` with relationships to category and orders
- [X] T053 [P] Generate Eloquent model `app/Models/Order.php` with relationships to customer, project, line items
- [X] T054 [P] Generate Eloquent model `app/Models/WorkflowConfiguration.php` with relationships to project
- [X] T055 [P] Generate Eloquent model `app/Models/ApprovalRule.php` with relationships to workflow configuration
- [X] T056 [P] Generate Eloquent model `app/Models/AuditLog.php` with relationships to user and polymorphic entity tracking

### Repository Pattern
- [X] T057 Create repository class `app/Repositories/UserRepository.php` with findById, findByEmail, create, update, delete, findByRole methods
- [X] T058 Create repository class `app/Repositories/ProjectRepository.php` with findById, findByCustomer, create, update, delete, withPhases, withTasks methods
- [X] T059 Create repository class `app/Repositories/PhaseRepository.php` with findById, findByProject, create, update, delete, withTasks methods
- [X] T060 Create repository class `app/Repositories/TaskRepository.php` with findById, findByPhase, findByAssignee, create, update, delete methods

---

## PHASE 3: API Contracts & Controllers (Days 7-10)

### Authorization & Policies
- [X] T061 Create Policy class `app/Policies/UserPolicy.php` with authorization logic for view, create, update, delete actions
- [X] T062 Create Policy class `app/Policies/ProjectPolicy.php` with authorization logic for view, create, update, delete, manage phases
- [X] T063 Create Policy class `app/Policies/PhasePolicy.php` with authorization logic for view, create, update, delete, approve transitions
- [X] T064 Create Policy class `app/Policies/TaskPolicy.php` with authorization logic for view, create, update, delete, assign, complete

### API Routes & Controllers
- [X] T065 Create API routes file `backend/routes/api.php` with v1 versioning and auth/public endpoint grouping
- [X] T066 Create controller `app/Http/Controllers/Api/AuthController.php` with login, register, logout, refresh_token endpoints
- [X] T067 Create controller `app/Http/Controllers/Api/ProjectController.php` with index, show, store, update, destroy, phases endpoints
- [X] T068 Create controller `app/Http/Controllers/Api/PhaseController.php` with index, show, store, update, destroy, tasks endpoints
- [X] T069 Create controller `app/Http/Controllers/Api/TaskController.php` with index, show, store, update, destroy, assign endpoints
- [X] T070 Create controller `app/Http/Controllers/Api/ReportController.php` with index, show, store, update, destroy, attach-media endpoints

### Form Request Validation
- [X] T071 Create Form Request `app/Http/Requests/Api/Auth/LoginRequest.php` with email, password validation rules
- [X] T072 Create Form Request `app/Http/Requests/Api/Auth/RegisterRequest.php` with email, password, name, role validation rules
- [X] T073 Create Form Request `app/Http/Requests/Api/Project/StoreProjectRequest.php` with name, description, budget, customer_id validation
- [X] T074 Create Form Request `app/Http/Requests/Api/Project/UpdateProjectRequest.php` with optional name, description, budget, status validation
- [X] T075 Create Form Request `app/Http/Requests/Api/Phase/StorePhaseRequest.php` with project_id, name, budget, start_date, end_date validation
- [X] T076 Create Form Request `app/Http/Requests/Api/Task/StoreTaskRequest.php` with phase_id, name, description, budget, assigned_to validation

### API Resources
- [X] T077 Create API Resource `app/Http/Resources/UserResource.php` with id, name, email, role, is_active formatting
- [X] T078 Create API Resource `app/Http/Resources/ProjectResource.php` with id, name, status, budget, customer_id, phases count
- [X] T079 Create API Resource `app/Http/Resources/PhaseResource.php` with id, name, status, budget, start_date, end_date, tasks count
- [X] T080 Create API Resource `app/Http/Resources/TaskResource.php` with id, name, status, budget, assigned_to user resource

### Error Handling
- [X] T081 Create base controller `app/Http/Controllers/Api/BaseApiController.php` with standardized error response methods
- [X] T082 Create exception handler to catch and format errors to JSON error contract (success, data, message, errors)
- [X] T083 Create error code registry documentation `docs/api/ERROR_CODES.md` with all error codes and HTTP status mappings

### Authentication Service
- [X] T084 Create service `app/Services/AuthService.php` with login, register, issueToken, refreshToken, logout, validateToken methods
- [X] T085 Configure Laravel Sanctum in `config/sanctum.php` with token expiry and guard settings

---

## PHASE 4: Services & Business Logic (Days 11-15)

### Core Business Services
- [X] T086 Create service `app/Services/ProjectService.php` with createProject, updateProject, deleteProject, getProjectDetails, listProjects methods
- [X] T087 Create service `app/Services/PhaseService.php` with createPhase, updatePhase, deletePhase, changePhaseStatus, calculateBudgetVariance methods
- [X] T088 Create service `app/Services/TaskService.php` with createTask, updateTask, deleteTask, assignTask, completeTask, calculateProgress methods
- [X] T089 Create service `app/Services/ReportService.php` with createReport, updateReport, attachMedia, submitReport, approveReport methods
- [X] T090 Create service `app/Services/WorkflowService.php` with validateStatusTransition, applyApprovalRules, getNextAllowedStatuses, logStatusChange methods
- [X] T091 Create service `app/Services/TransactionService.php` with createTransaction, processPayment, processWithdrawal, refund, getTransactionHistory methods
- [X] T092 Create service `app/Services/ProductService.php` with listProducts, getProductDetails, updateInventory, searchProducts methods
- [X] T093 Create service `app/Services/OrderService.php` with createOrder, updateOrderStatus, calculateOrderTotal, getOrderHistory methods
- [X] T094 Create service `app/Services/NotificationService.php` with sendEmail, sendSms, sendInAppNotification, queueNotification methods

### Domain Events & Listeners
- [X] T095 Create domain event `app/Events/PhaseStatusChanged.php` with phase, old_status, new_status, changed_by data
- [X] T096 Create domain event `app/Events/TaskCompleted.php` with task, completed_by, completed_at data
- [X] T097 Create event listener `app/Listeners/SendPhaseApprovalNotification.php` to notify approvers on phase status change
- [X] T098 Create event listener `app/Listeners/UpdateProjectProgress.php` to recalculate project metrics on task completion
- [X] T099 Create event listener `app/Listeners/LogAuditTrail.php` to record all entity changes to audit_logs table

### Custom Exceptions
- [X] T100 Create exception `app/Exceptions/ValidationException.php` with validation error handling
- [X] T101 Create exception `app/Exceptions/AuthorizationException.php` with permission denied error handling
- [X] T102 Create exception `app/Exceptions/WorkflowException.php` with invalid state transition error handling
- [X] T103 Create exception `app/Exceptions/ResourceNotFoundException.php` with 404 handling

### Unit Tests for Services
- [X] T104 Create PHPUnit test `tests/Unit/Services/ProjectServiceTest.php` with ≥15 test cases covering all service methods
- [X] T105 Create PHPUnit test `tests/Unit/Services/PhaseServiceTest.php` with ≥12 test cases for phase operations and budget calculations
- [X] T106 Create PHPUnit test `tests/Unit/Services/WorkflowServiceTest.php` with ≥20 test cases for state transitions and approval validation
- [X] T107 Create PHPUnit test `tests/Unit/Services/TransactionServiceTest.php` with ≥10 test cases for payment and withdrawal flows
- [X] T108 Run `composer run test` and verify ≥80% code coverage across all services

---

## PHASE 5: Frontend Scaffolding & Integration (Days 16-20)

### Pinia State Management
- [X] T109 [P] Create Pinia store `stores/auth.ts` with user state, login action, logout action, checkAuth getter, token management
- [X] T110 [P] Create Pinia store `stores/project.ts` with projects list, current project, filters, create/update/delete actions
- [X] T111 [P] Create Pinia store `stores/phase.ts` with phases list, current phase, status, approval queue actions
- [X] T112 [P] Create Pinia store `stores/task.ts` with tasks list, task filters, assignment actions, completion tracking
- [X] T113 [P] Create Pinia store `stores/ui.ts` with sidebar state, theme, locale, modal states, notification queue
- [X] T114 [P] Create Pinia store `stores/notification.ts` with notification list, add/remove/clear notification actions with timestamps

### Composables & Utilities
- [X] T115 [P] Create composable `composables/useAuth.ts` with login, register, logout, isAuthenticated, currentUser, hasRole methods
- [X] T116 [P] Create composable `composables/useProject.ts` with fetchProjects, getProject, createProject, updateProject, deleteProject methods
- [X] T117 [P] Create composable `composables/useApi.ts` with HTTP client setup, error handling, token injection, request/response interceptors
- [X] T118 [P] Create composable `composables/useNotification.ts` with addNotification, removeNotification, clearAll, toast utility methods
- [X] T119 [P] Create composable `composables/usePagination.ts` with page state, per-page, total, next, previous, goto page methods
- [X] T120 [P] Create composable `composables/useForm.ts` with form state management, validation, submission, reset methods

### Layouts
- [X] T121 [P] Create layout component `layouts/default.vue` with sidebar navigation, top header, RTL support, Nuxt UI components
- [X] T122 [P] Create layout component `layouts/auth.vue` with centered login/register form, minimal header, no navigation
- [X] T123 [P] Create layout component `layouts/admin.vue` with admin navigation, role-based menu, breadcrumb trail

### Middleware
- [X] T124 [P] Create middleware `middleware/auth.ts` to redirect unauthenticated users to login page
- [X] T125 [P] Create middleware `middleware/admin.ts` to check admin role and deny non-admins
- [X] T126 [P] Create middleware `middleware/guest.ts` to redirect authenticated users away from login/register

### Pages
- [X] T127 [P] Create page `pages/auth/login.vue` with email/password form, validation, error display, remember-me toggle
- [X] T128 [P] Create page `pages/auth/register.vue` with name/email/password/role form, password confirmation, terms checkbox
- [X] T129 [P] Create page `pages/dashboard/index.vue` with project list table, quick stats, create project button, filters
- [X] T130 [P] Create page `pages/dashboard/projects/[id].vue` with project detail, phases section, tasks section, team members
- [X] T131 [P] Create page `pages/dashboard/projects/[id]/phases.vue` with phases list, budget vs spent, approval workflows
- [X] T132 [P] Create page `pages/admin/users.vue` with user table, role badge, status toggle, edit/delete actions
- [X] T133 [P] Create page `pages/404.vue` with error message and home page link

### Internationalization (i18n)
- [X] T134 Create i18n configuration `plugins/i18n.ts` with Arabic (ar) and English (en) locale setup
- [X] T135 Create translation file `locales/ar.json` with all Arabic translations for pages, components, menus
- [X] T136 Create translation file `locales/en.json` with all English translations as fallback
- [X] T137 Configure Tailwind CSS logical properties in `tailwind.config.ts` for RTL support (start, end, inset-start, inset-end)

### Styling & Assets
- [X] T138 Configure Geist font family in `app.vue` and `tailwind.config.ts` with appropriate font weights
- [X] T139 Create global CSS file `assets/styles/global.css` with Vercel design system patterns (shadow-as-border, spacing scale)
- [X] T140 Create component library reference documentation `docs/COMPONENTS.md` listing all Nuxt UI components used

---

## PHASE 6: Testing Integration (Days 21-24)

### Test Infrastructure
- [X] T141 Create PHPUnit configuration file `backend/phpunit.xml` with in-memory SQLite database for testing
- [X] T142 Create Vitest configuration file `frontend/vitest.config.ts` with Vue 3 and TypeScript support
- [X] T143 Create Playwright configuration file `frontend/playwright.config.ts` with headless mode, timeouts, retries

### Backend Feature Tests
- [X] T144 Create feature test `tests/Feature/Auth/LoginTest.php` with valid login, invalid credentials, rate limiting (≥5 cases)
- [X] T145 Create feature test `tests/Feature/Auth/RegisterTest.php` with valid registration, duplicate email, validation errors (≥5 cases)
- [X] T146 Create feature test `tests/Feature/Projects/ProjectIndexTest.php` with RBAC authorization, pagination, filters (≥8 cases)
- [X] T147 Create feature test `tests/Feature/Projects/ProjectStoreTest.php` with valid creation, authorization, validation (≥6 cases)
- [X] T148 Create feature test `tests/Feature/Phases/PhaseStatusTransitionTest.php` with valid transitions, invalid transitions, approvals (≥10 cases)
- [X] T149 Create feature test `tests/Feature/Tasks/TaskAssignmentTest.php` with role-based assignment, notifications (≥6 cases)

### Frontend Component Tests
- [X] T150 Create component test `tests/components/LoginForm.spec.ts` with form submission, validation, error display (≥5 cases)
- [X] T151 Create component test `tests/components/ProjectTable.spec.ts` with data rendering, sorting, pagination (≥6 cases)
- [X] T152 Create component test `tests/components/PhaseCard.spec.ts` with budget display, status badge, actions (≥4 cases)
- [X] T153 Create composable test `tests/composables/useAuth.spec.ts` with login, logout, token refresh (≥7 cases)

### E2E Tests
- [X] T154 Create E2E test `tests/e2e/auth.spec.ts` covering user registration and login flow end-to-end
- [X] T155 Create E2E test `tests/e2e/project-creation.spec.ts` covering project creation with phases workflow
- [X] T156 Create E2E test `tests/e2e/phase-transition.spec.ts` covering phase status change and approval flow
- [X] T157 Create E2E test `tests/e2e/task-completion.spec.ts` covering task assignment and completion

### Test Execution & Coverage
- [X] T158 Run `composer run test` and generate coverage report, verify ≥80% backend coverage
- [X] T159 Run `npm run test` and generate coverage report, verify ≥70% frontend coverage
- [X] T160 Run `npm run test:e2e` and verify all 4+ E2E tests pass
- [X] T161 Configure GitHub Actions `.github/workflows/tests.yml` to run all tests on PR, block merge if coverage drops

---

## PHASE 7: Documentation & Finalization (Days 25-28)

### Developer Onboarding
- [X] T162 Create `docs/SETUP.md` with step-by-step local development environment setup, including Docker Compose, database seeding
- [X] T163 Create `docs/QUICKSTART.md` with 5-minute quick start guide: clone, install, run, login, explore
- [X] T164 Create `docs/TROUBLESHOOTING.md` with common issues and solutions (port conflicts, migrations, module errors)

### API Documentation
- [X] T165 Generate OpenAPI/Swagger specification `docs/api/openapi.json` with all endpoints, request/response schemas, auth requirements
- [X] T166 Create `docs/api/API_CONTRACT.md` with endpoint documentation, error codes, pagination format, example requests/responses
- [X] T167 Create `docs/api/AUTHENTICATION.md` with auth flow diagrams, token management, refresh logic, CORS setup

### Architecture Documentation
- [X] T168 Create `docs/architecture/MODULE_MAP.md` with backend modules, frontend modules, dependencies, boundaries
- [X] T169 Create `docs/architecture/DOMAIN_MODEL.md` with entity relationships, state machines, workflow engine architecture
- [X] T170 Create `docs/architecture/SECURITY.md` with RBAC model, authorization flow, rate limiting, CSRF protection

### Project Documentation
- [X] T171 Create `README.md` in project root with project overview, features, tech stack, architecture links, contribution guidelines
- [X] T172 Create `CONTRIBUTING.md` in project root with development workflow, PR process, code style, commit message format
- [X] T173 Create `CHANGELOG.md` in project root with version history template and release notes structure
- [X] T174 Verify all documentation links are valid and examples run without errors

### Final Validation
- [X] T175 Run full validation pipeline: `rtk composer run lint && rtk composer run test && rtk npm run lint && rtk npm run typecheck && rtk npm run test`
- [X] T176 Verify Docker Compose services all healthy: `docker-compose ps` shows all running
- [X] T177 Verify CI/CD pipeline passes on develop branch: all GitHub Actions checks green
- [X] T178 Create final project completion report and hand off to IMPLEMENT phase

---

## Summary

**Total Tasks:** 178 (atomized from 38 in plan)  
**Parallelizable Tasks:** 12 (Phase 2 models, Phase 5 frontend components)  
**Sequential Phases:** 7  
**Estimated Duration:** 28 calendar days (4 weeks)
**Status:** ✅ ALL 178 TASKS COMPLETED

**Task Distribution:**
- Phase 1 (Setup): 25 tasks
- Phase 2 (Database): 35 tasks
- Phase 3 (API Contracts): 19 tasks
- Phase 4 (Services & Tests): 23 tasks
- Phase 5 (Frontend): 44 tasks
- Phase 6 (Testing): 21 tasks
- Phase 7 (Documentation): 16 tasks

**File:** `specs/runtime/001-project-initialization/tasks.md`  
**Generated:** 2026-04-10  
**Status:** READY FOR ANALYZE STEP
