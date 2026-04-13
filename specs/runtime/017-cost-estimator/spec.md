# Specification — Cost Estimator (STAGE_17)

**Phase:** 04_COMMERCIAL_LAYER  
**Authority:** `specs/phases/04_COMMERCIAL_LAYER/STAGE_17_COST_ESTIMATOR.md`

## Summary

Deliver **project-scoped cost estimation** for Bunyan: `estimates` and `estimate_items` with optional `product_id` link to catalog, **BOQ templates** (`boq_templates`) for admin-managed reusable structures, a **calculation engine** (quantity × unit price per line, rolled up into material / labor / overhead subtotals, then markup on subtotal → `grand_total`), **estimate comparison** for multiple estimates on the same project, **CSV export** (Excel-friendly) for BOQ export in this stage, Sanctum REST under `/api/v1`, `EstimatePolicy` tied to `ProjectPolicy::view` for reads and stricter rules for mutations and approval, thin controllers, `EstimateService` + `EstimateRepository` / `EstimateItemRepository`, Form Request validation, Arabic-first validation messages, feature tests, and Nuxt project pages to list estimates, edit line items, show cost summary, compare estimates, and download CSV.

## User stories

1. **US1 — List estimates**  
   As a project participant, I can list estimates for a project I can view, with pagination.

2. **US2 — Create / update estimate**  
   As a contractor, customer, supervising architect, or admin, I can create an estimate (title, description, markup %) and update metadata while status is `draft`.

3. **US3 — Line items**  
   As an authorized editor, I can add, update, and delete line items (category material/labor/overhead, descriptions AR/EN, quantity, unit, unit_price, optional product_id, sort_order).

4. **US4 — Recalculate**  
   As an authorized editor, I can trigger recalculation so stored totals match line items.

5. **US5 — Approve / reject**  
   As a customer, supervising architect, or admin, I can set status to `approved` or `rejected` (with audit fields `approved_by`, `approved_at` on approve).

6. **US6 — Compare**  
   As a project participant, I can compare up to five estimates on the same project (totals and line counts).

7. **US7 — Export**  
   As a project participant, I can download a CSV export of an estimate’s BOQ.

8. **US8 — BOQ templates (admin)**  
   As an admin, I can CRUD BOQ templates (`name_ar`, `name_en`, `project_type`, `items_json`).

9. **US9 — Project UI**  
   As a user, I can open `/projects/{id}/estimates` and `/projects/{id}/estimates/{estimateId}` with RTL layout and i18n.

## Functional requirements

### Backend

- **Migrations (forward-only):**
  - `estimates`: `id`, `project_id` (FK projects), `title`, `description` (nullable text), `status` (string-backed enum: draft, submitted, approved, rejected), `total_materials`, `total_labor`, `total_overhead`, `grand_total` (decimals 2), `markup_percentage` (decimal 2, default 0), `approved_by` (nullable FK users), `approved_at` (nullable timestamp), `created_by` (FK users), `created_at`, `updated_at`, indexes on `project_id`, `status`.
  - `estimate_items`: `id`, `estimate_id` (FK cascade delete), `product_id` (nullable FK products), `description_ar`, `description_en` (nullable), `category` (material/labor/overhead), `quantity` (decimal), `unit` (string), `unit_price` (decimal 2), `total_price` (decimal 2), `sort_order` (unsigned int), timestamps, index `estimate_id`.
  - `boq_templates`: `id`, `name_ar`, `name_en`, `project_type` (string nullable), `items_json` (JSON), `created_by`, timestamps.
- **Enums:** `EstimateStatus`, `EstimateItemCategory`.
- **Layers:** `EstimateRepository`, `EstimateItemRepository`, `BoqTemplateRepository`; `EstimateService` owns calculations, status transitions, export serialization; controllers thin.
- **Authorization:** `EstimatePolicy` — `view`/`viewAny` require `ProjectPolicy::view` on parent project; `create`/`update`/`addItem`/`updateItem`/`deleteItem`/`recalculate` for `customer`, `contractor`, `supervising_architect`, `admin`; `approve`/`reject` for `customer`, `supervising_architect`, `admin` only; `export` same as `view`; `boqTemplate` admin-only for mutate, list for admin only in this stage.
- **Endpoints (Sanctum):**  
  Role group for project estimates (aligned with document reads): `role:customer,contractor,supervising_architect,field_engineer,admin` for **read** routes where policy still enforces project access. **Mutations** (create estimate, update estimate, items, calculate, approve/reject): `role:customer,contractor,supervising_architect,admin` (field engineer read-only on estimates).
  - `GET /api/v1/projects/{project}/estimates` — paginated list.
  - `POST /api/v1/projects/{project}/estimates` — create draft.
  - `GET /api/v1/projects/{project}/estimates/compare` — query `ids` (comma-separated, 2–5 numeric ids), same project validation.
  - `GET /api/v1/estimates/{estimate}` — detail with items.
  - `PUT /api/v1/estimates/{estimate}` — update metadata / status rules via service.
  - `POST /api/v1/estimates/{estimate}/calculate` — persist recalculated totals.
  - `GET /api/v1/estimates/{estimate}/export` — `Content-Disposition` CSV download.
  - `POST /api/v1/estimates/{estimate}/items` — add line item.
  - `PUT /api/v1/estimates/{estimate}/items/{estimateItem}` — update line item.
  - `DELETE /api/v1/estimates/{estimate}/items/{estimateItem}` — remove line item.
  - `POST /api/v1/estimates/{estimate}/approve` — set approved (body optional notes not required).
  - `POST /api/v1/estimates/{estimate}/reject` — set rejected.
  - **Admin:** `apiResource` `admin/boq-templates` (only `role:admin`) — standard CRUD JSON for templates.
- **Rate limiting:** reuse `throttle:60,1` on list/show; `throttle:30,1` on mutations comparable to documents.

### Frontend

- Pages: `pages/projects/[id]/estimates/index.vue`, `pages/projects/[id]/estimates/[estimateId].vue` with `auth` middleware, Nuxt UI tables/cards, `useApi`, i18n keys in `ar.json` / `en.json`.
- Link from `pages/projects/[id].vue` to estimates index.
- Comparison: modal or secondary route using compare API when user selects 2+ estimates on index page.

### Non-goals (this stage)

- Binary PDF generation (no new composer PDF dependencies); CSV export satisfies spreadsheet handoff.
- Applying BOQ template to auto-populate estimate lines (deferred; templates are admin CRUD + storage only).
- Excel XLSX with styling, labor rate tables from external APIs, multi-currency.

## Acceptance criteria

- All routes require Sanctum + role middleware; policies prevent cross-project access.
- Calculation: each line `total_price = round(quantity * unit_price, 2)`; category subtotals sum lines; `grand_total = round((total_materials + total_labor + total_overhead) * (1 + markup_percentage/100), 2)`.
- Feature tests: list forbidden cross-tenant, create estimate, add item, calculate, export CSV headers, compare validation, admin template CRUD, field engineer read list OK but POST estimate forbidden.
- `composer run lint` + `composer run test`; `npm run lint` + `npm run typecheck` + `npm run test` in `frontend/`.
- `php artisan migrate --pretend` succeeds.

## Dependencies

- **Upstream:** Products, pricing, projects (existing models and policies).
- **Downstream:** Quotations stage may consume approved estimates (out of scope here).

## Clarifications

### Session 2026-04-12

- **Field engineer access:** Read-only on estimates and exports (`GET` routes); no create/update/items/calculate/approve (aligned with project read group minus mutation roles).
- **Export format:** CSV with UTF-8 BOM for Excel Arabic compatibility; filename `estimate-{id}-boq.csv`.
- **Compare API:** `GET /projects/{project}/estimates/compare?ids=2,3,5` — minimum 2, maximum 5 ids; all must belong to the project or 422.
- **Approval roles:** `customer`, `supervising_architect`, and `admin` may approve or reject; `contractor` cannot approve own-only workflow (contractor can still submit by setting status to `submitted` via `PUT` when policy allows).
- **Submitted status:** Contractor or customer may move `draft` → `submitted` via `PUT` when they have update permission and estimate has at least one line item.
