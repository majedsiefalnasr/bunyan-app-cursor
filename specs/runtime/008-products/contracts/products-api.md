# API Contract — Products (v1)

All responses use `{ success, data, message, errors }`.

## Read

- `GET /api/v1/products` — auth required; filters: `category`, `category_id`, `supplier_id`, `min_price`, `max_price`, `search`, `in_stock`, `per_page`, `page`.
- `GET /api/v1/products/{id}` — auth required; includes `category`, `variants`, `media` in payload.

## Admin write

Requires `role:admin` + policies.

- `POST /api/v1/admin/products`
- `PUT /api/v1/admin/products/{id}`
- `DELETE /api/v1/admin/products/{id}`
- `POST /api/v1/admin/products/{id}/variants` — body: `name`, `sku`, `price_modifier?`, `stock_quantity`, `attributes_json?`, `is_active?`
- `POST /api/v1/admin/products/{id}/media` — body: `type`, `path`, `sort_order?`
