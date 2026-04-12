# API Contract — Estimates (v1)

Base: `/api/v1` — all routes require `auth:sanctum`.

## List & create

- `GET /projects/{project}/estimates` — query: `page`, `per_page`
- `POST /projects/{project}/estimates` — body: `title` (required), `description`, `markup_percentage`

## Compare

- `GET /projects/{project}/estimates/compare?ids=1,2,3` — 2–5 ids

## Estimate detail

- `GET /estimates/{estimate}` — includes `items`
- `PUT /estimates/{estimate}` — `title`, `description`, `markup_percentage`, `status` (validated transitions)
- `POST /estimates/{estimate}/calculate` — no body
- `GET /estimates/{estimate}/export` — CSV stream
- `POST /estimates/{estimate}/approve` — no body
- `POST /estimates/{estimate}/reject` — no body

## Line items

- `POST /estimates/{estimate}/items` — `category`, `quantity`, `unit`, `unit_price`, `description_ar`, `description_en`, `product_id`, `sort_order`
- `PUT /estimates/{estimate}/items/{estimateItem}` — partial update
- `DELETE /estimates/{estimate}/items/{estimateItem}`

## BOQ templates (admin)

- `GET/POST /admin/boq-templates`
- `GET/PUT/DELETE /admin/boq-templates/{boqTemplate}`

Success envelope: `{ success, data, message, errors }`.
