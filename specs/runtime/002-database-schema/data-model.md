# Data Model — Database Schema Foundation

## New Migration: role_user

```sql
CREATE TABLE `role_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `assigned_by` bigint unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_user_user_id_role_id_unique` (`user_id`,`role_id`),
  KEY `role_user_user_id_index` (`user_id`),
  KEY `role_user_role_id_index` (`role_id`),
  KEY `role_user_assigned_by_index` (`assigned_by`),
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Enum Backing Values (DB Storage)

| Enum              | Column | Table                   | Possible Values                                                    |
| ----------------- | ------ | ----------------------- | ------------------------------------------------------------------ |
| UserRole          | role   | users                   | customer, contractor, supervising_architect, field_engineer, admin |
| ProjectStatus     | status | projects                | pending, active, on_hold, completed, cancelled                     |
| PhaseStatus       | status | phases                  | pending, in_progress, completed, approved, rejected                |
| TaskStatus        | status | tasks                   | pending, in_progress, completed, approved, rejected                |
| OrderStatus       | status | orders                  | pending, processing, shipped, delivered, cancelled, refunded       |
| TransactionType   | type   | transactions            | payment, withdrawal, refund, commission                            |
| TransactionStatus | status | transactions            | pending, completed, failed, cancelled                              |
| WorkflowType      | type   | workflow_configurations | project, phase, task                                               |
| ApprovalStatus    | status | approval_rules          | pending, approved, rejected                                        |
| ReportType        | type   | reports                 | progress, inspection, incident, completion                         |

## Class Hierarchy

```
Illuminate\Database\Eloquent\Model
  └── App\Models\BaseModel (abstract)
        ├── App\Models\Project
        ├── App\Models\Phase
        ├── App\Models\Task
        ├── App\Models\Report
        ├── App\Models\Role
        ├── App\Models\Permission
        ├── App\Models\ApprovalRule
        ├── App\Models\WorkflowConfiguration
        ├── App\Models\Order
        ├── App\Models\OrderItem
        ├── App\Models\Product
        └── App\Models\Transaction

Illuminate\Foundation\Auth\User (Authenticatable → Model)
  └── App\Models\User
        └── uses App\Models\Concerns\HasBaseModelBehavior (trait)

App\Repositories\BaseRepository (abstract)
  ├── App\Repositories\UserRepository
  ├── App\Repositories\ProjectRepository
  ├── App\Repositories\PhaseRepository
  ├── App\Repositories\TaskRepository
  ├── App\Repositories\ReportRepository
  ├── App\Repositories\ApprovalRuleRepository
  ├── App\Repositories\WorkflowConfigurationRepository
  ├── App\Repositories\OrderRepository
  ├── App\Repositories\ProductRepository
  └── App\Repositories\TransactionRepository
```

## File Map

### New Files

| Path                                                                       | Type      | Description                    |
| -------------------------------------------------------------------------- | --------- | ------------------------------ |
| `backend/app/Enums/UserRole.php`                                           | Enum      | User role enum                 |
| `backend/app/Enums/ProjectStatus.php`                                      | Enum      | Project status                 |
| `backend/app/Enums/PhaseStatus.php`                                        | Enum      | Phase status                   |
| `backend/app/Enums/TaskStatus.php`                                         | Enum      | Task status                    |
| `backend/app/Enums/OrderStatus.php`                                        | Enum      | Order status                   |
| `backend/app/Enums/TransactionType.php`                                    | Enum      | Transaction type               |
| `backend/app/Enums/TransactionStatus.php`                                  | Enum      | Transaction status             |
| `backend/app/Enums/WorkflowType.php`                                       | Enum      | Workflow type                  |
| `backend/app/Enums/ApprovalStatus.php`                                     | Enum      | Approval status                |
| `backend/app/Enums/ReportType.php`                                         | Enum      | Report type                    |
| `backend/app/Models/BaseModel.php`                                         | Class     | Abstract base model            |
| `backend/app/Models/Concerns/HasBaseModelBehavior.php`                     | Trait     | Shared model behavior for User |
| `backend/app/Repositories/BaseRepository.php`                              | Class     | Abstract base repository       |
| `backend/database/migrations/2026_04_11_120000_create_role_user_table.php` | Migration | role_user pivot                |
| `backend/database/seeders/RolePermissionSeeder.php`                        | Seeder    | Role-permission assignments    |
| `backend/tests/Unit/Enums/UserRoleTest.php`                                | Test      | UserRole enum test             |
| `backend/tests/Unit/Enums/ProjectStatusTest.php`                           | Test      | ProjectStatus enum test        |
| `backend/tests/Unit/Enums/PhaseStatusTest.php`                             | Test      | PhaseStatus enum test          |
| `backend/tests/Unit/Enums/TaskStatusTest.php`                              | Test      | TaskStatus enum test           |
| `backend/tests/Unit/Enums/OtherEnumsTest.php`                              | Test      | Remaining 6 enums              |
| `backend/tests/Unit/Repositories/BaseRepositoryTest.php`                   | Test      | BaseRepository contract        |
| `backend/tests/Feature/Database/DatabaseSchemaTest.php`                    | Test      | Table/column assertions        |
| `backend/tests/Feature/Database/MigrationRollbackTest.php`                 | Test      | Rollback integrity             |
| `backend/tests/Feature/Database/SeederTest.php`                            | Test      | Seeder data validation         |
| `backend/tests/Feature/Database/SoftDeleteTest.php`                        | Test      | Soft delete behavior           |
| `backend/tests/Feature/Database/EnumCastTest.php`                          | Test      | Enum casting E2E               |

### Modified Files

| Path                                                           | Change                                                                                |
| -------------------------------------------------------------- | ------------------------------------------------------------------------------------- |
| `backend/app/Models/User.php`                                  | Add `HasBaseModelBehavior` trait, cast `role` to `UserRole`                           |
| `backend/app/Models/Project.php`                               | Extend `BaseModel`, cast `status` to `ProjectStatus`                                  |
| `backend/app/Models/Phase.php`                                 | Extend `BaseModel`, cast `status` to `PhaseStatus`                                    |
| `backend/app/Models/Task.php`                                  | Extend `BaseModel`, cast `status` to `TaskStatus`                                     |
| `backend/app/Models/Order.php`                                 | Extend `BaseModel`, cast `status` to `OrderStatus`                                    |
| `backend/app/Models/Transaction.php`                           | Extend `BaseModel`, cast `type` to `TransactionType`, `status` to `TransactionStatus` |
| `backend/app/Models/WorkflowConfiguration.php`                 | Extend `BaseModel`, cast `type` to `WorkflowType`                                     |
| `backend/app/Models/ApprovalRule.php`                          | Extend `BaseModel`, cast `status` to `ApprovalStatus`                                 |
| `backend/app/Models/Report.php`                                | Extend `BaseModel`, cast `type` to `ReportType`                                       |
| `backend/app/Models/Role.php`                                  | Extend `BaseModel`                                                                    |
| `backend/app/Models/Permission.php`                            | Extend `BaseModel`                                                                    |
| `backend/app/Models/OrderItem.php`                             | Extend `BaseModel`                                                                    |
| `backend/app/Models/Product.php`                               | Extend `BaseModel`                                                                    |
| `backend/app/Repositories/UserRepository.php`                  | Extend `BaseRepository`                                                               |
| `backend/app/Repositories/ProjectRepository.php`               | Extend `BaseRepository`                                                               |
| `backend/app/Repositories/PhaseRepository.php`                 | Extend `BaseRepository`                                                               |
| `backend/app/Repositories/TaskRepository.php`                  | Extend `BaseRepository`                                                               |
| `backend/app/Repositories/ReportRepository.php`                | Extend `BaseRepository`                                                               |
| `backend/app/Repositories/ApprovalRuleRepository.php`          | Extend `BaseRepository`                                                               |
| `backend/app/Repositories/WorkflowConfigurationRepository.php` | Extend `BaseRepository`                                                               |
| `backend/app/Repositories/OrderRepository.php`                 | Extend `BaseRepository`                                                               |
| `backend/app/Repositories/ProductRepository.php`               | Extend `BaseRepository`                                                               |
| `backend/app/Repositories/TransactionRepository.php`           | Extend `BaseRepository`                                                               |
| `backend/database/factories/UserFactory.php`                   | Add role states + inactive state                                                      |
| `backend/database/factories/ProjectFactory.php`                | Add status states                                                                     |
| `backend/database/factories/PhaseFactory.php`                  | Add status states                                                                     |
| `backend/database/factories/TaskFactory.php`                   | Add status states                                                                     |
| `backend/database/seeders/DatabaseSeeder.php`                  | Add RolePermissionSeeder, correct ordering                                            |
