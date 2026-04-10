# API Contract — Bunyan Platform

**Version:** 1.0  
**Status:** PLANNING  
**Date:** 2026-04-10  
**Base URL:** `http://localhost:8000/api/v1` (development)

---

## Overview

All API responses follow a standardized **JSON contract** with fields: `success`, `data`, `message`, and `errors`. All API requests use **Bearer token authentication** (Laravel Sanctum) for protected endpoints.

---

## Response Contract

### Success Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Sample Data"
  },
  "message": "Operation completed successfully",
  "errors": {}
}
```

### Error Response (Validation)

```json
{
  "success": false,
  "data": null,
  "message": "Validation failed",
  "errors": {
    "email": ["Email is required", "Email must be valid"],
    "password": ["Password must be at least 8 characters"]
  }
}
```

### Error Response (Authorization)

```json
{
  "success": false,
  "data": null,
  "message": "Unauthorized",
  "errors": {}
}
```

---

## HTTP Status Codes

| Code | Meaning | Scenario |
|------|---------|----------|
| 200 | OK | Successful GET, PATCH |
| 201 | Created | Successful POST (resource created) |
| 400 | Bad Request | Validation failed |
| 401 | Unauthorized | Missing/invalid token |
| 403 | Forbidden | Authenticated but no permission (policy fails) |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed (same as 400) |
| 500 | Internal Server Error | Server error |

---

## Authentication Endpoints

### POST /auth/register

**Description:** Register a new user  
**Auth:** Public (no token required)

**Request Body:**
```json
{
  "name": "احمد محمد",
  "email": "ahmad@bunyan.local",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "phone": "+966501234567",
  "role": "customer"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "احمد محمد",
      "email": "ahmad@bunyan.local",
      "phone": "+966501234567",
      "role": "customer",
      "created_at": "2026-04-10T12:00:00Z"
    },
    "access_token": "1|abcdef123456...",
    "token_type": "Bearer"
  },
  "message": "User registered successfully"
}
```

**Response (422 Validation Failed):**
```json
{
  "success": false,
  "data": null,
  "message": "Validation failed",
  "errors": {
    "email": ["Email already exists"],
    "password": ["Passwords do not match"]
  }
}
```

---

### POST /auth/login

**Description:** Login with email & password  
**Auth:** Public

**Request Body:**
```json
{
  "email": "ahmad@bunyan.local",
  "password": "SecurePassword123!"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "احمد محمد",
      "email": "ahmad@bunyan.local",
      "role": "customer"
    },
    "access_token": "1|abcdef123456...",
    "token_type": "Bearer"
  },
  "message": "Login successful"
}
```

**Response (401 Unauthorized):**
```json
{
  "success": false,
  "data": null,
  "message": "Invalid credentials",
  "errors": {}
}
```

---

### POST /auth/logout

**Description:** Revoke authentication token  
**Auth:** Required (Bearer token)

**Response (200 OK):**
```json
{
  "success": true,
  "data": null,
  "message": "Logout successful"
}
```

---

## Project Endpoints

### GET /projects

**Description:** List all projects (filtered by user role)  
**Auth:** Required  
**Query Params:** `page=1`, `status=pending`, `search=query`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "مشروع البناء الحديث",
      "description": "مشروع تشييد مبنى سكني حديث",
      "budget": 500000.00,
      "status": "in_progress",
      "customer_id": 1,
      "contractor_id": 2,
      "supervising_architect_id": 3,
      "start_date": "2026-04-10",
      "end_date": "2026-08-10",
      "created_at": "2026-04-10T12:00:00Z",
      "updated_at": "2026-04-10T12:00:00Z"
    }
  ],
  "message": "Projects retrieved successfully"
}
```

---

### POST /projects

**Description:** Create a new project  
**Auth:** Required (Customer only)  
**Policy:** ProjectPolicy@create

**Request Body:**
```json
{
  "title": "مشروع البناء الحديث",
  "description": "مشروع تشييد مبنى سكني",
  "budget": 500000,
  "contractor_id": 2,
  "supervising_architect_id": 3,
  "start_date": "2026-04-10",
  "end_date": "2026-08-10"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "مشروع البناء الحديث",
    "budget": 500000.00,
    "status": "pending",
    "customer_id": 1,
    "workflow_config_id": 1,
    "created_at": "2026-04-10T12:00:00Z"
  },
  "message": "Project created successfully"
}
```

---

### GET /projects/{id}

**Description:** Get project details with all relationships  
**Auth:** Required  
**Policy:** ProjectPolicy@view

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "مشروع البناء الحديث",
    "description": "...",
    "budget": 500000.00,
    "status": "in_progress",
    "customer": {
      "id": 1,
      "name": "احمد محمد",
      "email": "ahmad@bunyan.local",
      "role": "customer"
    },
    "contractor": {
      "id": 2,
      "name": "شركة البناء",
      "email": "contractor@bunyan.local",
      "role": "contractor"
    },
    "phases": [
      {
        "id": 1,
        "name": "مرحلة الأساس",
        "budget": 150000.00,
        "status": "complete",
        "tasks": [
          {
            "id": 1,
            "name": "حفر الأساس",
            "status": "complete",
            "assigned_to": {
              "id": 4,
              "name": "محمد الميداني",
              "role": "field_engineer"
            },
            "reports": [
              {
                "id": 1,
                "text": "تم إكمال حفر الأساس بنجاح",
                "photos": ["https://..."],
                "created_at": "2026-04-10T12:00:00Z"
              }
            ]
          }
        ]
      }
    ],
    "transactions": [
      {
        "id": 1,
        "amount": 100000.00,
        "type": "payment",
        "status": "completed",
        "created_at": "2026-04-10T12:00:00Z"
      }
    ]
  },
  "message": "Project retrieved successfully"
}
```

---

### PATCH /projects/{id}

**Description:** Update project  
**Auth:** Required  
**Policy:** ProjectPolicy@update

**Request Body:**
```json
{
  "title": "مشروع البناء المتقدم",
  "budget": 600000,
  "contractor_id": 5,
  "status": "in_progress"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "مشروع البناء المتقدم",
    "budget": 600000.00,
    "status": "in_progress"
  },
  "message": "Project updated successfully"
}
```

---

### DELETE /projects/{id}

**Description:** Delete project (soft delete)  
**Auth:** Required  
**Policy:** ProjectPolicy@delete

**Response (200 OK):**
```json
{
  "success": true,
  "data": null,
  "message": "Project deleted successfully"
}
```

---

## Phase Endpoints

### POST /api/v1/projects/{project_id}/phases

**Description:** Create phase for project  
**Auth:** Required  
**Policy:** PhasePolicy@create

**Request Body:**
```json
{
  "name": "مرحلة الأساس",
  "description": "حفر وتشييد الأساسات",
  "budget": 150000,
  "start_date": "2026-04-10",
  "end_date": "2026-05-10",
  "order": 1
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "project_id": 1,
    "name": "مرحلة الأساس",
    "budget": 150000.00,
    "status": "pending",
    "order": 1,
    "created_at": "2026-04-10T12:00:00Z"
  },
  "message": "Phase created successfully"
}
```

---

## Task Endpoints

### POST /api/v1/phases/{phase_id}/tasks

**Description:** Create task in phase  
**Auth:** Required  
**Policy:** TaskPolicy@create

**Request Body:**
```json
{
  "name": "حفر الأساس",
  "description": "حفر الأرضية بعمق 2 متر",
  "budget": 50000,
  "priority": "high",
  "assigned_to": 4,
  "start_date": "2026-04-10",
  "end_date": "2026-04-15"
}
```

---

### POST /api/v1/tasks/{id}/complete

**Description:** Mark task as complete  
**Auth:** Required  
**Policy:** TaskPolicy@complete

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "complete",
    "updated_at": "2026-04-15T15:30:00Z"
  },
  "message": "Task completed successfully"
}
```

---

## Report Endpoints

### POST /api/v1/tasks/{task_id}/reports

**Description:** Submit field report with photos/videos  
**Auth:** Required  
**Policy:** ReportPolicy@create  
**Content-Type:** `multipart/form-data`

**Request Body:**
```
text=تم إكمال حفر الأساس بنجاح&photos=<file1.jpg>&photos=<file2.jpg>&videos=<video1.mp4>
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "task_id": 1,
    "text": "تم إكمال حفر الأساس بنجاح",
    "photos": [
      "https://storage.bunyan.local/reports/photo-1.jpg",
      "https://storage.bunyan.local/reports/photo-2.jpg"
    ],
    "videos": [
      "https://storage.bunyan.local/reports/video-1.mp4"
    ],
    "created_at": "2026-04-15T15:30:00Z"
  },
  "message": "Report created successfully"
}
```

---

## Payment & Transaction Endpoints

### POST /api/v1/projects/{project_id}/pay

**Description:** Create payment transaction  
**Auth:** Required (Customer only)  
**Policy:** TransactionPolicy@create

**Request Body:**
```json
{
  "amount": 100000,
  "payment_method": "bank_transfer",
  "reference": "TXN-20260410-001"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "project_id": 1,
    "amount": 100000.00,
    "type": "payment",
    "status": "pending",
    "payment_method": "bank_transfer",
    "reference": "TXN-20260410-001",
    "created_at": "2026-04-10T12:00:00Z"
  },
  "message": "Payment transaction created successfully"
}
```

---

## Product & Order Endpoints

### GET /products

**Description:** List building materials  
**Auth:** Required  
**Query Params:** `category=cement`, `search=keyword`, `page=1`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "الاسمنت البورتلاندي",
      "price": 150.00,
      "sku": "CEMENT-001",
      "stock_quantity": 1000,
      "category": {
        "id": 1,
        "name": "الإسمنت"
      },
      "images": ["https://..."]
    }
  ],
  "message": "Products retrieved successfully"
}
```

---

### POST /orders

**Description:** Create purchase order  
**Auth:** Required (Customer only)

**Request Body:**
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 100
    },
    {
      "product_id": 2,
      "quantity": 50
    }
  ],
  "shipping_address": "الرياض، حي النخيل، الشارع 5",
  "notes": "توصيل مباشرة للموقع"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_number": "ORD-2026-0001",
    "customer_id": 1,
    "total_amount": 12500.00,
    "status": "pending",
    "items": [
      {
        "product_id": 1,
        "product_name": "الاسمنت البورتلاندي",
        "quantity": 100,
        "unit_price": 150.00
      }
    ],
    "created_at": "2026-04-10T12:00:00Z"
  },
  "message": "Order created successfully"
}
```

---

## Error Codes Reference

| Error Code | HTTP | Meaning | Solution |
|-----------|------|---------|----------|
| ERR_AUTH_INVALID_CREDENTIALS | 401 | Email/password incorrect | Verify credentials |
| ERR_AUTH_TOKEN_EXPIRED | 401 | Token expired (>7 days) | Re-login to get new token |
| ERR_AUTH_MISSING_TOKEN | 401 | Authorization header missing | Add: `Authorization: Bearer <token>` |
| ERR_FORBIDDEN_POLICY | 403 | User lacks permission | Check role/ownership |
| ERR_VALIDATION_FAILED | 422 | Input validation failed | Check errors field for details |
| ERR_RESOURCE_NOT_FOUND | 404 | Resource doesn't exist | Verify ID exists |
| ERR_CONFLICT_BUDGET | 422 | Phase/task budget exceeds project | Adjust budgets |
| ERR_WORKFLOW_INVALID_TRANSITION | 422 | Status change not allowed | Check workflow rules |

---

## Rate Limiting

**Applied to:** Auth endpoints (login, register, refresh)  
**Limit:** 5 requests per minute per IP  
**Response (429 Too Many Requests):**
```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "errors": {}
}
```

---

## Pagination

Endpoints that return lists support pagination:

**Query Params:**
- `page=1` (default: 1)
- `per_page=15` (default: 15, max: 100)

**Response Structure:**
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4,
    "from": 1,
    "to": 15
  }
}
```

---

**Status:** READY FOR REVIEW  
**Last Updated:** 2026-04-10
