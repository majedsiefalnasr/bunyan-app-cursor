# Suppliers — Quickstart

1. `cd backend && php artisan migrate`
2. `php artisan test --filter=SupplierProfileControllerTest`
3. Seed or create a contractor user, `POST /api/v1/suppliers` with bearer token.
4. As admin, `PUT /api/v1/suppliers/{id}/verify` with `{ "verification_status": "verified" }`.
5. Public `GET /api/v1/suppliers` should list the supplier.
6. Frontend (with `NUXT_PUBLIC_API_BASE_URL` pointing at Laravel `/api`): open locale-prefixed `/suppliers`.
