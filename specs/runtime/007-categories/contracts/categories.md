# API Contract — Categories (`/api/v1`)

## GET /categories

**Query**

- `include_inactive` (optional, `1`/`true`) — admin only; otherwise 403.

**Response `data`:** array of root `CategoryResource` objects with nested `children`.

## GET /categories/{id}

**Response `data`:** single `CategoryResource` (children loaded one level deep for detail; tree index remains canonical).

## POST /categories (admin)

**Body**

```json
{
  "parent_id": null,
  "name_ar": "كهرباء",
  "name_en": "Electrical",
  "slug": "electrical",
  "icon": null,
  "sort_order": 0,
  "is_active": true
}
```

## PUT /categories/{id} (admin)

Same fields; optional `parent_id` with cycle validation.

## DELETE /categories/{id} (admin)

Soft delete; **409** or **422** when children exist (implementation uses error contract).

## PUT /categories/{id}/reorder (admin)

```json
{ "sort_order": 2 }
```

`sort_order` is the new zero-based index among siblings (implementation normalizes orders).
