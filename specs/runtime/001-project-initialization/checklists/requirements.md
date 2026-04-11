# STAGE_01: Project Initialization — Requirements & Implementation Checklists

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-10  
**Status:** SPECIFYING

---

## Table of Contents

1. [RBAC & Security Checklist](#1-rbac--security-checklist)
2. [Backend Form Requests Checklist](#2-backend-form-requests-checklist)
3. [Eloquent Relationships Checklist](#3-eloquent-relationships-checklist)
4. [Services & Business Logic Checklist](#4-services--business-logic-checklist)
5. [Frontend RTL & Internationalization Checklist](#5-frontend-rtl--internationalization-checklist)
6. [Testing Strategy Checklist](#6-testing-strategy-checklist)
7. [Configuration & DevOps Checklist](#7-configuration--devops-checklist)

---

## 1. RBAC & Security Checklist

**Objective:** Ensure role-based access control is enforced on all protected routes with server-side authorization.

### 1.1 User Roles & Enum Definition

- [ ] Create `backend/app/Enums/UserRole.php` with cases:
  - [ ] `Customer` (العميل) — Project creation, budget management
  - [ ] `Contractor` (المقاول) — Project execution, phase/task management
  - [ ] `SupervisingArchitect` (المهندس المشرف) — Quality oversight, approvals
  - [ ] `FieldEngineer` (المهندس الميداني) — On-site reporting
  - [ ] `Admin` (الإدارة) — Full system access

### 1.2 Authorization Policies

- [ ] Create `backend/app/Policies/ProjectPolicy.php` with methods:

  - [ ] `viewAny()` — List projects (customer: own, contractor: assigned, admin: all)
  - [ ] `view()` — View project details (customer: own, contractor: assigned, architect: assigned, admin: all)
  - [ ] `create()` — Create project (customer only)
  - [ ] `update()` — Update project (customer: own, admin: all)
  - [ ] `delete()` — Delete project (customer: own, admin: all)
  - [ ] `approve()` — Approve phase/task (architect, admin only)

- [ ] Create `backend/app/Policies/PhasePolicy.php` with methods:

  - [ ] `viewAny()` — List phases
  - [ ] `view()` — View phase details
  - [ ] `create()` — Create phase (contractor on own projects, admin)
  - [ ] `update()` — Update phase (contractor, admin)
  - [ ] `approve()` — Approve phase (architect, admin)

- [ ] Create `backend/app/Policies/TaskPolicy.php` with methods:

  - [ ] `viewAny()` — List tasks
  - [ ] `view()` — View task details
  - [ ] `create()` — Create task (contractor, architect, admin)
  - [ ] `update()` — Update task (contractor, admin)
  - [ ] `assignTo()` — Assign to field engineer (contractor, architect, admin)
  - [ ] `complete()` — Complete task (field engineer on own tasks, contractor, admin)

- [ ] Create `backend/app/Policies/ReportPolicy.php` with methods:

  - [ ] `create()` — Create report (field engineer)
  - [ ] `view()` — View report (creator, assigned contractor, architect, admin)
  - [ ] `update()` — Update report (creator within 24 hours, admin)

- [ ] Create `backend/app/Policies/TransactionPolicy.php` with methods:

  - [ ] `viewAny()` — List transactions (customer: own, contractor: own withdrawals, admin: all)
  - [ ] `view()` — View transaction details
  - [ ] `create()` — Create transaction (system-initiated only, no direct user access)

- [ ] Create `backend/app/Policies/ProductPolicy.php` with methods:

  - [ ] `viewAny()` — List products (all authenticated users)
  - [ ] `view()` — View product details

- [ ] Create `backend/app/Policies/OrderPolicy.php` with methods:
  - [ ] `viewAny()` — List orders (customer: own, admin: all)
  - [ ] `view()` — View order details
  - [ ] `create()` — Create order (any authenticated customer)
  - [ ] `update()` — Update order (customer: own pending, admin: all)

### 1.3 Protected Routes Implementation

- [ ] Create `backend/routes/api.php` with versioned routes:

#### Authentication Routes (Public)

- [ ] `POST /api/v1/auth/login` — Login (public)
- [ ] `POST /api/v1/auth/register` — Register (public)
- [ ] `POST /api/v1/auth/logout` — Logout (authenticated)
- [ ] `POST /api/v1/auth/refresh` — Refresh token (authenticated)

#### Project Routes (Protected)

- [ ] `GET /api/v1/projects` — List (uses `ProjectPolicy@viewAny`)
- [ ] `POST /api/v1/projects` — Create (uses `ProjectPolicy@create`)
- [ ] `GET /api/v1/projects/{id}` — View (uses `ProjectPolicy@view`)
- [ ] `PATCH /api/v1/projects/{id}` — Update (uses `ProjectPolicy@update`)
- [ ] `DELETE /api/v1/projects/{id}` — Delete (uses `ProjectPolicy@delete`)

#### Phase Routes (Protected)

- [ ] `GET /api/v1/projects/{project_id}/phases` — List
- [ ] `POST /api/v1/projects/{project_id}/phases` — Create
- [ ] `GET /api/v1/phases/{id}` — View
- [ ] `PATCH /api/v1/phases/{id}` — Update
- [ ] `DELETE /api/v1/phases/{id}` — Delete
- [ ] `POST /api/v1/phases/{id}/approve` — Approve (architect, admin)

#### Task Routes (Protected)

- [ ] `GET /api/v1/phases/{phase_id}/tasks` — List
- [ ] `POST /api/v1/phases/{phase_id}/tasks` — Create
- [ ] `GET /api/v1/tasks/{id}` — View
- [ ] `PATCH /api/v1/tasks/{id}` — Update
- [ ] `DELETE /api/v1/tasks/{id}` — Delete
- [ ] `POST /api/v1/tasks/{id}/assign` — Assign to field engineer
- [ ] `POST /api/v1/tasks/{id}/complete` — Mark complete

#### Report Routes (Protected)

- [ ] `GET /api/v1/tasks/{task_id}/reports` — List
- [ ] `POST /api/v1/tasks/{task_id}/reports` — Create (field engineer)
- [ ] `GET /api/v1/reports/{id}` — View
- [ ] `PATCH /api/v1/reports/{id}` — Update (creator within 24h)
- [ ] `DELETE /api/v1/reports/{id}` — Delete (creator, admin)

#### Transaction Routes (Protected)

- [ ] `GET /api/v1/transactions` — List (filtered by role)
- [ ] `GET /api/v1/transactions/{id}` — View
- [ ] `POST /api/v1/projects/{id}/pay` — Create payment (customer)

#### Product Routes (Protected)

- [ ] `GET /api/v1/products` — List (all authenticated)
- [ ] `GET /api/v1/products/{id}` — View
- [ ] (Admin-only) `POST /api/v1/products` — Create
- [ ] (Admin-only) `PATCH /api/v1/products/{id}` — Update
- [ ] (Admin-only) `DELETE /api/v1/products/{id}` — Delete

#### Order Routes (Protected)

- [ ] `GET /api/v1/orders` — List (filtered by role)
- [ ] `POST /api/v1/orders` — Create (customer)
- [ ] `GET /api/v1/orders/{id}` — View
- [ ] `PATCH /api/v1/orders/{id}` — Update (customer: pending, admin: all)
- [ ] `DELETE /api/v1/orders/{id}` — Cancel (customer: pending, admin: all)

### 1.4 Middleware Implementation

- [ ] Create `backend/app/Http/Middleware/VerifyApiToken.php`

  - [ ] Verify Sanctum token presence
  - [ ] Attach authenticated user to request
  - [ ] Return 401 if token invalid/expired

- [ ] Create `backend/app/Http/Middleware/CheckRole.php` (optional, used for coarse-grained checks)

  - [ ] Check user role against allowed roles
  - [ ] Return 403 if unauthorized

- [ ] Register middleware in `backend/app/Http/Kernel.php`
  - [ ] Add `auth:sanctum` to API middleware group
  - [ ] Apply to all protected routes

### 1.5 Security Best Practices

- [ ] Implement rate limiting on auth endpoints (max 5 attempts per minute)
- [ ] Hash all passwords with bcrypt (Laravel default)
- [ ] Use CSRF tokens for state-changing web routes (if SPA sends POST from same domain)
- [ ] Sanitize all user inputs via Form Requests
- [ ] Log all authentication attempts (successful and failed)
- [ ] Implement activity logging for sensitive operations (payments, approvals, deletions)

---

## 2. Backend Form Requests Checklist

**Objective:** Centralize validation logic via Form Request classes for all user inputs.

### 2.1 Authentication Form Requests

- [ ] `backend/app/Http/Requests/Auth/LoginRequest.php`

  - [ ] `email` — required, email format
  - [ ] `password` — required, string, min 6

- [ ] `backend/app/Http/Requests/Auth/RegisterRequest.php`
  - [ ] `name` — required, string, max 255
  - [ ] `email` — required, email, unique:users
  - [ ] `password` — required, string, min 8, confirmed
  - [ ] `password_confirmation` — required
  - [ ] `phone` — required, string, regex (Arabic/International format)
  - [ ] `role` — required, enum:customer,contractor,supervising_architect,field_engineer

### 2.2 Project Form Requests

- [ ] `backend/app/Http/Requests/Project/StoreProjectRequest.php`

  - [ ] `title` — required, string, max 255
  - [ ] `description` — nullable, string, max 2000
  - [ ] `budget` — required, numeric, min 1000
  - [ ] `customer_id` — required, exists:users (authenticated user if not admin)
  - [ ] `contractor_id` — nullable, exists:users, role must be contractor
  - [ ] `start_date` — nullable, date, after_or_equal:today
  - [ ] `end_date` — nullable, date, after:start_date

- [ ] `backend/app/Http/Requests/Project/UpdateProjectRequest.php`
  - [ ] `title` — nullable, string, max 255
  - [ ] `description` — nullable, string, max 2000
  - [ ] `budget` — nullable, numeric, min 1000
  - [ ] `contractor_id` — nullable, exists:users
  - [ ] `start_date` — nullable, date
  - [ ] `end_date` — nullable, date, after:start_date
  - [ ] `status` — nullable, enum (from WorkflowConfig)

### 2.3 Phase Form Requests

- [ ] `backend/app/Http/Requests/Phase/StorePhaseRequest.php`

  - [ ] `project_id` — required, exists:projects
  - [ ] `name` — required, string, max 255
  - [ ] `description` — nullable, string, max 2000
  - [ ] `budget` — required, numeric, min 100
  - [ ] `start_date` — required, date, after_or_equal:project.start_date
  - [ ] `end_date` — required, date, before_or_equal:project.end_date
  - [ ] `order` — nullable, integer (phase sequence)

- [ ] `backend/app/Http/Requests/Phase/UpdatePhaseRequest.php`
  - [ ] `name` — nullable, string, max 255
  - [ ] `description` — nullable, string, max 2000
  - [ ] `budget` — nullable, numeric, min 100
  - [ ] `status` — nullable, enum

### 2.4 Task Form Requests

- [ ] `backend/app/Http/Requests/Task/StoreTaskRequest.php`

  - [ ] `phase_id` — required, exists:phases
  - [ ] `name` — required, string, max 255
  - [ ] `description` — nullable, string, max 2000
  - [ ] `budget` — required, numeric, min 50
  - [ ] `assigned_to` — nullable, exists:users (must be field_engineer role)
  - [ ] `start_date` — nullable, date
  - [ ] `end_date` — nullable, date
  - [ ] `priority` — nullable, enum:low,medium,high

- [ ] `backend/app/Http/Requests/Task/UpdateTaskRequest.php`
  - [ ] `name` — nullable, string, max 255
  - [ ] `assigned_to` — nullable, exists:users
  - [ ] `status` — nullable, enum

### 2.5 Report Form Requests

- [ ] `backend/app/Http/Requests/Report/StoreReportRequest.php`

  - [ ] `task_id` — required, exists:tasks
  - [ ] `text` — required, string, max 5000 (Arabic or English)
  - [ ] `photos` — nullable, array, max 5 files
  - [ ] `photos.*.` — file, image, max 5MB
  - [ ] `videos` — nullable, array, max 2 files
  - [ ] `videos.*.` — file, mimetypes:video/mp4,video/quicktime, max 50MB

- [ ] `backend/app/Http/Requests/Report/UpdateReportRequest.php`
  - [ ] `text` — nullable, string, max 5000
  - [ ] Allow updates only within 24 hours of creation

### 2.6 Transaction Form Requests

- [ ] `backend/app/Http/Requests/Transaction/StoreTransactionRequest.php`
  - [ ] `project_id` — required, exists:projects
  - [ ] `amount` — required, numeric, min 100, max 1000000
  - [ ] `type` — required, enum:payment,withdrawal
  - [ ] `payment_method` — required, enum:bank_transfer,credit_card (if payment)
  - [ ] `reference` — nullable, string (bank reference number)

### 2.7 Product Form Requests

- [ ] `backend/app/Http/Requests/Product/StoreProductRequest.php` (admin)

  - [ ] `name` — required, string, max 255
  - [ ] `description` — nullable, string, max 2000
  - [ ] `price` — required, numeric, min 1
  - [ ] `category` — required, exists:categories
  - [ ] `sku` — required, string, unique:products
  - [ ] `stock_quantity` — required, integer, min 0
  - [ ] `images` — nullable, array, max 3
  - [ ] `images.*.` — file, image, max 5MB

- [ ] `backend/app/Http/Requests/Product/UpdateProductRequest.php` (admin)
  - [ ] Same fields as Store, all nullable

### 2.8 Order Form Requests

- [ ] `backend/app/Http/Requests/Order/StoreOrderRequest.php`

  - [ ] `customer_id` — required if admin, auto-set if customer
  - [ ] `items` — required, array, min 1 item
  - [ ] `items.*.product_id` — required, exists:products
  - [ ] `items.*.quantity` — required, integer, min 1
  - [ ] `shipping_address` — required, string, max 500
  - [ ] `notes` — nullable, string, max 1000

- [ ] `backend/app/Http/Requests/Order/UpdateOrderRequest.php`
  - [ ] `status` — nullable, enum (pending, confirmed, shipped, delivered, cancelled)
  - [ ] `shipping_address` — nullable, string, max 500
  - [ ] Allow status changes only if order pending

### 2.9 Workflow Configuration Form Requests

- [ ] `backend/app/Http/Requests/WorkflowConfiguration/StoreRequest.php`
  - [ ] `name` — required, string, unique
  - [ ] `statuses` — required, array (sequence of allowed statuses)
  - [ ] `approval_required_on_transition` — boolean (does status change require approval?)
  - [ ] `approver_role` — conditional, enum (if approval_required_on_transition = true)

### 2.10 Validation Message Localization

- [ ] Create `backend/resources/lang/ar/validation.php` with Arabic validation messages

  - [ ] All validation messages in Arabic
  - [ ] Example: `'required' => 'هذا الحقل مطلوب'`

- [ ] Create `backend/resources/lang/en/validation.php` with English messages
  - [ ] All validation messages in English

---

## 3. Eloquent Relationships Checklist

**Objective:** Define all database relationships, scopes, and accessors for the domain model.

### 3.1 User Model & Relationships

- [ ] `backend/app/Models/User.php`
  - [ ] Properties: id, name, email, password, phone, role (enum), avatar_url, created_at, updated_at
  - [ ] Relationship: `projects()` hasMany Project (for customers)
  - [ ] Relationship: `assignedProjects()` hasMany Project as contractor
  - [ ] Relationship: `supervisedProjects()` hasMany Project as supervising_architect
  - [ ] Relationship: `assignedTasks()` hasMany Task as field_engineer
  - [ ] Relationship: `reports()` hasMany Report
  - [ ] Relationship: `transactions()` hasMany Transaction
  - [ ] Relationship: `orders()` hasMany Order
  - [ ] Scope: `customers()` where role = customer
  - [ ] Scope: `contractors()` where role = contractor
  - [ ] Scope: `fieldEngineers()` where role = field_engineer
  - [ ] Scope: `admins()` where role = admin
  - [ ] Scope: `active()` where deleted_at is null

### 3.2 Role Model (Reference, Enum-Based)

- [ ] `backend/app/Enums/UserRole.php`
  - [ ] Cases: Customer, Contractor, SupervisingArchitect, FieldEngineer, Admin
  - [ ] Method: `label()` for display name (Arabic + English)

### 3.3 Project Model & Relationships

- [ ] `backend/app/Models/Project.php`
  - [ ] Properties: id, title, description, budget, status, customer_id, contractor_id, supervising_architect_id, start_date, end_date, created_at, updated_at
  - [ ] Relationship: `customer()` belongsTo User
  - [ ] Relationship: `contractor()` belongsTo User as contractor
  - [ ] Relationship: `supervisor()` belongsTo User as supervising_architect
  - [ ] Relationship: `phases()` hasMany Phase
  - [ ] Relationship: `tasks()` hasManyThrough Task (via phases)
  - [ ] Relationship: `reports()` hasManyThrough Report (via tasks)
  - [ ] Relationship: `transactions()` hasMany Transaction
  - [ ] Relationship: `workflowConfig()` belongsTo WorkflowConfiguration
  - [ ] Scope: `active()` where status != cancelled
  - [ ] Scope: `forCustomer($userId)` where customer_id = $userId
  - [ ] Scope: `forContractor($userId)` where contractor_id = $userId
  - [ ] Accessor: `totalBudget()` sum of phases' budgets
  - [ ] Accessor: `remainingBudget()` budget - sum of spent (via transactions)
  - [ ] Mutator: cast status to enum

### 3.4 Phase Model & Relationships

- [ ] `backend/app/Models/Phase.php`
  - [ ] Properties: id, project_id, name, description, budget, status, start_date, end_date, order, created_at, updated_at
  - [ ] Relationship: `project()` belongsTo Project
  - [ ] Relationship: `tasks()` hasMany Task
  - [ ] Relationship: `reports()` hasManyThrough Report (via tasks)
  - [ ] Scope: `active()` where status != cancelled
  - [ ] Accessor: `taskCount()` count of tasks
  - [ ] Accessor: `completedTaskCount()` count of completed tasks
  - [ ] Accessor: `completionPercentage()` completed / total \* 100

### 3.5 Task Model & Relationships

- [ ] `backend/app/Models/Task.php`
  - [ ] Properties: id, phase_id, name, description, budget, status, assigned_to (field_engineer_id), start_date, end_date, priority, created_at, updated_at
  - [ ] Relationship: `phase()` belongsTo Phase
  - [ ] Relationship: `assignedTo()` belongsTo User as fieldEngineer
  - [ ] Relationship: `reports()` hasMany Report
  - [ ] Scope: `pending()` where status = pending
  - [ ] Scope: `inProgress()` where status = in_progress
  - [ ] Scope: `completed()` where status = completed
  - [ ] Scope: `unassigned()` where assigned_to is null
  - [ ] Accessor: `reportCount()` count of reports
  - [ ] Accessor: `latestReport()` latest report

### 3.6 Report Model & Relationships

- [ ] `backend/app/Models/Report.php`
  - [ ] Properties: id, task_id, user_id (reporter), text, photos (JSON array of URLs), videos (JSON array of URLs), created_at, updated_at
  - [ ] Relationship: `task()` belongsTo Task
  - [ ] Relationship: `reporter()` belongsTo User
  - [ ] Scope: `recent()` orderBy created_at desc
  - [ ] Mutator: `photos` and `videos` cast to array
  - [ ] Method: `canUpdate($user)` true if user is reporter and created < 24h ago

### 3.7 WorkflowConfiguration Model

- [ ] `backend/app/Models/WorkflowConfiguration.php`
  - [ ] Properties: id, name, description, is_default, statuses (JSON array), approval_required_on_transition (bool), approver_role (enum), created_at, updated_at
  - [ ] Relationship: `projects()` hasMany Project
  - [ ] Scope: `default()` where is_default = true
  - [ ] Method: `isValidTransition($from, $to)` check if status transition allowed

### 3.8 ApprovalRule Model

- [ ] `backend/app/Models/ApprovalRule.php`
  - [ ] Properties: id, entity_type (project/phase/task), entity_id, status, requires_approval (bool), approver_role, approved_by (user_id), approved_at, rejection_reason, created_at, updated_at
  - [ ] Relationship: `approver()` belongsTo User
  - [ ] Scope: `pending()` where approved_at is null and rejection_reason is null
  - [ ] Scope: `approved()` where approved_at is not null
  - [ ] Scope: `rejected()` where rejection_reason is not null

### 3.9 Transaction Model

- [ ] `backend/app/Models/Transaction.php`
  - [ ] Properties: id, project_id, user_id, amount, type (payment/withdrawal), status (pending/completed/failed), payment_method, reference, created_at, updated_at
  - [ ] Relationship: `project()` belongsTo Project
  - [ ] Relationship: `user()` belongsTo User
  - [ ] Scope: `payments()` where type = payment
  - [ ] Scope: `withdrawals()` where type = withdrawal
  - [ ] Scope: `forProject($projectId)` where project_id = $projectId
  - [ ] Accessor: `totalAmount()` sum of all transactions for project

### 3.10 Product Model

- [ ] `backend/app/Models/Product.php`
  - [ ] Properties: id, name, description, price, category_id, sku, stock_quantity, images (JSON), created_at, updated_at
  - [ ] Relationship: `category()` belongsTo Category
  - [ ] Relationship: `orders()` belongsToMany Order (via order_items pivot)
  - [ ] Scope: `active()` where stock_quantity > 0
  - [ ] Scope: `inCategory($categoryId)` where category_id = $categoryId
  - [ ] Mutator: `images` cast to array

### 3.11 Category Model

- [ ] `backend/app/Models/Category.php`
  - [ ] Properties: id, name (Arabic + English), description, created_at, updated_at
  - [ ] Relationship: `products()` hasMany Product
  - [ ] Method: `localizedName()` return name in current locale

### 3.12 Order Model

- [ ] `backend/app/Models/Order.php`
  - [ ] Properties: id, customer_id, order_number (unique), total_amount, status (pending/confirmed/shipped/delivered/cancelled), shipping_address, notes, created_at, updated_at
  - [ ] Relationship: `customer()` belongsTo User
  - [ ] Relationship: `items()` belongsToMany Product (via order_items pivot)
  - [ ] Relationship: `transactions()` hasMany Transaction
  - [ ] Scope: `pending()` where status = pending
  - [ ] Scope: `shipped()` where status = shipped
  - [ ] Accessor: `itemCount()` sum of quantities in order_items

### 3.13 Pivot/Junction Tables

- [ ] `order_items` table (order_id, product_id, quantity, unit_price)
  - [ ] Both timestamps

### 3.14 Migration Files

- [ ] `create_users_table.php`

  - [ ] id, name, email, password, phone, role (enum), avatar_url, created_at, updated_at, deleted_at
  - [ ] Unique index: email
  - [ ] Index: role

- [ ] `create_projects_table.php`

  - [ ] id, title, description, budget, status, customer_id (FK), contractor_id (FK), supervising_architect_id (FK), workflow_config_id (FK), start_date, end_date, created_at, updated_at, deleted_at

- [ ] `create_phases_table.php`

  - [ ] id, project_id (FK), name, description, budget, status, start_date, end_date, order, created_at, updated_at, deleted_at

- [ ] `create_tasks_table.php`

  - [ ] id, phase_id (FK), name, description, budget, status, assigned_to (FK to users), start_date, end_date, priority, created_at, updated_at, deleted_at

- [ ] `create_reports_table.php`

  - [ ] id, task_id (FK), user_id (FK to reporter), text, photos (JSON), videos (JSON), created_at, updated_at

- [ ] `create_workflow_configurations_table.php`

  - [ ] id, name, description, is_default, statuses (JSON), approval_required_on_transition, approver_role, created_at, updated_at

- [ ] `create_approval_rules_table.php`

  - [ ] id, entity_type, entity_id, status, requires_approval, approver_role, approved_by (FK), approved_at, rejection_reason, created_at, updated_at

- [ ] `create_transactions_table.php`

  - [ ] id, project_id (FK), user_id (FK), amount, type, status, payment_method, reference, created_at, updated_at

- [ ] `create_products_table.php`

  - [ ] id, name, description, price, category_id (FK), sku, stock_quantity, images (JSON), created_at, updated_at

- [ ] `create_categories_table.php`

  - [ ] id, name (text), description, created_at, updated_at

- [ ] `create_orders_table.php`

  - [ ] id, customer_id (FK), order_number (unique), total_amount, status, shipping_address, notes, created_at, updated_at

- [ ] `create_order_items_table.php`
  - [ ] id, order_id (FK), product_id (FK), quantity, unit_price, created_at, updated_at

---

## 4. Services & Business Logic Checklist

**Objective:** Implement service classes for all business logic with proper dependency injection and error handling.

### 4.1 AuthService

- [ ] `backend/app/Services/AuthService.php`
  - [ ] `login($email, $password)` — Verify credentials, return token + user
  - [ ] `register($data)` — Create user, hash password, return token
  - [ ] `logout($user)` — Revoke Sanctum tokens
  - [ ] `refreshToken($user)` — Issue new token
  - [ ] Dependency: UserRepository
  - [ ] Error handling: throw custom exceptions for invalid credentials, email exists, etc.

### 4.2 ProjectService

- [ ] `backend/app/Services/ProjectService.php`
  - [ ] `createProject($data)` — Create project, set initial workflow config, return project
  - [ ] `updateProject($projectId, $data)` — Update project details
  - [ ] `deleteProject($projectId)` — Soft delete project (if no completed phases)
  - [ ] `getProjectDetails($projectId)` — Fetch project with relationships (phases, tasks, transactions)
  - [ ] `listProjectsForUser($userId, $role)` — Filter projects by user role
  - [ ] Dependency: ProjectRepository, WorkflowConfigService
  - [ ] Error handling: validate project exists, user has permission, budget constraints

### 4.3 PhaseService

- [ ] `backend/app/Services/PhaseService.php`
  - [ ] `createPhase($projectId, $data)` — Create phase, validate budget doesn't exceed project, return phase
  - [ ] `updatePhase($phaseId, $data)` — Update phase details
  - [ ] `deletePhase($phaseId)` — Delete phase (only if no completed tasks)
  - [ ] `approvePhase($phaseId, $approverId)` — Transition phase to approved state
  - [ ] `completePhase($phaseId)` — Mark phase complete (if all tasks complete)
  - [ ] Dependency: PhaseRepository, WorkflowService
  - [ ] Events: Dispatch `PhaseApproved`, `PhaseCompleted` events

### 4.4 TaskService

- [ ] `backend/app/Services/TaskService.php`
  - [ ] `createTask($phaseId, $data)` — Create task, validate budget, return task
  - [ ] `updateTask($taskId, $data)` — Update task details
  - [ ] `deleteTask($taskId)` — Delete task (only if not started)
  - [ ] `assignTask($taskId, $fieldEngineerId)` — Assign task to field engineer
  - [ ] `completeTask($taskId)` — Transition task to complete
  - [ ] `getTaskDetails($taskId)` — Fetch task with latest report, phase, project
  - [ ] Dependency: TaskRepository, WorkflowService
  - [ ] Error handling: validate task exists, budget constraints, role permissions

### 4.5 ReportService

- [ ] `backend/app/Services/ReportService.php`
  - [ ] `createReport($taskId, $userId, $data)` — Create report, process file uploads (photos, videos), return report
  - [ ] `updateReport($reportId, $userId, $data)` — Update report (only within 24h, user is creator)
  - [ ] `deleteReport($reportId, $userId)` — Delete report (only if creator or admin)
  - [ ] `getReportsForTask($taskId)` — Fetch all reports for task, ordered by recency
  - [ ] File handling: Upload to S3 (or local storage), store URLs in database
  - [ ] Dependency: ReportRepository, FileStorageService
  - [ ] Error handling: validate file types, sizes, task exists, permissions

### 4.6 WorkflowService

- [ ] `backend/app/Services/WorkflowService.php`
  - [ ] `transitionStatus($entityType, $entityId, $newStatus, $userId)` — Validate transition, apply approval rules, update status
  - [ ] `getAvailableTransitions($entityType, $currentStatus)` — Return list of allowed next statuses
  - [ ] `requiresApproval($entityType, $newStatus)` — Check if transition requires approval
  - [ ] `approveTransition($approvalRuleId, $approverId)` — Approve pending status change
  - [ ] `rejectTransition($approvalRuleId, $approverId, $reason)` — Reject status change
  - [ ] Dependency: WorkflowConfigRepository, ApprovalRuleRepository
  - [ ] Events: Dispatch `StatusTransitioned`, `ApprovalRequested`, `TransitionApproved` events

### 4.7 TransactionService

- [ ] `backend/app/Services/TransactionService.php`
  - [ ] `createPayment($projectId, $customerId, $amount)` — Create payment transaction, verify customer owns project or is admin, return transaction
  - [ ] `createWithdrawal($projectId, $contractorId, $amount)` — Create withdrawal, verify contractor assigned to project
  - [ ] `getTransactionsForProject($projectId)` — Fetch all transactions for project
  - [ ] `getTransactionsForUser($userId, $role)` — Fetch transactions based on user role (own payments, own withdrawals, or all if admin)
  - [ ] Dependency: TransactionRepository, ProjectRepository
  - [ ] Error handling: validate project exists, user permissions, sufficient budget/funds, idempotency checks

### 4.8 ProductService

- [ ] `backend/app/Services/ProductService.php`
  - [ ] `listProducts($filters)` — List products with pagination, category filtering, search
  - [ ] `getProductDetails($productId)` — Fetch product with category, images
  - [ ] `createProduct($data)` — Create product (admin only, enforced in controller policy)
  - [ ] `updateProduct($productId, $data)` — Update product
  - [ ] `deleteProduct($productId)` — Delete product (soft delete)
  - [ ] `updateStock($productId, $quantityChange)` — Adjust stock after order
  - [ ] Dependency: ProductRepository, CategoryRepository
  - [ ] Error handling: validate inputs, check stock availability

### 4.9 OrderService

- [ ] `backend/app/Services/OrderService.php`
  - [ ] `createOrder($customerId, $items, $shippingAddress)` — Create order, verify items exist and stock available, calculate total, create order + line items, return order
  - [ ] `updateOrderStatus($orderId, $status)` — Update order status (only valid transitions)
  - [ ] `cancelOrder($orderId)` — Cancel pending order, restore stock
  - [ ] `getOrdersForCustomer($customerId)` — Fetch customer's orders
  - [ ] `getOrderDetails($orderId)` — Fetch order with items, customer, transactions
  - [ ] Dependency: OrderRepository, ProductService, TransactionService
  - [ ] Database transaction: Wrap order creation in DB transaction (atomic)
  - [ ] Error handling: validate items, stock, customer permissions

### 4.10 NotificationService (Optional, Placeholder)

- [ ] `backend/app/Services/NotificationService.php`
  - [ ] `notifyProjectCreated($project)` — Send email to customer, contractor
  - [ ] `notifyTaskAssigned($task)` — Send email to field engineer
  - [ ] `notifyReportSubmitted($report)` — Send email to contractor, architect
  - [ ] `notifyApprovalRequired($entity)` — Send email to approver
  - [ ] Dependency: Mail facade, event listeners

### 4.11 Service Layer Requirements

- [ ] All services use constructor dependency injection (no Service Locator pattern)
- [ ] All services have clear, single responsibilities
- [ ] All services throw custom exceptions (not generic exceptions)
- [ ] All services use repositories for data access (never direct Eloquent)
- [ ] All services handle database transactions for multi-step operations
- [ ] All services dispatch domain events for important state changes
- [ ] All services have comprehensive error logging
- [ ] All services are 100% testable (mockable dependencies)

---

## 5. Frontend RTL & Internationalization Checklist

**Objective:** Ensure full RTL support and Arabic-first i18n implementation.

### 5.1 i18n Configuration

- [ ] Install `@nuxtjs/i18n` module

  - [ ] `npm install @nuxtjs/i18n`

- [ ] Configure in `frontend/nuxt.config.ts`:
  ```typescript
  modules: ['@nuxtjs/i18n'],
  i18n: {
    locales: [
      { code: 'ar', name: 'العربية', dir: 'rtl' },
      { code: 'en', name: 'English', dir: 'ltr' }
    ],
    defaultLocale: 'ar',
    strategy: 'prefix_except_default',
    fallbackLocale: 'ar'
  }
  ```

### 5.2 Translation Files

- [ ] Create `frontend/locales/ar.json` (Arabic translations)

  - [ ] Sections: common, auth, dashboard, projects, phases, tasks, reports, products, orders, admin
  - [ ] Example keys:
    - [ ] `common.save` = "حفظ"
    - [ ] `common.cancel` = "إلغاء"
    - [ ] `auth.login` = "تسجيل الدخول"
    - [ ] `dashboard.welcome` = "أهلا وسهلا"
    - [ ] `projects.createNew` = "إنشاء مشروع جديد"

- [ ] Create `frontend/locales/en.json` (English translations)
  - [ ] Mirror structure of Arabic file
  - [ ] Example keys:
    - [ ] `common.save` = "Save"
    - [ ] `common.cancel` = "Cancel"
    - [ ] `auth.login` = "Sign In"

### 5.3 RTL HTML Structure

- [ ] Set `<html>` dir attribute dynamically:

  ```vue
  <html :dir="$i18n.locale === 'ar' ? 'rtl' : 'ltr'"></html>
  ```

- [ ] Or in `frontend/app.vue`:
  ```typescript
  const { locale } = useI18n();
  watch(
    locale,
    (newLocale) => {
      document.documentElement.dir = newLocale === "ar" ? "rtl" : "ltr";
    },
    { immediate: true }
  );
  ```

### 5.4 Tailwind CSS Logical Properties

- [ ] Use logical properties in Tailwind classes instead of directional:

  - [ ] ✅ `ms-4` (margin-inline-start) instead of `ml-4`
  - [ ] ✅ `me-4` (margin-inline-end) instead of `mr-4`
  - [ ] ✅ `ps-6` (padding-inline-start) instead of `pl-6`
  - [ ] ✅ `pe-6` (padding-inline-end) instead of `pr-6`
  - [ ] ✅ `start-0` (inset-inline-start) instead of `left-0`
  - [ ] ✅ `end-0` (inset-inline-end) instead of `right-0`

- [ ] Flex/Grid layouts automatically respond to `dir` attribute:
  - [ ] `flex` with `direction: row` becomes right-to-left in RTL
  - [ ] No need for explicit direction reversal in most cases

### 5.5 Component Internationalization

- [ ] All components use `{{ $t('key') }}` for text:

  ```vue
  <template>
    <UButton>{{ $t("common.save") }}</UButton>
  </template>
  ```

- [ ] Form labels, placeholders, validation messages use i18n:

  ```vue
  <UFormGroup :label="$t('auth.email')">
    <UInput :placeholder="$t('auth.emailPlaceholder')" />
  </UFormGroup>
  ```

- [ ] Error messages from API use i18n keys (map backend errors to frontend keys):
  ```typescript
  const errorMessages = {
    email_required: $t("validation.emailRequired"),
    password_too_short: $t("validation.passwordMinLength"),
  };
  ```

### 5.6 Date, Time, Number Formatting (Locale-Aware)

- [ ] Use `useI18n()` composable for locale:

  ```typescript
  const { locale } = useI18n();
  const formatted = new Intl.DateTimeFormat(locale.value).format(date);
  ```

- [ ] Or use utility function:
  ```typescript
  // utils/formatting.ts
  export const formatDate = (date: Date, locale: string) => {
    return new Intl.DateTimeFormat(locale, {
      year: "numeric",
      month: "long",
      day: "numeric",
    }).format(date);
  };
  ```

### 5.7 Geist Font Integration

- [ ] Install Geist fonts:

  ```bash
  npm install geist
  ```

- [ ] Configure in `frontend/tailwind.config.js`:

  ```javascript
  theme: {
    fontFamily: {
      sans: ['Geist', 'system-ui', 'sans-serif'],
      mono: ['Geist Mono', 'monospace']
    }
  }
  ```

- [ ] Add to `frontend/app.vue` or global CSS:

  ```css
  @import "geist/dist/fonts/geist-sans/index.css";
  @import "geist/dist/fonts/geist-mono/index.css";

  * {
    font-feature-settings: "liga" 1;
  }
  ```

### 5.8 RTL Testing Checklist

- [ ] [ ] View all pages in Arabic (RTL) and English (LTR)
- [ ] [ ] Test layout flipping: sidebars, buttons, form fields
- [ ] [ ] Test flex/grid layouts respond correctly to `dir="rtl"`
- [ ] [ ] Test modals/dialogs display correctly in both directions
- [ ] [ ] Test form validation error messages position (right vs left)
- [ ] [ ] Test images and icons don't require flipping (use semantic icons)
- [ ] [ ] Test table headers and columns align correctly in RTL
- [ ] [ ] Test dropdowns, tooltips position correctly
- [ ] [ ] Test all text is Arabic when locale is `ar`
- [ ] [ ] Test locale switcher updates all UI instantly

### 5.9 Arabic-Specific UI Patterns

- [ ] Numbers in forms accept Eastern Arabic numerals (٠١٢٣٤٥٦٧٨٩) and Western (0-9)
- [ ] Phone number formatting respects Arabic country codes
- [ ] Currency display: 1,234.56 ريال instead of 1234.56 SR
- [ ] Date format: التاريخ: 10 أبريل 2026 (not 2026-04-10)
- [ ] Form field hints/labels stack correctly in RTL
- [ ] Success/error icons position correctly (right side in RTL, left in LTR)

---

## 6. Testing Strategy Checklist

**Objective:** Implement comprehensive testing across all layers with coverage targets.

### 6.1 Backend Unit Tests (PHPUnit)

- [ ] `backend/tests/Unit/Services/AuthServiceTest.php`

  - [ ] Test login with valid credentials
  - [ ] Test login with invalid credentials
  - [ ] Test registration with valid data
  - [ ] Test registration with duplicate email
  - [ ] Test password hashing

- [ ] `backend/tests/Unit/Services/ProjectServiceTest.php`

  - [ ] Test create project with valid data
  - [ ] Test budget validation
  - [ ] Test project retrieval by role

- [ ] `backend/tests/Unit/Repositories/ProjectRepositoryTest.php`
  - [ ] Test find by id
  - [ ] Test query with scopes (active, forCustomer, etc.)
  - [ ] Test update
  - [ ] Test delete (soft delete)

### 6.2 Backend Feature Tests (Laravel TestCase)

- [ ] `backend/tests/Feature/Auth/LoginTest.php`

  - [ ] Test POST /api/v1/auth/login with valid credentials
  - [ ] Test returns token + user data
  - [ ] Test rate limiting (max 5 attempts/min)
  - [ ] Test response structure matches contract

- [ ] `backend/tests/Feature/Projects/CreateProjectTest.php`

  - [ ] Test authenticated customer can create project
  - [ ] Test unauthenticated user gets 401
  - [ ] Test contractor cannot create project (403)
  - [ ] Test validation fails on missing required fields
  - [ ] Test response includes project with relations

- [ ] `backend/tests/Feature/Projects/ListProjectsTest.php`

  - [ ] Test customer sees only own projects
  - [ ] Test contractor sees assigned projects
  - [ ] Test admin sees all projects
  - [ ] Test pagination works

- [ ] `backend/tests/Feature/Phases/ApprovePhaseTest.php`

  - [ ] Test supervising architect can approve phase
  - [ ] Test non-architect cannot approve
  - [ ] Test approval creates ApprovalRule record
  - [ ] Test phase status transitions to approved

- [ ] `backend/tests/Feature/Tasks/CompleteTaskTest.php`

  - [ ] Test field engineer can complete assigned task
  - [ ] Test other roles cannot complete task
  - [ ] Test task status transitions correctly

- [ ] `backend/tests/Feature/Reports/CreateReportTest.php`
  - [ ] Test field engineer can create report
  - [ ] Test file uploads (photos, videos) processed
  - [ ] Test files stored with correct permissions
  - [ ] Test report created with correct relationships

### 6.3 Frontend Unit Tests (Vitest)

- [ ] `frontend/tests/unit/composables/useAuth.test.ts`

  - [ ] Test login composable calls API correctly
  - [ ] Test token stored in Pinia
  - [ ] Test logout clears token
  - [ ] Test auto-login from localStorage

- [ ] `frontend/tests/unit/stores/auth.test.ts`

  - [ ] Test Pinia store initialization
  - [ ] Test setUser action
  - [ ] Test setToken action
  - [ ] Test clearAuth action

- [ ] `frontend/tests/unit/utils/formatting.test.ts`
  - [ ] Test formatDate with Arabic locale
  - [ ] Test formatDate with English locale
  - [ ] Test formatCurrency in Arabic
  - [ ] Test formatCurrency in English

### 6.4 Frontend Component Tests (Vitest + Vue Test Utils)

- [ ] `frontend/tests/components/LoginForm.test.ts`

  - [ ] Test form renders with email and password fields
  - [ ] Test submit button disabled until fields filled
  - [ ] Test validation errors display
  - [ ] Test i18n labels in Arabic and English

- [ ] `frontend/tests/components/ProjectCard.test.ts`

  - [ ] Test card renders project data
  - [ ] Test click navigates to project detail
  - [ ] Test edit button shows for owner
  - [ ] Test delete button shows for admin only

- [ ] `frontend/tests/components/PhaseList.test.ts`
  - [ ] Test list renders all phases
  - [ ] Test pagination works
  - [ ] Test search/filter works
  - [ ] Test loading state displays skeleton

### 6.5 Frontend E2E Tests (Playwright)

- [ ] `frontend/tests/e2e/auth.spec.ts`

  - [ ] User can navigate to login page
  - [ ] User can fill email and password
  - [ ] User can submit login form
  - [ ] User redirected to dashboard on success
  - [ ] Invalid credentials show error message

- [ ] `frontend/tests/e2e/project.creation.spec.ts`

  - [ ] Customer can navigate to create project
  - [ ] Customer can fill project form
  - [ ] Customer can submit and see success message
  - [ ] Project appears in project list

- [ ] `frontend/tests/e2e/report.submission.spec.ts`
  - [ ] Field engineer can navigate to task
  - [ ] Field engineer can upload report with text and photos
  - [ ] Report displays in task report list
  - [ ] Timestamp and reporter name visible

### 6.6 Coverage Targets

- [ ] Backend services: ≥80% coverage

  - [ ] All critical business logic tested
  - [ ] Error paths tested
  - [ ] Edge cases covered

- [ ] Backend controllers: ≥70% coverage

  - [ ] All endpoints tested with auth + policy checks
  - [ ] Validation failures tested

- [ ] Frontend composables: ≥70% coverage

  - [ ] All hooks tested with mocked API calls

- [ ] Frontend components: ≥60% coverage

  - [ ] Critical user paths tested
  - [ ] User interactions (clicks, form input) tested

- [ ] E2E: Critical flows only
  - [ ] Auth flow (login, logout, register)
  - [ ] Project creation and viewing
  - [ ] Report submission

### 6.7 Test Configuration Files

- [ ] `backend/phpunit.xml` configured:

  - [ ] Uses SQLite in-memory database
  - [ ] Sets APP_ENV=testing
  - [ ] Includes coverage reporting
  - [ ] Exclude vendor, node_modules

- [ ] `frontend/vitest.config.ts` configured:

  - [ ] Vue + TypeScript support
  - [ ] Coverage reporter
  - [ ] Alias paths

- [ ] `frontend/playwright.config.ts` configured:
  - [ ] Base URL: `http://localhost:3000`
  - [ ] Timeout: 30s per test
  - [ ] Headless: true
  - [ ] Screenshot on failure

---

## 7. Configuration & DevOps Checklist

**Objective:** Ensure all configuration files, CI/CD pipelines, and development tooling are properly set up.

### 7.1 Backend Configuration Files

- [ ] `backend/.env.example`

  - [ ] All required env vars documented
  - [ ] Safe defaults (no real credentials)
  - [ ] Clear comments for each variable

- [ ] `backend/pint.json`

  - [ ] PSR-12 ruleset
  - [ ] Exclude vendor, storage, bootstrap, tests (optional)
  - [ ] Rules: single_quote, no_trailing_comma_in_list_call

- [ ] `backend/phpstan.neon`

  - [ ] Level: 5 (strict)
  - [ ] Paths: app/, tests/
  - [ ] Stub files for Laravel
  - [ ] Ignore patterns for known false positives

- [ ] `backend/pint.json` (optional, if using Laravel Pint)

  - [ ] Preset: psr12
  - [ ] Paths: app/, routes/, config/

- [ ] `backend/phpunit.xml`

  - [ ] Test paths: tests/
  - [ ] Database: SQLite in-memory or file
  - [ ] Coverage: enabled, minimum threshold 80%

- [ ] `backend/config/sanctum.php`

  - [ ] Stateful domains: localhost:3000 (dev), production domain
  - [ ] Expires in: 7 days (configurable)
  - [ ] Prefix: Bearer

- [ ] `backend/config/app.php` (Laravel default)
  - [ ] Timezone: UTC
  - [ ] Locale: ar (Arabic default)
  - [ ] Fallback locale: en

### 7.2 Frontend Configuration Files

- [ ] `frontend/nuxt.config.ts`

  - [ ] Modules: @nuxt/ui, @nuxtjs/i18n
  - [ ] i18n: locales (ar, en), defaultLocale: ar
  - [ ] Nitro: prerender disabled (SSR enabled)
  - [ ] Build: minify enabled, sourcemap disabled in prod

- [ ] `frontend/tsconfig.json`

  - [ ] Target: ES2020
  - [ ] Module: ESNext
  - [ ] Strict: true
  - [ ] Paths: @ → src/

- [ ] `frontend/tailwind.config.js`

  - [ ] Content: pages/**, components/**, app.vue
  - [ ] Theme: Extend with Geist fonts, colors from DESIGN.md
  - [ ] Plugins: @tailwindcss/forms (optional)

- [ ] `frontend/.eslintrc.js`

  - [ ] Extends: @nuxt/eslint-config
  - [ ] Parser: vue-eslint-parser
  - [ ] Rules: no-console (warn in dev, error in prod), no-debugger

- [ ] `frontend/.prettierrc.json`

  - [ ] Semi: true
  - [ ] SingleQuote: true
  - [ ] TrailingComma: es5
  - [ ] TabWidth: 2
  - [ ] PrintWidth: 100

- [ ] `frontend/vitest.config.ts`

  - [ ] Environment: jsdom
  - [ ] Coverage: c8, threshold 70%
  - [ ] Include: tests/\*\*

- [ ] `frontend/playwright.config.ts`
  - [ ] BaseURL: http://localhost:3000
  - [ ] Timeout: 30000
  - [ ] Retries: 0 (local), 2 (CI)
  - [ ] Headed: false
  - [ ] Screenshot: only-on-failure

### 7.3 Git & Pre-Commit Setup

- [ ] `.husky/pre-commit` script:

  - [ ] Backend: `cd backend && vendor/bin/pint --test && vendor/bin/phpstan analyse --memory-limit=512M`
  - [ ] Frontend: `cd frontend && npm run lint:fix && npx prettier --write .`
  - [ ] Exit if any fails

- [ ] `.lintstagedrc.json`:

  - [ ] `backend/**/*.php` (lint-staged): [`vendor/bin/pint`, `vendor/bin/phpstan analyse --memory-limit=512M`]
  - [ ] `frontend/**/*.{vue,ts,js}`: [`eslint --fix`, `prettier --write`]
  - [ ] `frontend/**/*.json`: [`prettier --write`]

- [ ] `.gitignore`:
  - [ ] backend: /vendor, /.env, /node_modules, /storage
  - [ ] frontend: /node_modules, /.nuxt, /dist, /.output
  - [ ] Root: \*.log, .DS_Store, .env.local

### 7.4 GitHub Actions Workflows

- [ ] `.github/workflows/pre-commit-guard.yml`:
  - [ ] Trigger: on pull_request, push to develop/main
  - [ ] Jobs:
    - [ ] backend-lint: `vendor/bin/pint --test`
    - [ ] backend-analyze: `phpstan analyse`
    - [ ] backend-test: `php artisan test`
    - [ ] frontend-lint: `npm run lint`
    - [ ] frontend-typecheck: `npx nuxi typecheck`
    - [ ] frontend-test: `npm run test`
    - [ ] e2e-test: `npm run test:e2e`
  - [ ] All jobs must pass before merge

### 7.5 Docker Compose Setup

- [ ] `docker-compose.yml`:
  - [ ] MySQL 8.0: port 3306, database: bunyan
  - [ ] Redis 7: port 6379
  - [ ] PHP 8.2-FPM: (if using Docker for PHP, optional)
  - [ ] Node 20: (if using Docker for Node, optional)
  - [ ] Volumes: backend/, frontend/, mysql_data
  - [ ] Networks: app-network

### 7.6 Environment Variables

- [ ] `backend/.env.example`:

  - [ ] APP_NAME, APP_ENV, APP_DEBUG, APP_URL
  - [ ] DB\_\* (connection, host, port, database, username, password)
  - [ ] CACHE_DRIVER, QUEUE_CONNECTION, SESSION_DRIVER
  - [ ] SANCTUM_STATEFUL_DOMAINS
  - [ ] MAIL*\*, FILESYSTEM*\* (if applicable)

- [ ] `backend/ci.env`:

  - [ ] APP_ENV=testing, APP_URL for CI
  - [ ] DB\_\* aligned with GitHub Actions MySQL service (`bunyan_test`, root, password)
  - [ ] CACHE*DRIVER / QUEUE_CONNECTION / REDIS*\* aligned with CI Redis service
  - [ ] MAIL_MAILER=array (or log) for tests

- [ ] `frontend/.env.example`:
  - [ ] VITE_API_BASE_URL=http://localhost:8000
  - [ ] VITE_API_VERSION=v1

### 7.7 Root Configuration

- [ ] `package.json` (root):

  - [ ] Scripts: install:all, lint, lint:fix, test, dev, dev:backend, dev:frontend
  - [ ] DevDependencies: husky, lint-staged, concurrently

- [ ] `docker-compose.yml` (root):
  - [ ] Services: mysql, redis, (optional: php-fpm, node)
  - [ ] Volumes, networks, environment variables

### 7.8 Validation Pipeline

- [ ] Create root script `scripts/validate.sh`:

  ```bash
  #!/bin/bash
  set -e
  echo "Running validation pipeline..."
  cd backend && composer run lint && composer run analyze && composer run test
  cd ../frontend && npm run lint && npm run typecheck && npm run test
  cd ../
  echo "All checks passed!"
  ```

- [ ] Ensure all tests pass locally before pushing
- [ ] CI repeats validation (GitHub Actions pre-commit-guard.yml)

### 7.9 CI/CD Enforcement

- [ ] Branch protection rules (GitHub):

  - [ ] Require PR reviews
  - [ ] Require status checks to pass (all GitHub Actions)
  - [ ] Dismiss stale PR approvals on new pushes
  - [ ] Require branches to be up to date before merging
  - [ ] Restrict who can push to main, develop

- [ ] Auto-deployment (optional, future step):
  - [ ] Deploy main to production
  - [ ] Deploy develop to staging

---

## ✅ Checklist Completion Summary

**All items must be completed during IMPLEMENT step:**

- [ ] **RBAC & Security:** 35+ items (roles, policies, routes, middleware, rate limiting)
- [ ] **Form Requests:** 50+ validation rules across 8+ request classes
- [ ] **Eloquent Relationships:** 13 models with relationships, scopes, accessors defined
- [ ] **Services & Business Logic:** 10 service classes with dependency injection
- [ ] **RTL & i18n:** Arabic + English translations, Geist fonts, RTL testing
- [ ] **Testing:** 50+ PHPUnit tests, 45+ Vitest tests, 10+ Playwright E2E tests
- [ ] **Configuration & DevOps:** 15+ config files, CI/CD workflows, Docker Compose, git hooks

**Total Deliverables:** 200+ actionable checklist items

---

**Generated by:** SPECIFY Step  
**Specification Date:** 2026-04-10  
**Status:** COMPLETE
