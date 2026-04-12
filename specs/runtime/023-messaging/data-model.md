# Data Model — Messaging

## conversations

| Column     | Type               | Notes                                    |
| ---------- | ------------------ | ---------------------------------------- |
| id         | bigint PK          |                                          |
| project_id | bigint FK nullable | `projects.id`, null = not project-scoped |
| title      | string nullable    | Required for `group`                     |
| type       | string             | `direct`, `group`                        |
| created_at | timestamp          |                                          |
| updated_at | timestamp          |                                          |

## conversation_participants

| Column                           | Type               | Notes                      |
| -------------------------------- | ------------------ | -------------------------- |
| id                               | bigint PK          |                            |
| conversation_id                  | bigint FK          | cascade delete             |
| user_id                          | bigint FK          | `users.id`, cascade delete |
| last_read_at                     | timestamp nullable |                            |
| joined_at                        | timestamp          |                            |
| unique(conversation_id, user_id) |                    |                            |

## messages

| Column          | Type               | Notes          |
| --------------- | ------------------ | -------------- |
| id              | bigint PK          |                |
| conversation_id | bigint FK          | cascade delete |
| sender_id       | bigint FK          | `users.id`     |
| body            | text nullable      |                |
| type            | string             | `text`, `file` |
| attachment_path | string nullable    | disk path      |
| deleted_at      | timestamp nullable | soft delete    |
| created_at      | timestamp          |                |
| updated_at      | timestamp          |                |

## Relationships

- `Conversation` hasMany `participants`, `messages`; belongsTo `project` optional.
- `ConversationParticipant` belongsTo `conversation`, `user`.
- `Message` belongsTo `conversation`, `sender` (User).
