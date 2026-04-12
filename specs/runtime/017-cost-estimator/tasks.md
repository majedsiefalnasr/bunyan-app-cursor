# Tasks — Cost Estimator

- [ ] T001 [P] Add migration `backend/database/migrations/2026_04_12_210000_create_estimates_boq_tables.php` for `estimates`, `estimate_items`, `boq_templates`
- [ ] T002 [P] Add enums `backend/app/Enums/EstimateStatus.php` and `backend/app/Enums/EstimateItemCategory.php`
- [ ] T003 Add Eloquent models `backend/app/Models/Estimate.php`, `EstimateItem.php`, `BoqTemplate.php` and extend `backend/app/Models/Project.php` with `estimates()` relation
- [ ] T004 Add repositories `backend/app/Repositories/EstimateRepository.php`, `EstimateItemRepository.php`, `BoqTemplateRepository.php`
- [ ] T005 Add `backend/app/Services/EstimateService.php` (calculate, status transitions, compare, CSV)
- [ ] T006 Add `backend/app/Policies/EstimatePolicy.php` and `backend/app/Policies/BoqTemplatePolicy.php`
- [ ] T007 Add Form Requests under `backend/app/Http/Requests/Api/V1/` for estimates, items, compare, templates
- [ ] T008 Add API resources `backend/app/Http/Resources/Api/V1/EstimateResource.php`, `EstimateItemResource.php`, `BoqTemplateResource.php`
- [ ] T009 Add controllers `ProjectEstimateController`, `EstimateController`, `EstimateItemController`, `ProjectEstimateCompareController` under `backend/app/Http/Controllers/Api/V1/`
- [ ] T010 Add `backend/app/Http/Controllers/Api/V1/Admin/BoqTemplateController.php` for admin CRUD
- [ ] T011 Register routes in `backend/routes/api.php` with correct RBAC groups and throttling
- [ ] T012 Add factories `EstimateFactory`, `EstimateItemFactory`, `BoqTemplateFactory` and feature tests `backend/tests/Feature/Api/V1/EstimateApiTest.php`
- [ ] T013 [P] Add Nuxt pages `frontend/pages/projects/[id]/estimates/index.vue` and `frontend/pages/projects/[id]/estimates/[estimateId].vue`
- [ ] T014 Add i18n keys in `frontend/i18n/locales/ar.json` and `en.json` for estimates UI
- [ ] T015 Add estimates entry link in `frontend/pages/projects/[id].vue`
- [ ] T016 Mark spec checklists complete in `specs/runtime/017-cost-estimator/checklists/requirements.md` after verification
- [ ] T017 Run validation gate: `composer run lint`, `composer run test`, `php artisan migrate --pretend`, frontend `npm run lint`, `npm run typecheck`, `npm run test`
- [ ] T018 Write `specs/runtime/017-cost-estimator/audits/VALIDATION_REPORT.md` from gate outputs
