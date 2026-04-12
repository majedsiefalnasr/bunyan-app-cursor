# PR — Products

## Summary

**Stage:** Products  
**Phase:** 02_CATALOG_AND_INVENTORY  
**Branch:** `spec/008-products` → `develop`  
**Tasks:** 12 / 12 completed

## What Changed

### Backend

- Added `ProductService` and refactored `ProductController` to delegate list/detail/CRUD plus variant and media metadata endpoints.
- Extended `ProductRepository::allActive()` with `category_id`, `supplier_id`, price range, scoped text search, and optional `in_stock`.
- New models `ProductVariant`, `ProductMedia`; `Product::catalogCategory()` relation; `Category::products()`.
- Migrations: nullable `products.category_id` FK; `product_variants`; `product_media`.
- New Form Requests for variant/media; API resources for nested payloads; admin routes `POST admin/products/{product}/variants` and `.../media`.

### Frontend

- New authenticated pages `pages/products/index.vue` and `pages/products/[id].vue` using `useApi()` and Nuxt UI.
- i18n keys under `catalog.*` in `locales/ar.json` and `locales/en.json`.

### Database

- `2026_04_12_160001_add_category_id_to_products_table.php`
- `2026_04_12_160002_create_product_variants_table.php`
- `2026_04_12_160003_create_product_media_table.php`

## Breaking Changes

- None intended. `ProductResource` adds `category_id`, `category_detail`, `variants`, and `media` keys; clients should tolerate extra fields.

## Testing

- [x] Unit + feature tests (`composer run test`)
- [x] Frontend tests (`npm run test`)
- [x] Lint (`composer run lint`, `npm run lint`)
- [x] Type check (`composer run analyze`, `npm run typecheck`)
- [ ] `php artisan migrate --pretend` against real DB (blocked in sandbox; see `reports/LOCAL_CI_REPORT.md`)

## Checklist

- [x] RBAC: nested routes under existing `role:admin` group
- [x] Form Request validation on new endpoints
- [x] Arabic/RTL: `catalog` strings in ar/en; layout uses existing design tokens
- [x] Error contract followed
- [x] List query uses eager `catalogCategory` to avoid N+1 on category slug/detail

## Related

- Stage File: `specs/phases/02_CATALOG_AND_INVENTORY/STAGE_08_PRODUCTS.md`
- Testing Guide: `specs/runtime/008-products/guides/TESTING_GUIDE.md`
