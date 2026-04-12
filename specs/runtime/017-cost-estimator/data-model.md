# Data Model — Cost Estimator

## estimates

| Column            | Type               | Notes            |
| ----------------- | ------------------ | ---------------- |
| id                | bigint PK          |                  |
| project_id        | FK → projects      | cascade delete   |
| title             | string(255)        | required         |
| description       | text nullable      |                  |
| status            | string             | `EstimateStatus` |
| total_materials   | decimal(14,2)      | default 0        |
| total_labor       | decimal(14,2)      | default 0        |
| total_overhead    | decimal(14,2)      | default 0        |
| grand_total       | decimal(14,2)      | default 0        |
| markup_percentage | decimal(5,2)       | default 0        |
| approved_by       | FK users nullable  |                  |
| approved_at       | timestamp nullable |                  |
| created_by        | FK users           |                  |
| timestamps        |                    |                  |

## estimate_items

| Column         | Type                   | Notes                       |
| -------------- | ---------------------- | --------------------------- |
| id             | bigint PK              |                             |
| estimate_id    | FK → estimates         | cascade                     |
| product_id     | FK → products nullable |                             |
| description_ar | string nullable        |                             |
| description_en | string nullable        |                             |
| category       | string                 | material / labor / overhead |
| quantity       | decimal(14,4)          |                             |
| unit           | string(32)             |                             |
| unit_price     | decimal(14,2)          |                             |
| total_price    | decimal(14,2)          |                             |
| sort_order     | unsigned int           | default 0                   |
| timestamps     |                        |                             |

## boq_templates

| Column       | Type            | Notes         |
| ------------ | --------------- | ------------- |
| id           | bigint PK       |               |
| name_ar      | string          |               |
| name_en      | string          |               |
| project_type | string nullable |               |
| items_json   | json            | BOQ structure |
| created_by   | FK users        |               |
| timestamps   |                 |               |

## Relationships

- `Project hasMany Estimate`
- `Estimate hasMany EstimateItem`
- `Estimate belongsTo User (creator, approver)`
- `EstimateItem belongsTo Product` (optional)
- `BoqTemplate belongsTo User`
