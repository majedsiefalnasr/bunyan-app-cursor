# PHASE 2 FILE MANIFEST — Database & Layering

## STAGE_01_PROJECT_INITIALIZATION

**Generated:** April 10, 2026  
**Status:** ✅ COMPLETE

---

## Migrations (14 files)

### Core Identity Tables

```
backend/database/migrations/2026_04_10_174656_create_users_table.php
  - id, name, email (unique), role, phone, active, timestamps, soft_deletes
  - Indexes: role, active, email

backend/database/migrations/2026_04_10_174657_create_roles_table.php
  - id, name (unique), description, timestamps
  - Stores: customer, contractor, supervising_architect, field_engineer, admin

backend/database/migrations/2026_04_10_174658_create_permissions_table.php
  - id, name (unique), description, timestamps
  - Stores: 28+ permission entries (project.*, phase.*, task.*, report.*, etc.)

backend/database/migrations/2026_04_10_174659_create_role_permissions_table.php
  - id, role_id (FK), permission_id (FK), timestamps
  - Unique constraint: role_id + permission_id
  - Many-to-many pivot table
```

### Project Management Tables

```
backend/database/migrations/2026_04_10_174700_create_projects_table.php
  - id, name, description, customer_id (FK-restrict), contractor_id (FK-null),
    supervising_architect_id (FK-null), status, budget, location, start_date, end_date,
    timestamps, soft_deletes
  - Indexes: status, customer_id, contractor_id, supervising_architect_id, created_at

backend/database/migrations/2026_04_10_174701_create_phases_table.php
  - id, project_id (FK-cascade), name, description, status, budget, progress (0-100),
    start_date, end_date, timestamps, soft_deletes
  - Indexes: project_id, status, created_at

backend/database/migrations/2026_04_10_174702_create_tasks_table.php
  - id, phase_id (FK-cascade), name, description, status, budget, assigned_to (FK-null),
    start_date, end_date, timestamps, soft_deletes
  - Indexes: phase_id, assigned_to, status
```

### Workflow & Approval Tables

```
backend/database/migrations/2026_04_10_174703_create_workflow_configurations_table.php
  - id, project_id (FK-cascade, nullable), name, description,
    status_transitions (JSON), approval_requirements (JSON), is_global, timestamps
  - Indexes: project_id, is_global
  - Stores global workflow defaults + per-project overrides

backend/database/migrations/2026_04_10_174704_create_approval_rules_table.php
  - id, workflow_configuration_id (FK-cascade), entity_type, status_from, status_to,
    approver_role, approval_count, timestamps
  - Indexes: workflow_configuration_id, (entity_type, status_from, status_to)
```

### Field Reporting & Transactions

```
backend/database/migrations/2026_04_10_174705_create_reports_table.php
  - id, task_id (FK-cascade, nullable), phase_id (FK-cascade, nullable),
    project_id (FK-cascade, nullable), created_by (FK-restrict), description,
    attachments (JSON), status, timestamps, soft_deletes
  - Indexes: task_id, phase_id, project_id, created_by, status

backend/database/migrations/2026_04_10_174706_create_transactions_table.php
  - id, user_id (FK-restrict), project_id (FK-null), order_id (FK-null), type,
    amount (decimal 15,2), status, payment_method, reference, description, timestamps
  - Indexes: user_id, project_id, order_id, type, status, created_at
```

### E-Commerce Tables

```
backend/database/migrations/2026_04_10_174707_create_products_table.php
  - id, name, description, sku (unique), price (decimal 15,2), quantity_in_stock,
    category, specifications (JSON), image_url, active, timestamps, soft_deletes
  - Indexes: sku, category, active

backend/database/migrations/2026_04_10_174708_create_orders_table.php
  - id, customer_id (FK-restrict), project_id (FK-null), status, total_amount (decimal 15,2),
    notes, delivery_date, delivery_address, timestamps, soft_deletes
  - Indexes: customer_id, project_id, status, created_at

backend/database/migrations/2026_04_10_174709_create_order_items_table.php
  - id, order_id (FK-cascade), product_id (FK-restrict), quantity, unit_price (decimal 15,2),
    subtotal (decimal 15,2), timestamps
  - Indexes: order_id, product_id
  - Pivot table for orders ↔ products many-to-many
```

---

## Models (13 files)

```
backend/app/Models/User.php
  ├─ Authenticatable, HasApiTokens, SoftDeletes
  ├─ Relationships: projects, contractorProjects, supervisedProjects, assignedTasks, reports, transactions, orders
  ├─ Scopes: active, byRole
  └─ Casts: email_verified_at (datetime), password (hashed), active (boolean)

backend/app/Models/Role.php
  ├─ BelongsToMany: permissions (via role_permissions)
  └─ Fillable: name, description

backend/app/Models/Permission.php
  ├─ BelongsToMany: roles (via role_permissions)
  └─ Fillable: name, description

backend/app/Models/Project.php
  ├─ SoftDeletes
  ├─ Relationships: customer, contractor, supervisingArchitect, phases, tasks, reports, transactions, orders
  ├─ Scopes: active, forUser (RBAC), byStatus
  ├─ Casts: start_date (date), end_date (date), budget (decimal:2)
  └─ Fillable: name, description, customer_id, contractor_id, supervising_architect_id, status, budget, location, start_date, end_date

backend/app/Models/Phase.php
  ├─ SoftDeletes
  ├─ Relationships: project, tasks, reports
  ├─ Scopes: active, byProject, byStatus
  ├─ Casts: start_date (date), end_date (date), budget (decimal:2)
  └─ Fillable: project_id, name, description, status, budget, progress, start_date, end_date

backend/app/Models/Task.php
  ├─ SoftDeletes
  ├─ Relationships: phase, assignee, reports
  ├─ Scopes: active, byPhase, assignedTo, byStatus
  ├─ Casts: start_date (date), end_date (date), budget (decimal:2)
  └─ Fillable: phase_id, name, description, status, budget, assigned_to, start_date, end_date

backend/app/Models/WorkflowConfiguration.php
  ├─ Relationships: project, approvalRules
  ├─ Casts: status_transitions (json), approval_requirements (json), is_global (boolean)
  └─ Fillable: project_id, name, description, status_transitions, approval_requirements, is_global

backend/app/Models/ApprovalRule.php
  ├─ Relationships: workflowConfiguration
  └─ Fillable: workflow_configuration_id, entity_type, status_from, status_to, approver_role, approval_count

backend/app/Models/Report.php
  ├─ SoftDeletes
  ├─ Relationships: task, phase, project, creator
  ├─ Scopes: byProject, byPhase, byTask, byStatus
  ├─ Casts: attachments (json)
  └─ Fillable: task_id, phase_id, project_id, created_by, description, attachments, status

backend/app/Models/Transaction.php
  ├─ Relationships: user, project, order
  ├─ Scopes: byUser, byType, byStatus, completed
  ├─ Casts: amount (decimal:2)
  └─ Fillable: user_id, project_id, order_id, type, amount, status, payment_method, reference, description

backend/app/Models/Product.php
  ├─ SoftDeletes
  ├─ Relationships: orderItems
  ├─ Scopes: active, byCategory, bySku, inStock
  ├─ Casts: price (decimal:2), specifications (json), active (boolean)
  └─ Fillable: name, description, sku, price, quantity_in_stock, category, specifications, image_url, active

backend/app/Models/Order.php
  ├─ SoftDeletes
  ├─ Relationships: customer, project, items, transactions
  ├─ Scopes: byCustomer, byProject, byStatus, pending, completed
  ├─ Casts: total_amount (decimal:2), delivery_date (date)
  └─ Fillable: customer_id, project_id, status, total_amount, notes, delivery_date, delivery_address

backend/app/Models/OrderItem.php
  ├─ Relationships: order, product
  ├─ Casts: unit_price (decimal:2), subtotal (decimal:2)
  └─ Fillable: order_id, product_id, quantity, unit_price, subtotal
```

---

## Repositories (10 files)

```
backend/app/Repositories/UserRepository.php
  ├─ Methods: findById, findByIdOrFail, findByEmail, all, allByRole, create, update, delete, restore
  └─ Patterns: Filtering by role/active/search, pagination (15 per_page)

backend/app/Repositories/ProjectRepository.php
  ├─ Methods: findById (eager load), findByIdOrFail, listForUser (RBAC), allActive, allByCustomer, allByContractor, create, update, delete, restore
  └─ Patterns: Eager load customer/contractor/supervisor, RBAC filtering by user role

backend/app/Repositories/PhaseRepository.php
  ├─ Methods: findById, findByIdOrFail, allByProject (paginated), allActiveByProject, create, update, delete, restore
  └─ Patterns: Filter by project/status, eager load tasks/reports

backend/app/Repositories/TaskRepository.php
  ├─ Methods: findById, findByIdOrFail, allByPhase, allAssignedTo, allActiveByPhase, create, update, delete, restore
  └─ Patterns: Filter by phase/assignee/status, eager load assignee/reports

backend/app/Repositories/ReportRepository.php
  ├─ Methods: findById, findByIdOrFail, allByProject, allByPhase, allByTask, create, update, delete, restore
  └─ Patterns: Filter by entity (project/phase/task)/status, eager load creator

backend/app/Repositories/TransactionRepository.php
  ├─ Methods: findById, findByIdOrFail, allByUser (paginated), allByProject, allCompleted, create, update
  └─ Patterns: Filter by user/project/type/status, payment audit queries

backend/app/Repositories/ProductRepository.php
  ├─ Methods: findById, findByIdOrFail, findBySku, allActive (paginated), allByCategory, allInStock, create, update, delete, restore
  └─ Patterns: Catalog queries (active products, in-stock filters, category grouping)

backend/app/Repositories/OrderRepository.php
  ├─ Methods: findById (with items/transactions), findByIdOrFail, allByCustomer, allByProject, allPending, create, update, delete, restore
  └─ Patterns: Order lifecycle queries (pending, completed), customer isolation

backend/app/Repositories/WorkflowConfigurationRepository.php
  ├─ Methods: findById, findByIdOrFail, findGlobal, findByProject, allGlobal, allByProject, create, update, delete
  └─ Patterns: Workflow defaults (global) + per-project overrides

backend/app/Repositories/ApprovalRuleRepository.php
  ├─ Methods: findById, findByIdOrFail, allByWorkflowConfiguration, findRuleFor (entity_type, status_from→to), create, update, delete
  └─ Patterns: Approval workflow queries by entity type and transition
```

---

## Policies (8 files)

```
backend/app/Policies/UserPolicy.php
  ├─ viewAny: admin only
  ├─ view: own profile or admin
  ├─ create: admin only
  ├─ update: own profile or admin
  ├─ delete: admin only (not self)
  └─ restore, forceDelete: admin only

backend/app/Policies/ProjectPolicy.php
  ├─ viewAny: anyone (filtered by RBAC in repository)
  ├─ view: admin, customer, contractor, supervising_architect (cross-tenant isolation)
  ├─ create: customer or admin
  ├─ update: project customer or admin
  ├─ delete: project customer or admin
  ├─ approve: supervising_architect or admin
  └─ restore, forceDelete: admin only

backend/app/Policies/PhasePolicy.php
  ├─ viewAny: anyone
  ├─ view: project stakeholders (cross-tenant isolation)
  ├─ create: customer, contractor, admin
  ├─ update: project customer/contractor or admin
  ├─ delete: project customer or admin
  └─ restore, forceDelete: admin only

backend/app/Policies/TaskPolicy.php
  ├─ viewAny: anyone
  ├─ view: project stakeholders + assigned user (cross-tenant isolation)
  ├─ create: contractor, supervising_architect, admin
  ├─ update: project stakeholder/contractor/assigned or admin
  ├─ delete: project contractor or admin
  └─ restore, forceDelete: admin only

backend/app/Policies/ReportPolicy.php
  ├─ viewAny: anyone
  ├─ view: project stakeholders + creator (cross-tenant isolation)
  ├─ create: field_engineer, supervising_architect, admin only
  ├─ update: creator or admin
  ├─ delete: creator, supervising_architect, or admin
  └─ restore, forceDelete: admin only

backend/app/Policies/TransactionPolicy.php
  ├─ viewAny: admin only
  ├─ view: own transactions or admin (financial privacy)
  ├─ create: admin only
  ├─ update: admin only
  ├─ delete: admin only
  └─ restore, forceDelete: admin only

backend/app/Policies/ProductPolicy.php
  ├─ viewAny: anyone
  ├─ view: active products or admin
  ├─ create: admin only
  ├─ update: admin only
  ├─ delete: admin only
  └─ restore, forceDelete: admin only

backend/app/Policies/OrderPolicy.php
  ├─ viewAny: admin only
  ├─ view: own orders or admin (customer isolation)
  ├─ create: customer, contractor, admin
  ├─ update: own order or admin
  ├─ delete: own pending orders or admin
  └─ restore, forceDelete: admin only
```

---

## Seeders (5 files)

```
backend/database/seeders/RoleSeeder.php
  ├─ Creates 5 roles with Arabic descriptions
  ├─ Data: customer, contractor, supervising_architect, field_engineer, admin
  └─ Idempotent: uses firstOrCreate()

backend/database/seeders/PermissionSeeder.php
  ├─ Creates 28+ permissions
  ├─ Groups: project.*, phase.*, task.*, report.*, transaction.*, product.*, order.*
  └─ Idempotent: uses firstOrCreate()

backend/database/seeders/UserSeeder.php
  ├─ Creates 5 test users (1 per role)
  ├─ Emails: customer@example.com, contractor@example.com, architect@example.com, engineer@example.com, admin@example.com
  ├─ Password: "password" (hashed with bcrypt)
  ├─ Arabic names and phone numbers (Saudi Arabia format +966)
  └─ Idempotent: uses firstOrCreate()

backend/database/seeders/ProductSeeder.php
  ├─ Creates 10 building material products
  ├─ Products: cement, steel, sand, gravel, brick, ceramic, gypsum, paint, rods, glass
  ├─ Includes: SKU (unique), price, quantity_in_stock, category (Arabic), descriptions (Arabic)
  └─ Idempotent: uses firstOrCreate()

backend/database/seeders/DatabaseSeeder.php
  ├─ Orchestrator seeder
  ├─ Call order: RoleSeeder → PermissionSeeder → UserSeeder → ProductSeeder
  └─ Ensures correct dependency order for foreign key constraints
```

---

## Key Relationships Diagram

```
Users (5 roles)
├─ projects (as customer) → Projects
├─ contractor_projects (as contractor) → Projects
├─ supervised_projects (as supervisor) → Projects
├─ assigned_tasks → Tasks
├─ created_reports → Reports
└─ transactions → Transactions

Projects
├─ customer → Users
├─ contractor → Users (nullable)
├─ supervising_architect → Users (nullable)
├─ phases → Phases
├─ tasks → Tasks (via phases, HasManyThrough)
├─ reports → Reports
├─ transactions → Transactions
└─ orders → Orders

Phases
├─ project → Projects
├─ tasks → Tasks
└─ reports → Reports

Tasks
├─ phase → Phases
├─ assignee → Users (nullable)
└─ reports → Reports

Reports
├─ task → Tasks (nullable)
├─ phase → Phases (nullable)
├─ project → Projects (nullable)
└─ creator → Users

Transactions
├─ user → Users
├─ project → Projects (nullable)
└─ order → Orders (nullable)

Products
└─ orderItems → OrderItems

Orders
├─ customer → Users
├─ project → Projects (nullable)
├─ items → OrderItems
└─ transactions → Transactions

OrderItems (Pivot)
├─ order → Orders
└─ product → Products

Roles ↔ Permissions (Many-to-Many via role_permissions)

WorkflowConfigurations
├─ project → Projects (nullable, for global or per-project)
└─ approvalRules → ApprovalRules

ApprovalRules
└─ workflowConfiguration → WorkflowConfigurations
```

---

## Database Schema Summary

- **Tables:** 14
- **Foreign Keys:** 25+ with proper ON DELETE rules (CASCADE, RESTRICT, SET NULL)
- **Indexes:** 50+ on frequently queried columns (user_ids, status, created_at)
- **Soft Deletes:** 7 tables (users, projects, phases, tasks, reports, products, orders)
- **JSON Columns:** 4 (workflow_configurations, approval_requirements, specifications, attachments)
- **Charset:** UTF8MB4 with Arabic support throughout

---

## Validation Status

✅ All 14 migrations — Forward-only, reversible, proper foreign keys  
✅ All 13 models — Relationships, scopes, casts, soft deletes  
✅ All 10 repositories — Eager loading, filtering, pagination  
✅ All 8 policies — RBAC, cross-tenant isolation  
✅ All 5 seeders — Idempotent, correct dependency order  
✅ PHP syntax — Zero errors across all 50 files

---

**Ready for:** Phase 3 (API Controllers & HTTP Layer)
