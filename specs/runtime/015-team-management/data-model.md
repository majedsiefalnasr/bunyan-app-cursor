# Data Model — Team Management

## `project_members`

| Column       | Type        | Notes              |
| ------------ | ----------- | ------------------ |
| id           | bigint PK   |                    |
| project_id   | FK projects | cascade delete     |
| user_id      | FK users    | cascade delete     |
| project_role | string      | `ProjectRole` enum |
| joined_at    | timestamp   | default now        |
| created_at   | timestamp   |                    |
| updated_at   | timestamp   |                    |

**Constraints:** `unique(project_id, user_id)`

## `project_invitations`

| Column       | Type        | Notes                     |
| ------------ | ----------- | ------------------------- |
| id           | bigint PK   |                           |
| project_id   | FK projects | cascade delete            |
| email        | string(255) | normalized lowercase      |
| project_role | string      | `ProjectRole` (not owner) |
| token_hash   | string(128) | sha256 hex                |
| invited_by   | FK users    | nullOnDelete              |
| accepted_at  | timestamp   | nullable                  |
| expires_at   | timestamp   | required                  |
| created_at   | timestamp   |                           |
| updated_at   | timestamp   |                           |

**Indexes:** `project_id`, `token_hash` unique

## Relationships

- `Project::members()` → `hasMany(ProjectMember::class)`
- `Project::invitations()` → `hasMany(ProjectInvitation::class)`
- `ProjectMember::user()`, `ProjectInvitation::invitedByUser()`
