# Feature Specification — Products (المنتجات)

> **Stage:** Products  
> **Phase:** 02_CATALOG_AND_INVENTORY  
> **Authority:** `specs/phases/02_CATALOG_AND_INVENTORY/STAGE_08_PRODUCTS.md`

## Summary

Deliver a catalog-ready product domain: REST list/detail with filters, admin CRUD, optional hierarchical category linkage (`category_id`), supplier linkage, product variants, and product media metadata. Arabic-first UX on Nuxt listing and detail pages; API messages remain Arabic per existing `BaseController` usage.

## User stories

1. **US1 — Browse catalog** — As any authenticated user, I can list and open products with filters (category, supplier, price range, text search, in-stock).
2. **US2 — Admin manage products** — As an admin, I can create, update, and delete products via `/api/v1/admin/products`.
3. **US3 — Variants** — As an admin, I can attach sellable variants (SKU segment, stock, optional JSON attributes) to a product.
4. **US4 — Media metadata** — As an admin, I can register media rows (type + path + sort order) for gallery ordering; binary upload may reuse the global media upload endpoint separately.
5. **US5 — Frontend catalog** — As a user, I can open `/products` and `/products/{id}` with RTL layout and Nuxt UI.

## Functional requirements

### API

- **List:** `GET /api/v1/products` — paginated; query: `category`, `category_id`, `supplier_id`, `min_price`, `max_price`, `search`, `in_stock`, `per_page`, `page`.
- **Detail:** `GET /api/v1/products/{id}` — returns core fields plus optional nested `category`, `variants`, `media` when loaded.
- **Admin CRUD:** `POST|PUT|DELETE /api/v1/admin/products` — unchanged path prefix (RBAC `role:admin`); policies enforced; Form Requests validated.
- **Variants:** `POST /api/v1/admin/products/{product}/variants` — admin only.
- **Media rows:** `POST /api/v1/admin/products/{product}/media` — admin only; stores catalog metadata (not a replacement for `POST /api/v1/media/upload`).

### Data

- `products` gains nullable `category_id` → `categories.id` (legacy string `category` retained for backward compatibility during transition).
- New tables: `product_variants`, `product_media` per stage schema (soft deletes not required on child rows unless specified — use soft deletes on `products` only; child rows hard-delete on product delete or cascade).

### Layering

- `ProductController` remains HTTP-only; delegates to `ProductService`.
- `ProductService` owns create/update/SKU rules, variant/media orchestration.
- `ProductRepository` owns query composition and pagination (no N+1 on list when relations requested).

### Non-goals (explicit)

- Dedicated `supplier` user role (contractors hold `supplier_profiles`; supplier-owned product CRUD is a later governance change).
- Full bilingual `name_ar` / `name_en` columns on `products` in this slice (reserved for a later i18n migration; API continues to use `name` / `description`).

## Acceptance criteria

- All product routes remain behind `auth:sanctum`; mutations remain admin-only with policies.
- Filters do not widen SQL unintentionally (search `OR` groups scoped).
- Feature tests cover list, show, admin CRUD, variant attach, media attach, and unauthorized paths.
- Frontend `/products` and `/products/[id]` render with auth middleware and consume the v1 API.

## Technical constraints

- Laravel 11, Sanctum, service + repository pattern, Form Requests, API Resources, standard success/error JSON contract.
- Nuxt 3 + Nuxt UI + Tailwind v4 + RTL per `DESIGN.md`.
- Forward-only migrations with `down()` implemented.
