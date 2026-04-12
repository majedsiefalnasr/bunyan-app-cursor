# STAGE_08 — Products

> **Phase:** 02_CATALOG_AND_INVENTORY
> **Status:** DRAFT
> **Scope:** Product/material catalog, variants, attributes, media
> **Risk Level:** MEDIUM

## Stage Status

Status: DRAFT
Step: clarify
Risk Level: LOW
Last Updated: 2026-04-12T13:05:00Z

Scope Defined: Catalog API, admin CRUD, category FK, variants, media metadata, Nuxt catalog pages; admin route prefix; metadata-only nested media

Deferred Scope: Supplier-owned CRUD without admin role; full bilingual product name columns

Architecture Governance Compliance: Clarifications resolved — planning authorized

Notes: All specification ambiguities resolved. Ready for technical planning.

## Objective

Implement the product catalog for construction materials and services. Support product variants, custom attributes, and media attachments.

## Scope

### Backend

- Product Eloquent model with relationships (category, supplier, variants, media)
- Product variant model (size, color, specification variants)
- Product attribute model (dynamic key-value attributes)
- Product repository with filtering, search, pagination
- Product service (CRUD, status management, bulk operations)
- Product API resource with nested includes
- Product Form Request validation
- Product search with filters (category, price range, supplier, availability)

### Frontend

- Product listing page with grid/list view toggle
- Product detail page with image gallery
- Product creation/edit form (Admin, Supplier)
- Product search and filter sidebar
- Product card component

### API Endpoints

| Method | Route                          | Description                           |
| ------ | ------------------------------ | ------------------------------------- |
| GET    | /api/v1/products               | List products (paginated, filterable) |
| POST   | /api/v1/products               | Create product                        |
| GET    | /api/v1/products/{id}          | Get product details                   |
| PUT    | /api/v1/products/{id}          | Update product                        |
| DELETE | /api/v1/products/{id}          | Delete product                        |
| POST   | /api/v1/products/{id}/variants | Add variant                           |
| POST   | /api/v1/products/{id}/media    | Upload media                          |

### Database Schema

| Table              | Columns                                                                                                                                                            |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| products           | id, category_id, supplier_id, name_ar, name_en, slug, description_ar, description_en, sku, base_price, unit, status, is_active, created_at, updated_at, deleted_at |
| product_variants   | id, product_id, name, sku, price_modifier, stock_quantity, attributes_json, is_active                                                                              |
| product_attributes | id, product_id, key, value_ar, value_en                                                                                                                            |
| product_media      | id, product_id, type, path, sort_order                                                                                                                             |

## Dependencies

- **Upstream:** STAGE_07_CATEGORIES
- **Downstream:** STAGE_17_COST_ESTIMATOR, STAGE_19_ORDERS
