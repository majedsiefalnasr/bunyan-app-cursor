# Tasks — Products

- [ ] T001 [US1] Add migration `backend/database/migrations/2026_04_12_160001_add_category_id_to_products_table.php` with nullable FK to `categories`
- [ ] T002 [US3] Add migration `backend/database/migrations/2026_04_12_160002_create_product_variants_table.php`
- [ ] T003 [US4] Add migration `backend/database/migrations/2026_04_12_160003_create_product_media_table.php`
- [ ] T004 [US1] Add models `backend/app/Models/ProductVariant.php`, `backend/app/Models/ProductMedia.php` and extend `backend/app/Models/Product.php`, `backend/app/Models/Category.php` relationships
- [ ] T005 [US1] Extend `backend/app/Repositories/ProductRepository.php` filters (category_id, supplier_id, price range, scoped search)
- [ ] T006 [US1] [US2] Add `backend/app/Services/ProductService.php` and refactor `backend/app/Http/Controllers/Api/V1/ProductController.php` to delegate
- [ ] T007 [US3] [US4] Add Form Requests and admin routes for variants/media in `backend/routes/api.php`
- [ ] T008 [US1] [US2] Update `backend/app/Http/Resources/Api/V1/ProductResource.php` and product Form Requests for `category_id`
- [ ] T009 [US1] [US2] Extend `backend/tests/Feature/ProductControllerTest.php` (filters, variants, media, category_id create)
- [ ] T010 [US5] Add `frontend/pages/products/index.vue` and `frontend/pages/products/[id].vue` with auth + `useApi()`
- [ ] T011 [US5] Add i18n keys in `frontend/locales/ar.json` and `frontend/locales/en.json` for catalog strings
- [ ] T012 [US1] Update `backend/database/factories/ProductFactory.php` for `category_id` default linkage
