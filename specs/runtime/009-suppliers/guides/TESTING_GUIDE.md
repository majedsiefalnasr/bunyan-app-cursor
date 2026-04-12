# Testing Guide — Suppliers (STAGE_09)

## Preconditions

- Backend `.env` with working `DB_*` (e.g. local MySQL or CI service).
- Frontend `NUXT_PUBLIC_API_BASE_URL` set to Laravel API root including `/api` (e.g. `http://127.0.0.1:8000/api`).

## Automated

```bash
cd backend && php artisan migrate --no-interaction && php artisan test --filter=SupplierProfileControllerTest
cd ../frontend && npm run lint
```

## Manual — API (example values)

1. Register/login as **contractor**; obtain Sanctum token from `POST /api/v1/auth/login`.
2. `POST /api/v1/suppliers` with header `Authorization: Bearer <token>` and JSON body:
   `{ "company_name_ar": "شركة اختبار", "city": "الرياض" }` → expect `201`, `verification_status: pending`.
3. Login as **admin**; `GET /api/v1/admin/suppliers` with bearer token → expect list includes the new row.
4. `PUT /api/v1/suppliers/1/verify` with body `{ "verification_status": "verified" }` → expect `200`, `verified_at` non-null.
5. Without auth: `GET /api/v1/suppliers` → expect only verified rows; `GET /api/v1/suppliers/1` → expect `200` for verified profile.

## Manual — UI

- Visit `http://localhost:3000/ar/suppliers` — verified suppliers appear as cards.
- Open a supplier card → `/ar/suppliers/{id}` shows product list (if products assigned with `supplier_id`).
- As contractor: `/ar/suppliers/register` submits profile (requires login + contractor role).
- As admin: `/ar/admin/suppliers` lists suppliers; Verify / Suspend buttons call verify API.

## Product linkage (admin)

- `POST /api/v1/admin/products` with optional `"supplier_id": <supplier_profile_id>` after migrations applied.
