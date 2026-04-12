# API Contract — Pricing (v1)

## `GET /api/v1/products/{product}/pricing`

- **Auth:** Sanctum
- **Policy:** `view` on `product`
- **200 data:** `{ "tiers": [ { "id", "product_id", "product_variant_id", "min_quantity", "max_quantity", "unit_price" } ] }`

## `PUT /api/v1/admin/products/{product}/pricing`

- **Auth:** Sanctum + `role:admin`
- **Policy:** `update` on `product`
- **Body:** `{ "tiers": [ { "min_quantity": int, "max_quantity": int|null, "unit_price": string|number, "product_variant_id": int|null } ] }`
- **422:** overlapping bands or invalid variant ownership

## `POST /api/v1/pricing/calculate`

- **Auth:** Sanctum
- **Body:** `{ "product_id": int, "product_variant_id": int|null, "quantity": int }`
- **200 data:** `{ "unit_price": "…", "line_total": "…", "currency": "SAR" }`
- **422:** product inactive / not viewable / quantity out of range where applicable

All responses use the Bunyan standard envelope (`success`, `data`, `message`, `errors`).
