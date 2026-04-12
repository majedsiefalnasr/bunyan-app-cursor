# Catalog Read API (v1) — Contract Notes

All authenticated unless noted.

| Method | Path                              | Roles  | Query highlights                                                                  |
| ------ | --------------------------------- | ------ | --------------------------------------------------------------------------------- |
| GET    | `/api/v1/categories`              | Auth   | `include_inactive` (admin)                                                        |
| GET    | `/api/v1/categories/{slug}`       | Auth   | —                                                                                 |
| GET    | `/api/v1/products`                | Auth   | `page`, `per_page`, `category_id`, `min_price`, `max_price`, `search`, `in_stock` |
| GET    | `/api/v1/products/{id\|sku}`      | Auth   | —                                                                                 |
| GET    | `/api/v1/suppliers`               | Public | throttled                                                                         |
| GET    | `/api/v1/suppliers/{id}`          | Public | —                                                                                 |
| GET    | `/api/v1/suppliers/{id}/products` | Public | —                                                                                 |

Success envelope: `{ success, data, message, errors }`.
