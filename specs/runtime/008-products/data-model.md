# Data Model — Products

## `products` (alter)

| Column      | Type        | Notes                      |
| ----------- | ----------- | -------------------------- |
| category_id | FK nullable | → `categories.id`, indexed |

Legacy `category` string retained for backward compatibility.

## `product_variants`

| Column          | Type                           |
| --------------- | ------------------------------ |
| id              | bigint PK                      |
| product_id      | FK → products (cascade delete) |
| name            | string                         |
| sku             | string, unique                 |
| price_modifier  | decimal(15,2) default 0        |
| stock_quantity  | unsigned int default 0         |
| attributes_json | json nullable                  |
| is_active       | boolean default true           |
| timestamps      |                                |

## `product_media`

| Column     | Type                            |
| ---------- | ------------------------------- |
| id         | bigint PK                       |
| product_id | FK → products (cascade delete)  |
| type       | string (e.g. image, video, doc) |
| path       | string                          |
| sort_order | unsigned int default 0          |
| timestamps |                                 |

## Relationships

- `Product` belongsTo `Category` (optional)
- `Product` hasMany `ProductVariant`
- `Product` hasMany `ProductMedia`
- `Category` hasMany `Product`
