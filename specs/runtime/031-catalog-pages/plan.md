# Technical Plan — Catalog Pages

## Architecture

- **Frontend:** Nuxt 3 pages + composables + `components/catalog/*`; all data via `useApi()`.
- **Backend:** Minimal binding/resource tweaks only (`Category` slug key, `Product` id/SKU resolution, `sku` on `ProductResource`); no new controllers.
- **Tests:** Update `CategoryControllerTest` paths; extend `ProductControllerTest` for SKU show; Vitest + Playwright additions.

## Implementation phases

1. **Backend route models** — `Category::getRouteKeyName()`, `Product::resolveRouteBinding()`, `ProductResource` field.
2. **Composable** — `useProductCatalogQuery.ts` + unit tests.
3. **Shared UI** — `ProductCard`, `ProductFilterSidebar`.
4. **Pages** — categories index/detail, products index upgrade, `[slug]` detail, search index, supplier card polish.
5. **i18n** — New keys for categories, filters, search.
6. **E2E** — `catalog.spec.ts` smoke.

## Pagination contract

Client normalizes Laravel `Resource::collection($paginator)` JSON: outer `data` may be array **or** object with nested `data` + `meta`.

## Files (primary)

| Area     | Path                                                      |
| -------- | --------------------------------------------------------- |
| Backend  | `backend/app/Models/Category.php`, `Product.php`          |
| Backend  | `backend/app/Http/Resources/Api/V1/ProductResource.php`   |
| Backend  | `backend/tests/Feature/Api/V1/CategoryControllerTest.php` |
| Backend  | `backend/tests/Feature/ProductControllerTest.php`         |
| Frontend | `frontend/composables/useProductCatalogQuery.ts`          |
| Frontend | `frontend/components/catalog/ProductCard.vue`             |
| Frontend | `frontend/components/catalog/ProductFilterSidebar.vue`    |
| Frontend | `frontend/pages/categories/index.vue`, `[slug].vue`       |
| Frontend | `frontend/pages/products/index.vue`, `[slug].vue`         |
| Frontend | `frontend/pages/search/index.vue`                         |
| Frontend | `frontend/pages/suppliers/index.vue`, `[id].vue` (polish) |
| Frontend | `frontend/locales/ar.json`, `en.json`                     |
| Frontend | `frontend/tests/unit/useProductCatalogQuery.spec.ts`      |
| Frontend | `frontend/tests/e2e/catalog.spec.ts`                      |

## Guardians (Step 3.1A)

- **Architecture guardian:** PASS — no layer violations; thin UI.
- **API designer:** PASS — reuses v1 contracts; binding change documented.
