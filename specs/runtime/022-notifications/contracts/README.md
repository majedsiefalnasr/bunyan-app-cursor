# Notifications — API Contracts

Canonical routes live in `routes/api.php`. All endpoints require `Authorization: Bearer <token>`.

| Method | Path                               | Description          |
| ------ | ---------------------------------- | -------------------- |
| GET    | /api/v1/notifications              | Paginated feed       |
| PUT    | /api/v1/notifications/{id}/read    | Mark one read (UUID) |
| PUT    | /api/v1/notifications/read-all     | Mark all read        |
| GET    | /api/v1/notifications/unread-count | Badge count          |
| GET    | /api/v1/notification-preferences   | List merged prefs    |
| PUT    | /api/v1/notification-preferences   | Upsert preferences   |

Response envelope: `{ "success", "data", "message", "errors" }`.
