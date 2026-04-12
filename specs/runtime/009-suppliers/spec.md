# Suppliers — Runtime Specification

**Stage:** Suppliers (STAGE_09)  
**Phase:** 02_CATALOG_AND_INVENTORY

## Summary

Deliver supplier profiles linked to contractor users, admin verification workflow (pending → verified → suspended), public catalog of verified suppliers, product association via `supplier_id` on products, and Nuxt pages for directory, profile, contractor registration, and admin management.

## User Stories

1. **US1 — Public catalog:** As a visitor, I can browse verified suppliers and open a profile with listed products.
2. **US2 — Contractor onboarding:** As a contractor, I can create one supplier profile pending admin verification.
3. **US3 — Admin verification:** As an admin, I can list all supplier profiles and set verification to verified or suspended.
4. **US4 — Catalog association:** As an admin, I can assign a product to a supplier when creating or updating the product.

## Acceptance Criteria

- `GET /api/v1/suppliers` returns only **verified** suppliers for anonymous users; paginated; optional `city`, `search`.
- `GET /api/v1/suppliers/{id}` returns 404 for unauthenticated users when profile is not verified; owner and admin may view pending.
- `POST /api/v1/suppliers` requires Sanctum auth and **contractor** role; one profile per user.
- `PUT /api/v1/suppliers/{id}` allows profile owner (contractor) or admin.
- `PUT /api/v1/suppliers/{id}/verify` is **admin-only**; body `verification_status`: `verified` | `suspended`.
- `GET /api/v1/suppliers/{id}/products` lists active products for that supplier when the profile is viewable.
- `GET /api/v1/admin/suppliers` is **admin-only**; lists all profiles with filters.
- Products accept optional `supplier_id` on admin create/update; validated with `exists:supplier_profiles,id`.
- Frontend: `/suppliers`, `/suppliers/:id`, `/suppliers/register` (contractor), `/admin/suppliers` (admin).

## Clarifications

### Session 2026-04-12

- **Supplier role vs contractor:** Supplier identity is modeled as `supplier_profiles` attached to a **contractor** user (no new enum role value).
- **Public API:** Read-only supplier routes are outside `auth:sanctum` with throttle; mutating routes remain authenticated with RBAC middleware.
- **Ratings:** `rating_avg` / `total_ratings` are stored columns; aggregation from orders/reviews is deferred to downstream stages.

## Out of Scope (Deferred)

- Supplier-initiated product CRUD (contractor dashboard for SKUs).
- Payment or payout flows for suppliers.
- Full-text Arabic search tuning beyond `LIKE` filters.
