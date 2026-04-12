# STAGE_09 — Suppliers

> **Phase:** 02_CATALOG_AND_INVENTORY
> **Status:** PRODUCTION READY
> **Scope:** Supplier profiles, verification, product association
> **Risk Level:** MEDIUM

## Stage Status

Status: PRODUCTION READY  
Step: stage_production_ready  
Risk Level: MEDIUM  
Closure Date: 2026-04-12

Scope Closed:

- Supplier profiles, verification API, public catalog, admin supplier list, product `supplier_id`, Nuxt supplier pages, feature tests, SpecKit runtime artifacts under `specs/runtime/009-suppliers/`.

Deferred Scope:

- Supplier product self-service UI; automated rating aggregation from downstream commerce stages.

Architecture Governance Compliance:

- ADR alignment: no conflicting ADR changes introduced.
- RBAC: `role` middleware on authenticated supplier routes; admin-only verify and admin index.
- Service/repository layering maintained for supplier domain.
- Error contract: existing `ApiResponse` envelope preserved.

Notes:

- Stage delivered on branch `spec/009-suppliers` via SpecKit Hard Mode (autopilot).

## Objective

Implement supplier management with profiles, verification workflow, and product association.

## Scope

### Backend

- Supplier profile model (extends User role = Contractor)
- Supplier verification workflow (pending → verified → suspended)
- Supplier service (CRUD, verification, rating aggregation)
- Supplier repository with search and filtering
- Supplier API resource
- Supplier Form Request validation

### Frontend

- Supplier directory page (public)
- Supplier profile page with products, ratings
- Supplier registration form
- Supplier dashboard (Contractor role)
- Supplier management page (Admin)

### API Endpoints

| Method | Route                           | Description             |
| ------ | ------------------------------- | ----------------------- |
| GET    | /api/v1/suppliers               | List suppliers          |
| GET    | /api/v1/suppliers/{id}          | Get supplier profile    |
| PUT    | /api/v1/suppliers/{id}          | Update supplier profile |
| PUT    | /api/v1/suppliers/{id}/verify   | Verify supplier (Admin) |
| GET    | /api/v1/suppliers/{id}/products | Get supplier products   |

### Database Schema

| Table             | Columns                                                                                                                                                                |
| ----------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| supplier_profiles | id, user_id, company_name_ar, company_name_en, commercial_reg, tax_number, city, district, address, phone, verification_status, verified_at, rating_avg, total_ratings |

## Dependencies

- **Upstream:** STAGE_04_RBAC_SYSTEM, STAGE_06_API_FOUNDATION
- **Downstream:** STAGE_08_PRODUCTS, STAGE_18_QUOTATIONS
