# PR — Cost Estimator

## Summary

**Stage:** Cost Estimator  
**Phase:** 04_COMMERCIAL_LAYER  
**Branch:** `spec/017-cost-estimator` → `develop`  
**Tasks:** 18 / 18 completed

## What Changed

### Backend

- Added `estimates`, `estimate_items`, and `boq_templates` schema with enums, models, repositories, `EstimateService`, policies, Form Requests, API resources, and REST controllers.
- Registered routes: project estimates (list/create/compare), estimate CRUD actions (show/update/calculate/export/approve/reject), nested line items with scoped bindings, admin `boq-templates` resource.
- Added `EstimateApiTest` and model factories.

### Frontend

- Added `pages/projects/[id]/estimates/index.vue` and `[estimateId].vue`, link from project detail, and `projects.*` i18n keys in `locales/ar.json` and `en.json`.

### Database

- `2026_04_12_210000_create_estimates_boq_tables.php`

## Breaking Changes

- None.

## Testing

- [x] Feature tests (`composer run test -- --filter=EstimateApiTest` and full `composer run test`)
- [x] Frontend tests (`npm run test`)
- [x] Lint (`composer run lint`, `npm run lint`)
- [x] Type check (`composer run analyze`, `npm run typecheck`)
- [x] Migration pretend (run locally with valid DB — see `reports/LOCAL_CI_REPORT.md`)

## Checklist

- [x] RBAC middleware applied on all new routes
- [x] Form Request validation on new endpoints
- [x] Arabic/RTL support verified (i18n keys + CSV BOM)
- [x] Error contract followed
- [x] Eager loading on estimate show (`items` relation)

## Related

- Stage file: `specs/phases/04_COMMERCIAL_LAYER/STAGE_17_COST_ESTIMATOR.md`
- Runtime: `specs/runtime/017-cost-estimator/`
