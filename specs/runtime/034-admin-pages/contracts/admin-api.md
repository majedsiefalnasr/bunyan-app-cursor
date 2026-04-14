# Contracts — Admin API (used by Admin Pages)

> **Generated (UTC):** 2026-04-14T14:24:38Z

This document captures the backend endpoints the Admin frontend will call in this stage.

## Authentication & RBAC

- All endpoints require `auth:sanctum`.
- Admin-only endpoints use `role:admin` middleware.
- The frontend `role` middleware is **UX-only**; backend remains authoritative.

## Endpoints

### Users (Admin)

- `GET /api/v1/admin/users`
  - **Query**: `page`, `per_page`, optional `role`
  - **Response**: `{ success, data: { data: User[], meta: { total, last_page } } }`
- `POST /api/v1/admin/users/{user}/role`
  - **Body**: `{ role: string }`
- `DELETE /api/v1/admin/users/{user}/role`

### Roles & Permissions (Admin)

- `GET /api/v1/admin/roles`
- `GET /api/v1/admin/roles/{role}/permissions`

### Suppliers (Admin)

- `GET /api/v1/admin/suppliers`
- `PUT /api/v1/suppliers/{supplierProfile}/verify`
  - **Body**: `{ verification_status: 'verified'|'suspended' }`

### Activity Log (Admin)

- `GET /api/v1/admin/activity-log`
  - **Query**: `page`, `per_page`

### Reports Center (Admin)

- `GET /api/v1/admin/analytics/reports/types`
- `GET /api/v1/admin/analytics/reports/{type}`
  - **Query**: optional `date_from`, `date_to`
- `GET /api/v1/admin/analytics/reports/{type}/export`
  - **Query**: `format=pdf|xlsx`, optional `date_from`, `date_to`

### Categories (Admin write)

- `GET /api/v1/categories`
- `GET /api/v1/categories/{category}`
- `POST /api/v1/categories` (admin-only)
- `PUT /api/v1/categories/{category}` (admin-only)
- `DELETE /api/v1/categories/{category}` (admin-only)
- `PUT /api/v1/categories/{category}/reorder` (admin-only)
