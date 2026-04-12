# Technical Plan — Categories

## Architecture

- **Layers:** `CategoryController` (thin) → `CategoryService` → `CategoryRepository` → `Category` model.
- **Auth:** Laravel Sanctum + `CheckRole` for admin mutations; `CategoryPolicy` for `authorize()` on controller actions.
- **Validation:** `StoreCategoryRequest`, `UpdateCategoryRequest`, `ReorderCategoryRequest`.
- **Responses:** `CategoryResource` nested `children`; standard Bunyan JSON envelope via `BaseController`.

## Database

- New migration `create_categories_table` with columns per `data-model.md`.
- Forward-only; `down()` drops table.

## API surface

| Method | Path                                    | Auth            |
| ------ | --------------------------------------- | --------------- |
| GET    | `/api/v1/categories`                    | Sanctum         |
| GET    | `/api/v1/categories/{category}`         | Sanctum         |
| POST   | `/api/v1/categories`                    | Sanctum + admin |
| PUT    | `/api/v1/categories/{category}`         | Sanctum + admin |
| DELETE | `/api/v1/categories/{category}`         | Sanctum + admin |
| PUT    | `/api/v1/categories/{category}/reorder` | Sanctum + admin |

## Frontend

- Page `frontend/pages/admin/categories.vue` with `CategoryTree`, modal create/edit.
- Components under `frontend/components/ecommerce/`: `CategoryTree.vue`, `CategoryBreadcrumb.vue`, `CategorySelect.vue`.
- API via `useApi().apiFetch` with existing token handling.

## Testing

- Feature: `CategoryControllerTest` — RBAC, tree shape, reorder, delete guard.
- Unit: extend `ApplicationPoliciesTest` for `CategoryPolicy`.
- Factory: `CategoryFactory`.

## Seeder

- `CategorySeeder` registered in `DatabaseSeeder` before product-related flows.

## Risks

- **Cycle prevention:** service validates `parent_id` not self/descendant.
- **Delete:** reject when `children()->exists()`.
