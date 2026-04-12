# Data Model — Inventory

## inventories

| Column                | Type        | Notes                |
| --------------------- | ----------- | -------------------- |
| id                    | bigint PK   |                      |
| product_id            | FK products | required             |
| variant_id            | FK nullable | product_variants.id  |
| warehouse_location    | string(191) | default `default`    |
| quantity              | unsignedInt | on-hand              |
| reserved_quantity     | unsignedInt | default 0            |
| min_quantity          | unsignedInt | default 0, threshold |
| created_at/updated_at | timestamps  |                      |

**Unique:** (`product_id`, `variant_id`, `warehouse_location`)

## stock_movements

| Column         | Type                    | Notes                         |
| -------------- | ----------------------- | ----------------------------- |
| id             | bigint PK               |                               |
| product_id     | FK                      |                               |
| variant_id     | FK nullable             |                               |
| type           | string                  | in,out,adjust,reserve,release |
| quantity       | integer                 | signed delta                  |
| reference_type | string nullable         |                               |
| reference_id   | unsignedBigInt nullable |                               |
| notes          | text nullable           |                               |
| created_by     | FK users                |                               |
| created_at     | timestamp               | no updated_at required        |

**Indexes:** (`product_id`, `created_at`), (`created_by`)
