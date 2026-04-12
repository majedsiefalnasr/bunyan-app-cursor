# Data Model — Categories

## Table `categories`

| Column     | Type            | Notes                           |
| ---------- | --------------- | ------------------------------- |
| id         | bigint PK       |                                 |
| parent_id  | bigint nullable | FK → categories.id, null = root |
| name_ar    | string          | utf8mb4                         |
| name_en    | string          | utf8mb4                         |
| slug       | string unique   |                                 |
| icon       | string nullable | optional icon key/url           |
| sort_order | unsigned int    | default 0, sibling ordering     |
| is_active  | boolean         | default true                    |
| created_at | timestamp       |                                 |
| updated_at | timestamp       |                                 |
| deleted_at | timestamp null  | soft delete                     |

### Indexes

- `parent_id`
- `slug` (unique)
- (`parent_id`, `sort_order`)

## Eloquent

- `parent(): BelongsTo`
- `children(): HasMany` ordered by `sort_order`

## Resource JSON (excerpt)

```json
{
  "id": 1,
  "parent_id": null,
  "name_ar": "مواد بناء",
  "name_en": "Building materials",
  "slug": "building-materials",
  "icon": null,
  "sort_order": 0,
  "is_active": true,
  "children": []
}
```
