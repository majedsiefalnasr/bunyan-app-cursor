# Technical Plan — Cost Estimator (STAGE_17)

## Architecture

- **Stack:** Laravel 11 API + Nuxt 3 frontend; Sanctum; existing `ProjectPolicy` for tenancy.
- **Layers:** `EstimateController`, `ProjectEstimateController`, `EstimateItemController`, `EstimateCompareController`, `Admin\BoqTemplateController` → `EstimateService` → `EstimateRepository`, `EstimateItemRepository`, `BoqTemplateRepository` → Eloquent models.
- **Policies:** `EstimatePolicy` delegates project visibility to `ProjectPolicy::view`; mutation matrix per clarifications.

## Database

Single migration `2026_04_12_210000_create_estimates_boq_tables.php`:

- `estimates` — FK `project_id`, `created_by`, `approved_by`; decimal totals; `status` string; indexes.
- `estimate_items` — FK `estimate_id` cascade, nullable `product_id`; category string; decimals; `sort_order`.
- `boq_templates` — JSON `items_json`; FK `created_by`.

## API Surface

| Method          | Path                                    | Roles                                  |
| --------------- | --------------------------------------- | -------------------------------------- |
| GET             | `/projects/{project}/estimates`         | + field_engineer                       |
| POST            | `/projects/{project}/estimates`         | no field_engineer                      |
| GET             | `/projects/{project}/estimates/compare` | + field_engineer                       |
| GET             | `/estimates/{estimate}`                 | + field_engineer                       |
| PUT             | `/estimates/{estimate}`                 | no field_engineer                      |
| POST            | `/estimates/{estimate}/calculate`       | no field_engineer                      |
| GET             | `/estimates/{estimate}/export`          | + field_engineer                       |
| POST/PUT/DELETE | `/estimates/{estimate}/items`…          | no field_engineer                      |
| POST            | `/estimates/{estimate}/approve`         | customer, supervising_architect, admin |
| POST            | `/estimates/{estimate}/reject`          | customer, supervising_architect, admin |
| CRUD            | `/admin/boq-templates`                  | admin                                  |

## Frontend

- `pages/projects/[id]/estimates/index.vue` — list, link to detail, compare two selected (simple flow).
- `pages/projects/[id]/estimates/[estimateId].vue` — summary card, line items table, recalculate, export button, status badge.
- `pages/projects/[id].vue` — add `UButton` / link to estimates.

## Testing

- `tests/Feature/Api/V1/EstimateApiTest.php` — RBAC, totals, compare validation, CSV download, admin templates.
- Factories for `Estimate`, `EstimateItem`, `BoqTemplate`.

## Validation Gate

Run `composer run lint`, `composer run test`, `cd frontend && npm run lint && npm run typecheck && npm run test`, `php artisan migrate --pretend`.
