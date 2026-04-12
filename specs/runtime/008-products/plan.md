# Technical Plan — Products

## Overview

Introduce `ProductService` as the business layer for catalog and admin flows, extend `ProductRepository::allActive()` for safe filter composition, add nullable `category_id` on `products`, and add `product_variants` + `product_media` with admin-only nested create endpoints. Nuxt gains authenticated `/products` and `/products/[id]`.

## Backend

| Layer      | Change                                                                                                           |
| ---------- | ---------------------------------------------------------------------------------------------------------------- |
| Migrations | `add_category_id_to_products_table`; `create_product_variants_table`; `create_product_media_table`               |
| Models     | `Product` relations; `ProductVariant`; `ProductMedia`; `Category::products()`                                    |
| Repository | Extend `ProductRepository::allActive` filters + scoped search                                                    |
| Service    | New `ProductService` (list, show payload, CRUD, variant, media)                                                  |
| HTTP       | Thin `ProductController`; new Form Requests for variant/media                                                    |
| Routes     | Under `auth:sanctum` + `admin` group: nested `POST products/{product}/variants`, `POST products/{product}/media` |
| Resources  | `ProductResource` exposes nested data when loaded                                                                |

## Frontend

| Page                       | Purpose                                                |
| -------------------------- | ------------------------------------------------------ |
| `pages/products/index.vue` | Grid of cards, search + category filter (query params) |
| `pages/products/[id].vue`  | Detail with price, stock, optional variants list       |

## Testing

- Extend `ProductControllerTest` (and/or add focused tests) for filters, variants, media, 403 paths.
- Keep `DomainRepositoriesTest` passing after repository signature change (backward compatible default args).

## Rollout

1. Run migrations locally / CI.
2. Smoke: list → detail → admin create with `category_id`.
3. Ship branch `spec/008-products`.
