# Suppliers — Technical Plan

## Data Model

- **supplier_profiles:** `user_id` (unique FK users), company names, registration fields, `verification_status`, `verified_at`, rating columns, timestamps.
- **products.supplier_id:** nullable FK → `supplier_profiles`, `nullOnDelete`, indexed.

## Backend

- Enum `SupplierVerificationStatus`.
- Model `SupplierProfile`; `User::supplierProfile()`; `Product::supplierProfile()`.
- `SupplierProfileRepository`: verified catalog, admin list, verification update.
- `SupplierProfileService`: visibility rules, create/update/verify.
- `SupplierProfileController`: public index/show/products; authenticated store/update; admin verify + `adminIndex`.
- Routes in `routes/api.php` per stage table + admin list under `v1/admin`.

## Frontend

- Public directory and detail using `useApi` paths `/v1/suppliers`…
- Contractor registration `/suppliers/register` with `role: contractor` middleware.
- Admin management `/admin/suppliers` calling `/v1/admin/suppliers` and verify endpoint.

## Validation / CI

- PHPUnit: `SupplierProfileControllerTest`.
- Pint on changed PHP; ESLint on frontend.
