# Data Model — Activity Log

## Table: `activity_logs`

| Column          | Type            | Notes                                  |
| --------------- | --------------- | -------------------------------------- |
| id              | bigint PK       |                                        |
| user_id         | bigint FK null  | Actor; null for unauthenticated/system |
| action          | string(32)      | `created`, `updated`, `deleted`, …     |
| subject_type    | string          | Morph class name                       |
| subject_id      | bigint unsigned | Morph id                               |
| properties_json | json null       | Optional `{ old, new }` diff metadata  |
| ip_address      | string(45) null | IPv4/IPv6                              |
| user_agent      | text null       |                                        |
| created_at      | timestamp       | No `updated_at`                        |

**Indexes:** (`subject_type`, `subject_id`), (`user_id`), (`action`), (`created_at`).

## Relationships

- `ActivityLog` `belongsTo` `User` as `actor` (optional).
- Subjects (`Project`, …) may expose `morphMany` `activityLogs` (optional convenience).
