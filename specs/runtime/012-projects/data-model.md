# Data Model — Projects

## `projects`

| Column                   | Type         | Notes                                       |
| ------------------------ | ------------ | ------------------------------------------- |
| id                       | bigint PK    |                                             |
| name                     | string       | retained                                    |
| name_ar                  | string null  |                                             |
| name_en                  | string null  |                                             |
| description              | text null    |                                             |
| customer_id              | FK users     | owner                                       |
| contractor_id            | FK null      |                                             |
| supervising_architect_id | FK null      |                                             |
| status                   | string       | enum ProjectStatus                          |
| budget                   | decimal      | legacy estimated                            |
| budget_estimated         | decimal null |                                             |
| budget_actual            | decimal null |                                             |
| location                 | string       | legacy single line                          |
| city                     | string null  |                                             |
| district                 | string null  |                                             |
| location_lat             | decimal null |                                             |
| location_lng             | decimal null |                                             |
| project_type             | string null  | residential \| commercial \| infrastructure |
| start_date               | date null    |                                             |
| end_date                 | date null    |                                             |
| timestamps, deleted_at   |              | soft deletes                                |

## `phases`

| Column     | Type        | Notes                |
| ---------- | ----------- | -------------------- |
| name_ar    | string null |                      |
| name_en    | string null |                      |
| sort_order | int         | default 0            |
| progress   | int         | completion % (0–100) |

Relationships unchanged: `Project hasMany Phase`, etc.
