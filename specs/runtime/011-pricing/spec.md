# Feature Specification — Pricing (التسعير)

> **Stage:** Pricing  
> **Phase:** 02_CATALOG_AND_INVENTORY  
> **Authority:** `specs/phases/02_CATALOG_AND_INVENTORY/STAGE_11_PRICING.md`

## Summary

Deliver quantity-based **price tiers** for catalog products (optional per **product variant**), a server-side **price calculator** for checkout and displays, **SAR** formatting helpers for Arabic-first UI, and **price history** rows when the product’s base `price` changes. Mutations remain **admin-only** (supplier-owned catalog pricing is deferred until supplier RBAC for catalog writes exists).

## User stories

1. **US1 — View tiers** — As an authenticated user, I can read tier bands for a product via `GET /api/v1/products/{product}/pricing` when I can view the product.
2. **US2 — Admin configure tiers** — As an admin, I can replace tier rows for a product (and optional variant scope) via `PUT /api/v1/admin/products/{product}/pricing`.
3. **US3 — Calculate price** — As an authenticated user, I can call `POST /api/v1/pricing/calculate` with `product_id`, optional `product_variant_id`, and `quantity` to receive `unit_price`, `line_total`, and `currency`.
4. **US4 — Price history** — As an admin, I can rely on the system recording `price_history` when a product’s base `price` field changes (audit trail).
5. **US5 — Frontend** — As an admin, I can manage tiers on `/admin/products/{id}/pricing`; as any user, I see SAR-formatted prices and a bulk-pricing table on the product detail page when tiers exist.

## Functional requirements

### API

- **Read tiers:** `GET /api/v1/products/{product}/pricing` — `auth:sanctum`; authorize with `ProductPolicy::view`; returns ordered tiers for product-level and per-variant scope.
- **Replace tiers:** `PUT /api/v1/admin/products/{product}/pricing` — `auth:sanctum` + `role:admin`; `ProductPolicy::update`; body: `tiers[]` with `min_quantity`, optional `max_quantity`, `unit_price`, optional `product_variant_id` (nullable = product-level band).
- **Calculate:** `POST /api/v1/pricing/calculate` — `auth:sanctum`; validated `product_id`, optional `product_variant_id`, `quantity` (min 1); resolves tier by quantity; applies variant `price_modifier` to base product price when no variant-specific tier exists (documented behavior).

### Tier rules

- Tiers are **non-overlapping** per scope key `(product_id, product_variant_id)` where `product_variant_id` null means product-level.
- `min_quantity` ≥ 1; `max_quantity` null means “no upper bound”; bands are half-open `[min, max]` except the highest band uses max null.
- `unit_price` is **absolute** SAR per unit for that band (not incremental).

### Data

- New tables: `price_tiers`, `price_histories` (forward-only migrations, `down()` implemented).
- `price_histories`: `product_id`, `old_price`, `new_price`, `changed_by` (user id), `changed_at`.

### Layering

- `PricingController` (or dedicated controllers) remain HTTP-only; delegate to `PricingService`.
- `PricingService` owns tier validation, overlap checks, sync logic, and calculation.
- `PriceTierRepository` / `PriceHistoryRepository` own persistence; Eloquent models hold relationships only.

### Non-goals (explicit)

- Supplier self-service tier editing (UI targets **admin** until supplier catalog RBAC is defined).
- Full **discount rules engine** — only extension points / comments in service; no coupon stack in this slice.
- Multi-currency beyond **SAR** display constants (no FX rates).

## Acceptance criteria

- All routes behind `auth:sanctum`; tier mutations admin-only with policy checks.
- Form Requests validate all inputs; responses follow the standard JSON contract.
- Feature tests: read pricing (authorized/unauthorized), admin replace tiers (happy path + overlap validation failure), calculate endpoint, base price change creates `price_histories` row.
- Nuxt: SAR formatting composable; product detail shows tier table; admin pricing page loads/saves tiers.

## Technical constraints

- Laravel 11, Sanctum, service + repository pattern, Form Requests, API Resources.
- Nuxt 3 + Nuxt UI + RTL per `DESIGN.md`.
- Structured logging on tier sync and calculation errors.

## Clarifications

### Session 2026-04-12

1. **Write path for tiers** — `PUT /api/v1/admin/products/{product}/pricing` (admin + `ProductPolicy::update`), not `PUT /api/v1/products/{id}/pricing`, to match existing catalog RBAC.
2. **Supplier UI** — Deferred; admin configures tiers on `/admin/products/{id}/pricing`.
3. **Variant pricing** — When `product_variant_id` is set on a tier row, that band applies to that variant; if no tier matches, fall back to `(product.price + variant.price_modifier) * quantity` for calculation.
4. **Price history** — Rows are written when `products.price` changes (via `ProductService` orchestration), not for every tier row edit (tier edits are auditable via application logs in this slice).
