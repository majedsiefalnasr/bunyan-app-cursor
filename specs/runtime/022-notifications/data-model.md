# Notifications — Data Model

## `notifications`

| Column          | Type             | Notes                                     |
| --------------- | ---------------- | ----------------------------------------- |
| id              | CHAR(36) UUID    | Primary key                               |
| type            | string           | Fully-qualified notification class name   |
| notifiable_type | string           | Morph type (`App\Models\User`)            |
| notifiable_id   | unsignedBigInt   | FK to `users.id`                          |
| channel         | string, nullable | Metadata (e.g. `database`, future `mail`) |
| data            | JSON             | Payload (`title_ar`, `title_en`, …)       |
| read_at         | timestamp, null  | Null = unread                             |
| created_at      | timestamp        |                                           |
| updated_at      | timestamp        |                                           |

Indexes: (`notifiable_type`, `notifiable_id`), (`notifiable_type`, `notifiable_id`, `read_at`).

## `notification_preferences`

| Column        | Type          | Notes             |
| ------------- | ------------- | ----------------- |
| id            | bigIncrements |                   |
| user_id       | FK users      | cascade on delete |
| type          | string(64)    | Registry key      |
| email_enabled | boolean       | default true      |
| sms_enabled   | boolean       | default false     |
| push_enabled  | boolean       | default false     |
| created_at    | timestamp     |                   |
| updated_at    | timestamp     |                   |

Unique: (`user_id`, `type`).

## Registry values

`general`, `orders`, `projects`, `approvals`
