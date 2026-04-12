# Tasks — Categories

- [ ] T001 [P] [US1] Add `backend/database/migrations/2026_04_12_150000_create_categories_table.php` with adjacency columns and indexes.
- [ ] T002 [P] [US1] Add `backend/app/Models/Category.php` with parent/children relations and soft deletes.
- [ ] T003 [US1] Add `backend/database/factories/CategoryFactory.php` for tests.
- [ ] T004 [US1] Implement `backend/app/Repositories/CategoryRepository.php` for ordered flat fetch used by tree builder.
- [ ] T005 [US1] Implement `backend/app/Services/CategoryService.php` (tree, CRUD, reorder, cycle and delete guards).
- [ ] T006 [US2] Add Form Requests under `backend/app/Http/Requests/Api/V1/` for store, update, and reorder.
- [ ] T007 [US2] Add `backend/app/Policies/CategoryPolicy.php` and `backend/app/Http/Resources/Api/V1/CategoryResource.php`.
- [ ] T008 [US2] Add `backend/app/Http/Controllers/Api/V1/CategoryController.php` and register routes in `backend/routes/api.php`.
- [ ] T009 [US1] Add `backend/database/seeders/CategorySeeder.php` and register in `backend/database/seeders/DatabaseSeeder.php`.
- [ ] T010 [US3] Add `backend/tests/Feature/Api/V1/CategoryControllerTest.php` and extend `backend/tests/Unit/Policies/ApplicationPoliciesTest.php` for category policy matrix.
- [ ] T011 [P] [US3] Add `frontend/pages/admin/categories.vue` and link from `frontend/layouts/admin.vue`.
- [ ] T012 [US3] Add `frontend/components/ecommerce/CategoryTree.vue`, `CategoryBreadcrumb.vue`, and `CategorySelect.vue`.
