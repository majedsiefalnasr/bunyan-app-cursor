# Suppliers — API Contracts

See `plan.md` and Laravel routes `routes/api.php` for canonical paths.

| Method | Path                            | Auth     | Roles                                |
| ------ | ------------------------------- | -------- | ------------------------------------ |
| GET    | /api/v1/suppliers               | No       | Public (verified only)               |
| GET    | /api/v1/suppliers/{id}          | Optional | Verified public; pending owner/admin |
| GET    | /api/v1/suppliers/{id}/products | Optional | Same visibility as show              |
| POST   | /api/v1/suppliers               | Sanctum  | contractor                           |
| PUT    | /api/v1/suppliers/{id}          | Sanctum  | contractor (owner) or admin          |
| PUT    | /api/v1/suppliers/{id}/verify   | Sanctum  | admin                                |
| GET    | /api/v1/admin/suppliers         | Sanctum  | admin                                |

Response envelope: `{ "success", "data", "message", "errors" }` (existing platform contract).
