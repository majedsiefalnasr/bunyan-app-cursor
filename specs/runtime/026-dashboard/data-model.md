# Data Model — Dashboard (read-only)

No new tables. Aggregates read from:

| Entity              | Columns / notes used                                       |
| ------------------- | ---------------------------------------------------------- |
| `users`             | `id`, `role`, `active`                                     |
| `projects`          | `customer_id`, `contractor_id`, `supervising_architect_id` |
| `orders`            | `customer_id`, `supplier_id`, `status`, `total_amount`     |
| `supplier_profiles` | `id`, `user_id`                                            |
| `tasks`             | `assigned_to`                                              |
| `reports`           | `created_by`                                               |
| `activity_logs`     | `user_id`, `action`, `subject_*`, `created_at`             |

Relationships: standard Eloquent relations on `User` (`projects`, `orders`, `assignedTasks`, `reports`, `supervisedProjects`, `contractorProjects`).
