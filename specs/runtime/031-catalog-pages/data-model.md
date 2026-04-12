# Data Model Notes — Catalog Pages

No new tables in this slice.

## Existing entities (read)

| Entity              | Use in UI                                    |
| ------------------- | -------------------------------------------- |
| `categories`        | Tree from `GET /v1/categories`; slug in URLs |
| `products`          | Paginated list + detail with relations       |
| `supplier_profiles` | Directory + detail + nested products list    |

## API fields consumed

- **CategoryResource:** `id`, `name_ar`, `name_en`, `slug`, `icon`, `children`
- **ProductResource:** adds `sku` for link construction; existing `price`, `quantity`, `category_id`, nested media/tiers on detail
