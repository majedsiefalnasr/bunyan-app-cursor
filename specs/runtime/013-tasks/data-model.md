# Data Model — Tasks

## `tasks` (extensions)

| Column          | Type              | Notes                                     |
| --------------- | ----------------- | ----------------------------------------- |
| project_id      | FK projects       | Backfilled from phase                     |
| title_ar        | string nullable   | Primary display AR                        |
| title_en        | string nullable   | Optional EN                               |
| priority        | string            | low, medium, high, urgent                 |
| due_date        | date nullable     |                                           |
| estimated_hours | decimal nullable  |                                           |
| actual_hours    | decimal nullable  |                                           |
| sort_order      | int               | default 0                                 |
| created_by      | FK users nullable |                                           |
| name            | string            | retained; sync from title_ar when missing |

## `task_comments`

| Column     | Type             |
| ---------- | ---------------- |
| id         | PK               |
| task_id    | FK tasks cascade |
| user_id    | FK users cascade |
| body       | text             |
| timestamps |                  |

## `task_dependencies`

| Column                              | Type             |
| ----------------------------------- | ---------------- |
| id                                  | PK               |
| task_id                             | FK tasks cascade |
| depends_on_task_id                  | FK tasks cascade |
| unique(task_id, depends_on_task_id) |                  |

## Status migration map

| Old         | New         |
| ----------- | ----------- |
| pending     | todo        |
| in_progress | in_progress |
| completed   | done        |
| approved    | done        |
| rejected    | blocked     |
