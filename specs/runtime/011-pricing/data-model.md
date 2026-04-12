# Data Model — Pricing

## `price_tiers`

| Column             | Type                  | Notes                                    |
| ------------------ | --------------------- | ---------------------------------------- |
| id                 | bigint PK             |                                          |
| product_id         | bigint FK             | → `products.id` (cascade delete)         |
| product_variant_id | bigint nullable       | → `product_variants.id` (cascade delete) |
| min_quantity       | unsigned int          | ≥ 1                                      |
| max_quantity       | unsigned int nullable | null = unbounded upper bound             |
| unit_price         | decimal(12,2)         | SAR per unit in this band                |
| created_at         | datetime              |                                          |
| updated_at         | datetime              |                                          |

**Indexes:** (`product_id`), (`product_id`, `product_variant_id`).

## `price_histories`

| Column     | Type          | Notes                            |
| ---------- | ------------- | -------------------------------- |
| id         | bigint PK     |                                  |
| product_id | bigint FK     | → `products.id` (cascade delete) |
| old_price  | decimal(12,2) | Snapshot before change           |
| new_price  | decimal(12,2) | Snapshot after change            |
| changed_by | bigint FK     | → `users.id` (null on delete)    |
| changed_at | datetime      |                                  |

**Index:** (`product_id`, `changed_at`).

## Relationships

- `Product::hasMany(PriceTier::class)`
- `Product::hasMany(PriceHistory::class)`
- `ProductVariant::hasMany(PriceTier::class)` scoped by `product_variant_id`
