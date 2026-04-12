# Testing Guide — Cost Estimator

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-12T21:00:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
# Configure DB_* then:
php artisan migrate
php artisan db:seed   # optional if your flow needs seed data

cd ../frontend && npm install
# Set NUXT_PUBLIC_API_BASE_URL=http://localhost:8000 in frontend/.env
```

## Running automated tests

```bash
cd backend && composer run test -- --filter=EstimateApiTest
cd backend && composer run test
cd frontend && npm run test
cd frontend && npm run lint && npm run typecheck
cd backend && composer run lint && composer run analyze
```

## Manual scenarios

### 1. List and create estimate

1. Log in as **customer** or **contractor** who owns or participates in project `1` (use real seeded IDs from your DB).
2. Open `http://localhost:3000/projects/1/estimates` (replace `1`).
3. Enter title `تجربة تقدير` and click **تقدير جديد** / **New estimate**.
4. Confirm the new row appears with status `draft`.

### 2. Line items and recalculate

1. Open the estimate detail URL: `http://localhost:3000/projects/1/estimates/{estimateId}`.
2. Use API (e.g. Postman) as the same user:  
   `POST /api/v1/estimates/{estimateId}/items` with JSON  
   `{"category":"material","quantity":2,"unit":"m2","unit_price":100,"description_ar":"بلاط"}`  
   (Sanctum `Authorization: Bearer {token}`).
3. `POST /api/v1/estimates/{estimateId}/calculate` — expect `grand_total` **220.00** with default markup `0` on a fresh estimate; if you set `markup_percentage: 10` on create, expect **220.00** from prior automated test (2×100 + 10%).

### 3. CSV export

1. `GET /api/v1/estimates/{estimateId}/export` with bearer token.
2. Confirm file starts with UTF-8 BOM bytes `EF BB BF` and contains header `description_ar`.

### 4. Field engineer read-only

1. Log in as **field_engineer** with access to the project (via report auth path).
2. `GET /api/v1/projects/{projectId}/estimates` → **200**.
3. `POST /api/v1/projects/{projectId}/estimates` with `{"title":"x"}` → **403**.

### 5. Admin BOQ templates

1. Log in as **admin**.
2. `POST /api/v1/admin/boq-templates` with body  
   `{"name_ar":"قالب","name_en":"Tpl","items_json":[{"line":1}]}` → **201**.
3. `GET /api/v1/admin/boq-templates` → **200** list.
4. Log in as **customer**; `GET /api/v1/admin/boq-templates` → **403**.

## Migration check

```bash
cd backend && php artisan migrate --pretend
```

Run only when `.env` has valid database credentials.
