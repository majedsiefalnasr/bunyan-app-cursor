# Technical Plan — Quotations (STAGE_18)

## Architecture

- **Stack:** Laravel 11 API + Nuxt 3 frontend; Sanctum; role middleware already in `backend/routes/api.php`.
- **Layers:** Controllers → Services → Repositories → Eloquent Models.
- **Controllers (new):**
  - `App\Http\Controllers\Api\V1\RfqController` (index/store/show/send/compare)
  - `App\Http\Controllers\Api\V1\RfqQuotationController` (index/store/accept)
- **Services (new):**
  - `App\Services\RfqService` (create, send, status transitions, supplier targeting)
  - `App\Services\QuotationService` (submit, revise rules, accept award flow, comparison builder)
- **Repositories (new):**
  - `App\Repositories\RfqRepository`
  - `App\Repositories\RfqItemRepository`
  - `App\Repositories\QuotationRepository`
  - `App\Repositories\QuotationItemRepository`
- **Policies (new):**
  - `App\Policies\RfqPolicy`
  - `App\Policies\QuotationPolicy`

### Layering Constraints (Non-Negotiable)

- **Controllers**: thin HTTP layer only (no business logic, no Eloquent).
- **Services**: business logic orchestration; **must not** import/use Eloquent models or query builder. Services **may** wrap multi-repository operations in `DB::transaction(...)` to guarantee atomicity.
- **Repositories**: the **only** layer allowed to execute Eloquent queries.

## Database

Create a single forward-only migration:

- `create_rfqs_quotations_tables`
  - `rfqs`
  - `rfq_items`
  - `quotations`
  - `quotation_items`

Add foreign keys and indexes on all FK columns to meet performance checklist.

## Data Model (Summary)

See `data-model.md` for detailed schema tables and relationships.

## API Surface

All endpoints under `/api/v1/` and protected by `auth:sanctum`.

| Method | Path                                        | Roles                                        |
| ------ | ------------------------------------------- | -------------------------------------------- |
| GET    | `/rfqs`                                     | customer, contractor, admin                  |
| POST   | `/rfqs`                                     | customer                                     |
| GET    | `/rfqs/{rfq}`                               | customer(owner), contractor(eligible), admin |
| POST   | `/rfqs/{rfq}/send`                          | customer(owner)                              |
| GET    | `/rfqs/{rfq}/quotations`                    | customer(owner), contractor(own), admin      |
| POST   | `/rfqs/{rfq}/quotations`                    | contractor                                   |
| PUT    | `/rfqs/{rfq}/quotations/{quotation}/accept` | customer(owner)                              |
| GET    | `/rfqs/{rfq}/compare`                       | customer(owner), admin                       |

### Filters

- `GET /rfqs?page=1&per_page=15&status=DRAFT|SENT|QUOTING|EVALUATION|AWARDED|CLOSED&sort=-created_at`

## Workflow Rules (Server-Side)

- `send` allowed only from `DRAFT` and only by owner.
- `submit/revise` allowed only while RFQ is in `QUOTING` and before `response_deadline`.
- `accept` allowed only from `EVALUATION` (or `QUOTING` if decision made early), sets RFQ → `AWARDED`.
- `close` is planned as a service method; endpoint can be added later if needed (out of the stage’s published endpoints).

## Frontend

Add new pages (Arabic-first, RTL, Nuxt UI components):

- Customer:
  - `frontend/pages/rfqs/index.vue` — list/filter
  - `frontend/pages/rfqs/new.vue` — create draft
  - `frontend/pages/rfqs/[id]/index.vue` — details + quotations list
  - `frontend/pages/rfqs/[id]/compare.vue` — comparison
- Supplier (Contractor):
  - `frontend/pages/contractor/rfqs/index.vue` — inbox/list eligible RFQs
  - `frontend/pages/contractor/rfqs/[id].vue` — submit/revise quotation

Shared:

- `frontend/components/rfq/RfqStatusBadge.vue`
- `frontend/components/rfq/QuotationComparisonTable.vue`

## Observability

Log key state transitions using structured context:

- `rfq.created`, `rfq.sent`, `quotation.submitted`, `quotation.revised`, `rfq.awarded`

## Testing

Backend:

- `backend/tests/Feature/Api/V1/RfqApiTest.php` — RBAC matrix for list/show/send/compare
- `backend/tests/Feature/Api/V1/QuotationApiTest.php` — RBAC for submit/list/accept + deadline rules
- Factories: `RfqFactory`, `RfqItemFactory`, `QuotationFactory`, `QuotationItemFactory`

Frontend (Vitest):

- Minimal component tests for comparison table rendering and RTL classes.

## Validation Gate

Run:

- `composer run lint && composer run test`
- `cd frontend && npm run lint && npm run typecheck && npm run test`
- `cd backend && php artisan migrate --pretend`
