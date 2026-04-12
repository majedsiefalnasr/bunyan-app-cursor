# PR — Catalog Pages

## Summary

**Stage:** Catalog Pages  
**Phase:** 07_FRONTEND_APPLICATION  
**Branch:** `spec/031-catalog-pages` → `develop`  
**Tasks:** 20 / 20 completed

## What Changed

### Backend

- `Category` resolves implicit route binding by `slug` for `GET/PUT/DELETE /api/v1/categories/{category}`.
- `Product` resolves `{product}` by numeric **id** or **SKU** for `GET /api/v1/products/{product}`.
- `ProductResource` includes `sku` for client link construction.
- PHPUnit: category delete/reorder URLs use slugs; new SKU resolution test on products.

### Frontend

- New catalog composable `useProductCatalogQuery` (+ Vitest).
- Components `CatalogProductCard`, `ProductFilterSidebar`.
- Pages: `/categories`, `/categories/:slug`, upgraded `/products`, `/products/:slug` (was `[id]`), `/search`.
- `requiresAuth: true` on catalog pages so `auth` middleware redirects guests.
- Admin `categories` reorder uses slug in the API path.
- Navigation item `nav.categories`; i18n for categories, filters, search.
- Playwright: unauthenticated `/ar/products` redirects (in `middleware.spec.ts`).

### Database

- None (no migrations).

## Breaking Changes

- **API clients** that called `/api/v1/categories/{numericId}` must switch to **category slug** in the path (admin UI updated in-repo).

## Testing

- [x] Feature tests: `CategoryControllerTest`, `ProductControllerTest` (filtered)
- [x] Frontend: `npm run test`, `npm run lint`, `npm run typecheck`
- [x] Pint: `./vendor/bin/pint --test`
- [x] PHPStan: ran via pre-commit hook on staged PHP

## Checklist

- [x] RBAC: no new unauthenticated catalog APIs; UI gated with `requiresAuth`
- [x] Arabic/RTL strings via i18n keys
- [x] `DESIGN.md` card shadow patterns on new surfaces
