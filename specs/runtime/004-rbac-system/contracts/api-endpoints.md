# API Contracts — RBAC System

## Admin Endpoints

### GET /api/v1/admin/roles

**Auth:** `auth:sanctum` + `role:admin`
**Description:** List all roles with user counts

**Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "customer",
      "description": "End user requesting construction services",
      "label": "العميل",
      "users_count": 42,
      "permissions_count": 6
    }
  ],
  "message": null,
  "errors": []
}
```

### GET /api/v1/admin/roles/{role}/permissions

**Auth:** `auth:sanctum` + `role:admin`
**Description:** List permissions for a specific role
**Parameters:** `role` — role ID

**Response (200):**

```json
{
  "success": true,
  "data": {
    "role": {
      "id": 1,
      "name": "customer",
      "label": "العميل"
    },
    "permissions": [
      { "id": 1, "name": "project.view", "description": "View projects" },
      { "id": 2, "name": "project.create", "description": "Create projects" }
    ]
  },
  "message": null,
  "errors": []
}
```

### GET /api/v1/admin/users

**Auth:** `auth:sanctum` + `role:admin`
**Description:** List users with optional role filter (paginated)
**Query Parameters:**

- `role` (optional) — filter by role name
- `per_page` (optional, default 15)
- `page` (optional, default 1)

**Response (200):**

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "أحمد محمد",
        "email": "ahmed@example.com",
        "role": "customer",
        "role_label": "العميل",
        "phone": "+966501234567",
        "active": true,
        "email_verified_at": "2026-04-10T12:00:00+00:00",
        "created_at": "2026-04-10T12:00:00+00:00"
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 15,
      "total": 42,
      "last_page": 3
    }
  },
  "message": null,
  "errors": []
}
```

### POST /api/v1/admin/users/{user}/role

**Auth:** `auth:sanctum` + `role:admin`
**Description:** Assign role to user
**Parameters:** `user` — user ID
**Request Body:**

```json
{
  "role": "contractor"
}
```

**Validation:**

- `role` — required, must be valid `UserRole` enum value

**Response (200):**

```json
{
  "success": true,
  "data": {
    "id": 5,
    "name": "محمد علي",
    "email": "mohammed@example.com",
    "role": "contractor",
    "role_label": "المقاول",
    "permissions": [
      "project.view",
      "project.update",
      "phase.view",
      "phase.create",
      "phase.update",
      "task.view",
      "task.create",
      "task.update",
      "report.view",
      "report.create",
      "transaction.view"
    ]
  },
  "message": "تم تعيين الدور بنجاح",
  "errors": []
}
```

**Error (422 — Last Admin):**

```json
{
  "success": false,
  "data": null,
  "message": null,
  "errors": [],
  "error": {
    "code": "RBAC_LAST_ADMIN",
    "message": "لا يمكن تغيير دور آخر مدير في النظام",
    "details": null
  }
}
```

### DELETE /api/v1/admin/users/{user}/role

**Auth:** `auth:sanctum` + `role:admin`
**Description:** Reset user to customer role (default)
**Parameters:** `user` — user ID

**Response (200):**

```json
{
  "success": true,
  "data": {
    "id": 5,
    "name": "محمد علي",
    "email": "mohammed@example.com",
    "role": "customer",
    "role_label": "العميل"
  },
  "message": "تم إعادة تعيين الدور إلى عميل",
  "errors": []
}
```

## Updated Existing Endpoints

### GET /api/v1/auth/profile — Updated Response

Adds `permissions` array to user profile response:

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "أحمد",
    "email": "ahmed@example.com",
    "role": "customer",
    "permissions": [
      "project.view",
      "project.create",
      "order.view",
      "order.create",
      "transaction.view",
      "report.view"
    ],
    "phone": "+966501234567",
    "active": true,
    "email_verified_at": "2026-04-10T12:00:00+00:00",
    "created_at": "2026-04-10T12:00:00+00:00",
    "updated_at": "2026-04-10T12:00:00+00:00"
  },
  "message": null,
  "errors": []
}
```

## Error Responses

### 403 — Role Denied

```json
{
  "success": false,
  "data": null,
  "message": null,
  "errors": [],
  "error": {
    "code": "RBAC_ROLE_DENIED",
    "message": "ليس لديك الصلاحية للوصول لهذا المورد",
    "details": null
  }
}
```

### 403 — Permission Denied

```json
{
  "success": false,
  "data": null,
  "message": null,
  "errors": [],
  "error": {
    "code": "RBAC_PERMISSION_DENIED",
    "message": "ليس لديك الصلاحية لتنفيذ هذا الإجراء",
    "details": null
  }
}
```
