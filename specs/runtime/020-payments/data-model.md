# Data Model — Payments

## payments

| Column                | Type        | Notes                |
| --------------------- | ----------- | -------------------- |
| id                    | bigint PK   |                      |
| payable_type          | string      | `App\Models\Order`   |
| payable_id            | bigint      | FK logical via morph |
| user_id               | bigint FK   | payer (`users`)      |
| amount                | decimal     | SAR                  |
| currency              | char(3)     | default SAR          |
| method                | string      | enum cast            |
| status                | string      | enum cast            |
| gateway_reference     | string null | sandbox id           |
| paid_at               | datetime    | nullable             |
| created_at/updated_at | timestamps  |                      |

## payment_attempts

| Column           | Type      | Notes     |
| ---------------- | --------- | --------- |
| id               | bigint PK |           |
| payment_id       | bigint FK | cascade   |
| type             | string    | enum cast |
| amount           | decimal   |           |
| status           | string    | enum cast |
| gateway_id       | string    | nullable  |
| gateway_response | json      | nullable  |
| timestamps       |           |           |

## Relationships

- `Order` `morphMany` `Payment` as `payments`
- `Payment` `morphTo` payable; `belongsTo` user; `hasMany` attempts
