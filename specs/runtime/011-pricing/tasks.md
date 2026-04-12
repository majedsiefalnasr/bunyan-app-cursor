# Tasks — Pricing

- [ ] T001 [P] [US1] Add migrations `backend/database/migrations/*_create_price_tiers_table.php` and `*_create_price_histories_table.php` with `down()`
- [ ] T002 [P] [US1] Add Eloquent models `backend/app/Models/PriceTier.php`, `backend/app/Models/PriceHistory.php` + relations on `Product`, `ProductVariant`, `User`
- [ ] T003 [US1] Add `backend/app/Repositories/PriceTierRepository.php` and `backend/app/Repositories/PriceHistoryRepository.php`
- [ ] T004 [US3] Add `backend/app/Services/PricingService.php` (list, sync with overlap validation, calculate)
- [ ] T005 [US2] Add Form Requests `backend/app/Http/Requests/Api/V1/SyncProductPricingRequest.php`, `backend/app/Http/Requests/Api/V1/CalculatePriceRequest.php`
- [ ] T006 [US1] Add API Resources `backend/app/Http/Resources/Api/V1/PriceTierResource.php`
- [ ] T007 [US1] Add `backend/app/Http/Controllers/Api/V1/ProductPricingController.php` and `backend/app/Http/Controllers/Api/V1/PricingCalculationController.php`; register routes in `backend/routes/api.php`
- [ ] T008 [US4] Extend `backend/app/Services/ProductService.php` to write `price_histories` when `price` changes (inject repository)
- [ ] T009 [US1] Add `backend/tests/Feature/Api/V1/PricingTest.php` covering GET/PUT/POST + RBAC + overlap validation
- [ ] T010 [P] [US5] Add `frontend/composables/useSarPriceFormat.ts`
- [ ] T011 [US5] Add `frontend/pages/admin/products/[id]/pricing.vue` (admin-only tier editor)
- [ ] T012 [US5] Update `frontend/pages/products/[id].vue` to load and display pricing tiers with SAR formatting
