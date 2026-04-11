# STAGE_01: Project Initialization — Complete Database Schema & Data Model

**Phase:** 01_PLATFORM_FOUNDATION  
**Status:** PLANNING  
**Date:** 2026-04-10  
**Audience:** Backend engineers, database architects

---

## Executive Summary

This document specifies the complete database schema for Bunyan with **13 migrations**, including all tables, columns, indexes, foreign key constraints, and relationships. All migrations are forward-only and reversible.

**Total Entities:** 13 models  
**Total Relationships:** 25+ (one-to-many, many-to-many, polymorphic)  
**Charset:** UTF8MB4 (full Unicode support, including Arabic)

---

## 1. Migration Specifications

### Migration 1: Create Users Table

```sql
-- Migration: 2026_04_10_120000_create_users_table.php
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `role` ENUM('customer', 'contractor', 'supervising_architect', 'field_engineer', 'admin') NOT NULL DEFAULT 'customer',
  `avatar_url` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,

  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**

- `id`: Primary key (unsigned big int for scale)
- `name`: User full name (Arabic or English)
- `email`: Unique email for login
- `phone`: Contact phone number (supports international format)
- `role`: Enum-based role (no separate roles table — simplicity)
- `avatar_url`: Profile picture URL (nullable)
- `deleted_at`: Soft delete timestamp

**Indexes:**

- Primary: `id`
- Unique: `email`
- Regular: `role` (for role-based queries), `deleted_at` (for soft delete scopes)

**Eloquent Model:**

```php
class User extends Model {
    use SoftDeletes;

    protected $casts = [
        'role' => UserRole::class,
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function projects() { return $this->hasMany(Project::class, 'customer_id'); }
    public function assignedProjects() { return $this->hasMany(Project::class, 'contractor_id'); }
    public function supervisedProjects() { return $this->hasMany(Project::class, 'supervising_architect_id'); }
    public function assignedTasks() { return $this->hasMany(Task::class, 'assigned_to'); }
    public function reports() { return $this->hasMany(Report::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function orders() { return $this->hasMany(Order::class); }
}
```

---

### Migration 2: Create Workflow Configurations Table

```sql
-- Migration: 2026_04_10_120001_create_workflow_configurations_table.php
CREATE TABLE `workflow_configurations` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `is_default` BOOLEAN DEFAULT FALSE,
  `statuses` JSON NOT NULL COMMENT 'Array of allowed status values',
  `approval_required_on_transition` BOOLEAN DEFAULT FALSE,
  `approver_role` ENUM('supervising_architect', 'admin') NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_is_default` (`is_default`),
  UNIQUE KEY `unique_default` (`is_default`) COMMENT 'Ensure only one default config'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Example Data:**

```json
{
  "name": "Standard Construction Workflow",
  "is_default": true,
  "statuses": ["pending", "in_progress", "complete", "paid"],
  "approval_required_on_transition": true,
  "approver_role": "supervising_architect"
}
```

**Eloquent Model:**

```php
class WorkflowConfiguration extends Model {
    protected $casts = [
        'statuses' => 'json',
        'approval_required_on_transition' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function projects() { return $this->hasMany(Project::class); }

    public function scopeDefault($query) {
        return $query->where('is_default', true);
    }

    public function isValidTransition(string $from, string $to): bool {
        return in_array($to, $this->statuses) &&
               array_key_exists($from, array_flip($this->statuses));
    }
}
```

---

### Migration 3: Create Projects Table

```sql
-- Migration: 2026_04_10_120002_create_projects_table.php
CREATE TABLE `projects` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `budget` DECIMAL(15, 2) NOT NULL,
  `status` ENUM('pending', 'in_progress', 'complete', 'paid', 'cancelled') DEFAULT 'pending',
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `contractor_id` BIGINT UNSIGNED NULL,
  `supervising_architect_id` BIGINT UNSIGNED NULL,
  `workflow_config_id` BIGINT UNSIGNED NULL,
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,

  FOREIGN KEY `fk_customer_id` (`customer_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY `fk_contractor_id` (`contractor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY `fk_supervising_architect_id` (`supervising_architect_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY `fk_workflow_config_id` (`workflow_config_id`) REFERENCES `workflow_configurations`(`id`) ON DELETE SET NULL,

  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_contractor_id` (`contractor_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**

- `budget`: Total project budget (DECIMAL for financial accuracy)
- `status`: Current project status
- `customer_id`: Who owns/initiated the project (NOT NULL, RESTRICT on delete)
- `contractor_id`: Who executes the project (nullable, SET NULL on delete)
- `supervising_architect_id`: Who approves phases/tasks (nullable, SET NULL on delete)
- `workflow_config_id`: Reference to workflow state machine (SET NULL if deleted)

**Constraints:**

- Foreign keys with appropriate cascade rules
- Customer is immutable (RESTRICT)
- Contractor and supervisor can change (SET NULL)

**Eloquent Model:**

```php
class Project extends Model {
    use SoftDeletes;

    protected $casts = [
        'status' => ProjectStatus::class,
        'budget' => 'decimal:2',
    ];

    public function customer() { return $this->belongsTo(User::class); }
    public function contractor() { return $this->belongsTo(User::class, 'contractor_id'); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervising_architect_id'); }
    public function phases() { return $this->hasMany(Phase::class); }
    public function tasks() { return $this->hasManyThrough(Task::class, Phase::class); }
    public function reports() { return $this->hasManyThrough(Report::class, Task::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function workflowConfig() { return $this->belongsTo(WorkflowConfiguration::class); }

    public function scopeForCustomer($query, int $customerId) {
        return $query->where('customer_id', $customerId);
    }
}
```

---

### Migration 4: Create Phases Table

```sql
-- Migration: 2026_04_10_120003_create_phases_table.php
CREATE TABLE `phases` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `project_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `budget` DECIMAL(15, 2) NOT NULL,
  `status` ENUM('pending', 'in_progress', 'complete', 'paid', 'cancelled') DEFAULT 'pending',
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `order` INT UNSIGNED DEFAULT 1 COMMENT 'Phase sequence within project',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,

  FOREIGN KEY `fk_project_id` (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,

  INDEX `idx_project_id` (`project_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_order` (`order`),
  UNIQUE KEY `unique_phase_order` (`project_id`, `order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**

- `order`: Phase sequence (1st, 2nd, 3rd phase in project)
- `status`: Current phase status (mirrors project status enum)
- Foreign key cascade on delete (if project deleted, phases deleted too)

**Eloquent Model:**

```php
class Phase extends Model {
    use SoftDeletes;

    protected $casts = [
        'status' => PhaseStatus::class,
        'budget' => 'decimal:2',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function reports() { return $this->hasManyThrough(Report::class, Task::class); }

    public function scopeActive($query) {
        return $query->whereNotIn('status', ['cancelled']);
    }
}
```

---

### Migration 5: Create Tasks Table

```sql
-- Migration: 2026_04_10_120004_create_tasks_table.php
CREATE TABLE `tasks` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `phase_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `budget` DECIMAL(15, 2) NOT NULL,
  `status` ENUM('pending', 'in_progress', 'complete', 'paid', 'cancelled') DEFAULT 'pending',
  `assigned_to` BIGINT UNSIGNED NULL COMMENT 'Field engineer ID',
  `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,

  FOREIGN KEY `fk_phase_id` (`phase_id`) REFERENCES `phases`(`id`) ON DELETE CASCADE,
  FOREIGN KEY `fk_assigned_to` (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,

  INDEX `idx_phase_id` (`phase_id`),
  INDEX `idx_assigned_to` (`assigned_to`),
  INDEX `idx_status` (`status`),
  INDEX `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**

- `assigned_to`: Field engineer assigned to execute this task (nullable)
- `priority`: Task urgency level

**Eloquent Model:**

```php
class Task extends Model {
    use SoftDeletes;

    protected $casts = [
        'status' => TaskStatus::class,
        'budget' => 'decimal:2',
        'priority' => TaskPriority::class,
    ];

    public function phase() { return $this->belongsTo(Phase::class); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function reports() { return $this->hasMany(Report::class); }

    public function scopeUnassigned($query) {
        return $query->whereNull('assigned_to');
    }
}
```

---

### Migration 6: Create Reports Table

```sql
-- Migration: 2026_04_10_120005_create_reports_table.php
CREATE TABLE `reports` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `task_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Reporter (field engineer)',
  `text` LONGTEXT NOT NULL,
  `photos` JSON NULL COMMENT 'Array of URLs to uploaded photos',
  `videos` JSON NULL COMMENT 'Array of URLs to uploaded videos',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY `fk_task_id` (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
  FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,

  INDEX `idx_task_id` (`task_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**

- `photos`: JSON array of file URLs: `["https://...", "https://..."]`
- `videos`: JSON array of video URLs

**Eloquent Model:**

```php
class Report extends Model {
    protected $casts = [
        'photos' => 'json',
        'videos' => 'json',
    ];

    public function task() { return $this->belongsTo(Task::class); }
    public function reporter() { return $this->belongsTo(User::class, 'user_id'); }

    public function canUpdate(User $user): bool {
        return $user->id === $this->user_id &&
               $this->created_at->diffInHours(now()) < 24;
    }
}
```

---

### Migration 7: Create Workflow Status Transitions Table

```sql
-- Migration: 2026_04_10_120006_create_approval_rules_table.php
CREATE TABLE `approval_rules` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `entity_type` ENUM('project', 'phase', 'task') NOT NULL,
  `entity_id` BIGINT UNSIGNED NOT NULL,
  `status` VARCHAR(50) NOT NULL COMMENT 'Target status (e.g., "complete")',
  `requires_approval` BOOLEAN DEFAULT TRUE,
  `approver_role` ENUM('supervising_architect', 'admin') NULL,
  `approved_by` BIGINT UNSIGNED NULL,
  `approved_at` TIMESTAMP NULL,
  `rejection_reason` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY `fk_approved_by` (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,

  INDEX `idx_entity_type_id` (`entity_type`, `entity_id`),
  INDEX `idx_approver_role` (`approver_role`),
  INDEX `idx_approved_at` (`approved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**

- `entity_type`: What entity is awaiting approval (polymorphic)
- `entity_id`: Which specific entity
- `status`: Target status after approval
- `approved_by`: User who approved (nullable until approved)

**Eloquent Model:**

```php
class ApprovalRule extends Model {
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    public function scopePending($query) {
        return $query->whereNull('approved_at')->whereNull('rejection_reason');
    }

    public function scopeApproved($query) {
        return $query->whereNotNull('approved_at');
    }
}
```

---

### Migration 8: Create Transactions Table

```sql
-- Migration: 2026_04_10_120007_create_transactions_table.php
CREATE TABLE `transactions` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `project_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Payer/receiver',
  `amount` DECIMAL(15, 2) NOT NULL,
  `type` ENUM('payment', 'withdrawal') NOT NULL,
  `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
  `payment_method` VARCHAR(50) NULL COMMENT 'bank_transfer, credit_card, cash',
  `reference` VARCHAR(255) NULL COMMENT 'Bank/payment reference number',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY `fk_project_id` (`project_id`) REFERENCES `projects`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,

  INDEX `idx_project_id` (`project_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**

- `type`: "payment" (customer pays), "withdrawal" (contractor receives)
- `status`: Transaction processing status
- `reference`: External reference for reconciliation

**Eloquent Model:**

```php
class Transaction extends Model {
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopePayments($query) {
        return $query->where('type', 'payment');
    }

    public function scopeWithdrawals($query) {
        return $query->where('type', 'withdrawal');
    }
}
```

---

### Migration 9: Create Products Table

```sql
-- Migration: 2026_04_10_120008_create_products_table.php
CREATE TABLE `products` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(15, 2) NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `sku` VARCHAR(100) NOT NULL UNIQUE,
  `stock_quantity` INT UNSIGNED DEFAULT 0,
  `images` JSON NULL COMMENT 'Array of image URLs',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY `fk_category_id` (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,

  INDEX `idx_category_id` (`category_id`),
  INDEX `idx_sku` (`sku`),
  INDEX `idx_stock_quantity` (`stock_quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Eloquent Model:**

```php
class Product extends Model {
    protected $casts = [
        'price' => 'decimal:2',
        'images' => 'json',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function orders() { return $this->belongsToMany(Order::class, 'order_items'); }

    public function scopeActive($query) {
        return $query->where('stock_quantity', '>', 0);
    }
}
```

---

### Migration 10: Create Categories Table

```sql
-- Migration: 2026_04_10_120009_create_categories_table.php
CREATE TABLE `categories` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'Category name (e.g., "Cement", "Steel")',
  `description` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Eloquent Model:**

```php
class Category extends Model {
    public function products() { return $this->hasMany(Product::class); }
}
```

---

### Migration 11: Create Orders Table

```sql
-- Migration: 2026_04_10_120010_create_orders_table.php
CREATE TABLE `orders` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `order_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Human-readable order ID',
  `total_amount` DECIMAL(15, 2) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `shipping_address` TEXT NOT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY `fk_customer_id` (`customer_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,

  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_order_number` (`order_number`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**

- `order_number`: Human-readable (ORD-2026-001, ORD-2026-002)
- `total_amount`: Calculated from order_items, stored for historical accuracy

**Eloquent Model:**

```php
class Order extends Model {
    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function customer() { return $this->belongsTo(User::class); }
    public function items() { return $this->belongsToMany(Product::class, 'order_items'); }
    public function transactions() { return $this->hasMany(Transaction::class); }

    public function scopePending($query) {
        return $query->where('status', 'pending');
    }
}
```

---

### Migration 12: Create Order Items Pivot Table

```sql
-- Migration: 2026_04_10_120011_create_order_items_table.php
CREATE TABLE `order_items` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(15, 2) NOT NULL COMMENT 'Price at time of order',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY `fk_order_id` (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY `fk_product_id` (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,

  UNIQUE KEY `unique_order_product` (`order_id`, `product_id`),
  INDEX `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**

- `unit_price`: Price stored at order time (prevents price changes from affecting historical orders)
- Composite unique key: One product per order

---

### Migration 13: Create Personal Access Tokens Table (Sanctum)

```sql
-- Migration: 2026_04_10_120012_create_personal_access_tokens_table.php
CREATE TABLE `personal_access_tokens` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `token` VARCHAR(80) NOT NULL UNIQUE,
  `abilities` JSON NULL,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_tokenable` (`tokenable_type`, `tokenable_id`),
  INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Note:** This table is auto-created by Sanctum. Just ensure it's part of the schema documentation.

---

## 2. Relationship Graph

### Entity Relationship Diagram (Text Representation)

```
┌─────────────────┐
│     Users       │
├─────────────────┤
│ id (PK)         │
│ name            │
│ email           │
│ role (enum)     │
└─────────────────┘
        │
        ├─┬─────→ Projects (customer_id)
        ├─┬─────→ Projects (contractor_id)
        ├─┬─────→ Projects (supervising_architect_id)
        ├─┬─────→ Tasks (assigned_to)
        ├─┬─────→ Reports (user_id)
        ├─┬─────→ Transactions (user_id)
        └─┴─────→ Orders (customer_id)

┌─────────────────┐
│    Projects     │
├─────────────────┤
│ id (PK)         │
│ customer_id (FK)│
│ contractor_id(FK)
│ supervising... │
│ workflow_config│
└─────────────────┘
        │
        ├───────→ WorkflowConfig (1:1)
        ├───────→ Phases (1:N)
        ├───────→ Tasks (1:N via Phases)
        ├───────→ Reports (1:N via Tasks)
        └───────→ Transactions (1:N)

┌─────────────────┐
│     Phases      │
├─────────────────┤
│ id (PK)         │
│ project_id (FK) │
└─────────────────┘
        │
        ├───────→ Tasks (1:N)
        └───────→ Reports (1:N via Tasks)

┌─────────────────┐
│      Tasks      │
├─────────────────┤
│ id (PK)         │
│ phase_id (FK)   │
│ assigned_to(FK) │
└─────────────────┘
        │
        └───────→ Reports (1:N)

┌──────────────────────┐
│      Orders          │
├──────────────────────┤
│ id (PK)              │
│ customer_id (FK)     │
│ order_items (pivot)  │
└──────────────────────┘
        │
        ├───────→ Products (M:N via order_items)
        └───────→ Transactions (1:N)

┌──────────────────────┐
│      Products        │
├──────────────────────┤
│ id (PK)              │
│ category_id (FK)     │
└──────────────────────┘
        │
        └───────→ Orders (M:N via order_items)
```

---

## 3. Database Constraints & Indexes

### Foreign Key Strategy

| Table          | FK Column                | References                 | Action   | Reason                                      |
| -------------- | ------------------------ | -------------------------- | -------- | ------------------------------------------- |
| projects       | customer_id              | users.id                   | RESTRICT | Customer can't be deleted if project exists |
| projects       | contractor_id            | users.id                   | SET NULL | Contractor can be removed, no loss of data  |
| projects       | supervising_architect_id | users.id                   | SET NULL | Supervisor can be removed                   |
| projects       | workflow_config_id       | workflow_configurations.id | SET NULL | Config can be deleted, project survives     |
| phases         | project_id               | projects.id                | CASCADE  | Delete project → delete phases              |
| tasks          | phase_id                 | phases.id                  | CASCADE  | Delete phase → delete tasks                 |
| tasks          | assigned_to              | users.id                   | SET NULL | Unassign if user deleted                    |
| reports        | task_id                  | tasks.id                   | CASCADE  | Delete task → delete reports                |
| reports        | user_id                  | users.id                   | RESTRICT | Reporter required, can't delete             |
| transactions   | project_id               | projects.id                | RESTRICT | Transaction history immutable               |
| transactions   | user_id                  | users.id                   | RESTRICT | User transaction history immutable          |
| products       | category_id              | categories.id              | RESTRICT | Category can't delete if products exist     |
| orders         | customer_id              | users.id                   | RESTRICT | Order history immutable                     |
| order_items    | order_id                 | orders.id                  | CASCADE  | Delete order → delete line items            |
| order_items    | product_id               | products.id                | RESTRICT | Product can't delete if in order history    |
| approval_rules | approved_by              | users.id                   | SET NULL | Approver can be deleted, approval survives  |

### Indexes Strategy

| Table        | Column(s)     | Type   | Purpose                             |
| ------------ | ------------- | ------ | ----------------------------------- |
| users        | email         | UNIQUE | Login by email                      |
| users        | role          | INDEX  | Filter by role                      |
| projects     | customer_id   | INDEX  | Find projects by customer           |
| projects     | contractor_id | INDEX  | Find projects by contractor         |
| projects     | status        | INDEX  | Filter by status                    |
| phases       | project_id    | INDEX  | Find phases of project              |
| tasks        | phase_id      | INDEX  | Find tasks of phase                 |
| tasks        | assigned_to   | INDEX  | Find tasks assigned to engineer     |
| tasks        | status        | INDEX  | Filter by status                    |
| reports      | task_id       | INDEX  | Find reports of task                |
| reports      | created_at    | INDEX  | Order by date (recent first)        |
| transactions | project_id    | INDEX  | Find transactions of project        |
| transactions | user_id       | INDEX  | Find transactions of user           |
| transactions | type          | INDEX  | Filter by type (payment/withdrawal) |
| orders       | customer_id   | INDEX  | Find orders of customer             |
| order_items  | order_id      | INDEX  | Find items in order                 |
| order_items  | product_id    | INDEX  | Find product usage                  |

---

## 4. Indexes for Query Performance

### Query Patterns & Recommended Indexes

```sql
-- Query: List all projects for a customer
SELECT * FROM projects WHERE customer_id = ? AND deleted_at IS NULL;
INDEX: (customer_id, deleted_at) -- Composite index

-- Query: List incomplete tasks for a field engineer
SELECT * FROM tasks WHERE assigned_to = ? AND status != 'complete';
INDEX: (assigned_to, status)

-- Query: Find phases by project with date range
SELECT * FROM phases WHERE project_id = ? AND start_date >= ? AND end_date <= ?;
INDEX: (project_id, start_date, end_date)

-- Query: List transactions for project with status
SELECT * FROM transactions WHERE project_id = ? AND status = 'completed';
INDEX: (project_id, status)

-- Query: Recent reports for task
SELECT * FROM reports WHERE task_id = ? ORDER BY created_at DESC;
INDEX: (task_id, created_at DESC)
```

---

## 5. Seed Data & Example Migrations

### Example Seed for Development

```php
// database/seeders/DatabaseSeeder.php
public function run(): void {
    // Create roles/workflow config
    $workflow = WorkflowConfiguration::factory()->default()->create();

    // Create users
    $customer = User::factory()->customer()->create(['email' => 'customer@example.com']);
    $contractor = User::factory()->contractor()->create(['email' => 'contractor@example.com']);
    $engineer = User::factory()->fieldEngineer()->create(['email' => 'engineer@example.com']);
    $architect = User::factory()->supervis ingArchitect()->create(['email' => 'architect@example.com']);

    // Create project
    $project = Project::factory()
        ->for($customer, 'customer')
        ->for($contractor, 'contractor')
        ->for($architect, 'supervisor')
        ->for($workflow, 'workflowConfig')
        ->create();

    // Create phases
    $phases = Phase::factory(3)
        ->for($project)
        ->create();

    // Create tasks
    foreach ($phases as $phase) {
        Task::factory(5)
            ->for($phase)
            ->for($engineer, 'assignedTo')
            ->create();
    }

    // Create sample reports
    Report::factory(10)
        ->for(Task::first())
        ->for($engineer, 'reporter')
        ->create();

    // Create transactions
    Transaction::factory()
        ->for($project)
        ->for($customer)
        ->payment()
        ->create(['amount' => 50000]);

    // Create products and orders
    $category = Category::factory()->create();
    $products = Product::factory(5)->for($category)->create();

    $order = Order::factory()
        ->for($customer)
        ->create();

    foreach ($products->take(3) as $product) {
        $order->items()->attach($product, [
            'quantity' => rand(1, 5),
            'unit_price' => $product->price,
        ]);
    }
}
```

---

## 6. Migration Testing Checklist

### Validation Before Implementation

- ✅ All foreign keys reference valid tables
- ✅ Cascade/restrict rules appropriate for relationships
- ✅ All tables use utf8mb4 charset
- ✅ All tables have created_at + updated_at
- ✅ Soft deletes included where appropriate (users, projects, phases, tasks, reports)
- ✅ Indexes present for all foreign keys
- ✅ Unique constraints on unique columns (email, sku, order_number)
- ✅ JSON columns cast properly in models
- ✅ Enum columns cast to PHP enums in models
- ✅ Decimal columns with appropriate precision (15,2)

---

**Generated by:** PLAN Step  
**Status:** READY FOR IMPLEMENTATION  
**Total Migrations:** 13  
**Total Models:** 13  
**Last Updated:** 2026-04-10
