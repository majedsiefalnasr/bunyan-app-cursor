# Feature Specification — Catalog Pages (صفحات الكتالوج)

> **Stage:** Catalog Pages  
> **Phase:** 07_FRONTEND_APPLICATION  
> **Authority:** `specs/phases/07_FRONTEND_APPLICATION/STAGE_31_CATALOG_PAGES.md`

## Summary

Deliver Arabic-first, RTL catalog UX on Nuxt 3 using Nuxt UI: public-style browsing for authenticated users against existing Laravel v1 APIs (`/v1/categories`, `/v1/products`, `/v1/suppliers`). Includes category grid and detail, enhanced product listing with filters and pagination, product detail resolved by numeric id or SKU in the URL segment, supplier directory polish, and a dedicated search results page. No new public-unauthenticated catalog APIs — all catalog reads remain behind `auth:sanctum` except supplier directory endpoints that are already public.

## User stories

1. **US1 — Categories** — As an authenticated user, I can open `/categories` to browse active categories and `/categories/:slug` to see products scoped to that category.
2. **US2 — Product discovery** — As an authenticated user, I can filter products by category, price bounds, in-stock, and text search; paginate results; open detail at `/products/:slug` where `slug` is numeric id or product SKU.
3. **US3 — Search** — As an authenticated user, I can open `/search?q=` with the same filter and pagination behavior as the product catalog.
4. **US4 — Suppliers** — As any user, I can browse `/suppliers` and `/suppliers/:id` with improved Nuxt UI cards and ratings display consistent with `DESIGN.md`.
5. **US5 — Quality** — Vitest coverage for catalog query composable; Playwright smoke for auth gate and mocked happy-path listing.

## Functional requirements

### Routing (Nuxt)

- `pages/categories/index.vue` — layout default, middleware `auth`.
- `pages/categories/[slug].vue` — loads `GET /v1/categories/{slug}` (slug route key on backend) and product list with `category_id` filter.
- `pages/products/index.vue` — filters, debounced search, `UPagination`, reusable `ProductCard`.
- `pages/products/[slug].vue` — `GET /v1/products/{id|sku}`.
- `pages/search/index.vue` — reads `route.query.q`, syncs to product API `search` param.

### API alignment

- Categories: resolve `{category}` by **slug** in Laravel for consistent public URLs; admin mutations continue to use the same binding.
- Products: resolve `{product}` by **numeric id** or **SKU** for bookmarkable URLs without a `products.slug` column.
- Product list pagination: parse Laravel paginated resource shape under `data` when present.

### Components

- `components/catalog/ProductCard.vue` — `data-testid="product-card"`, shadow-as-border, link to detail using SKU when available else id.
- `components/catalog/ProductFilterSidebar.vue` — category multi-select (checkbox list), min/max price inputs, in-stock toggle, apply/reset.

### Composables

- `useProductCatalogQuery` — builds `URLSearchParams` from filter state; unit-tested for combinations and clearing.

### Non-goals

- New backend catalog endpoints, faceted search engine, or Elasticsearch.
- Guest (unauthenticated) product/category browsing.
- `UCommandPalette` production wiring (optional stub / follow-up).

## Acceptance criteria

- All new catalog pages use `useApi()` and respect RTL + i18n keys (ar/en).
- Category API tests updated for slug-based route parameters where applicable.
- Product `GET` accepts id or SKU; `ProductResource` exposes `sku` for the client.
- Playwright: at least unauthenticated redirect test + one mocked authenticated listing path.
- `npm run lint`, `npm run typecheck`, `npm run test` pass for touched frontend scope; backend PHPUnit passes for touched tests.

## Technical constraints

- Nuxt UI + Tailwind v4 + Pinia auth cookie; no direct `$fetch` bypassing `useApi`.
- Match existing error and success JSON contract on the client.

## Clarifications

### Session 2026-04-12

1. **Category URL segment** — Backend `Category` route binding uses `slug` (not numeric id) for `GET/PUT/DELETE /v1/categories/{category}` so public Nuxt routes and API stay aligned; admin UI must call the same slug-based paths.
2. **Product URL segment** — Detail segment accepts **numeric id** or **SKU** via `Product::resolveRouteBinding`; list links prefer `sku` when returned in `ProductResource`.
3. **Search scope** — `/search` reuses `GET /v1/products` with `search` and existing filters only (no new backend endpoint).
4. **Supplier routes** — Continue to use numeric `supplierProfile` id; no slug binding in this slice.
5. **Authentication** — Category and product catalog pages require Sanctum session (cookie); supplier directory remains callable without auth per existing API routes.
