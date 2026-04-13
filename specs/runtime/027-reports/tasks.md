# Tasks — STAGE_27 Analytics Reports

- [ ] T001 Add `maatwebsite/excel` to `backend/composer.json` / lockfile
- [ ] T002 [P] Create `BusinessAnalyticsReportRepository` in `backend/app/Repositories/Analytics/BusinessAnalyticsReportRepository.php`
- [ ] T003 Create `BusinessAnalyticsReportService` in `backend/app/Services/Analytics/BusinessAnalyticsReportService.php`
- [ ] T004 Create Form Requests `BusinessAnalyticsReportRequest`, `BusinessAnalyticsExportRequest` under `backend/app/Http/Requests/Api/V1/Admin/`
- [ ] T005 Create `BusinessAnalyticsReportController` in `backend/app/Http/Controllers/Api/V1/Admin/BusinessAnalyticsReportController.php`
- [ ] T006 Register admin routes in `backend/routes/api.php` with throttle on export
- [ ] T007 Add Blade view `backend/resources/views/reports/analytics-table.blade.php` for PDF
- [ ] T008 Add `BusinessAnalyticsExport` Excel export class under `backend/app/Exports/`
- [ ] T009 [P] Feature tests `backend/tests/Feature/Admin/BusinessAnalyticsReportControllerTest.php`
- [ ] T010 Add admin reports page `frontend/pages/admin/reports/index.vue`
- [ ] T011 [P] Add i18n keys in `frontend/locales/ar.json` and `frontend/locales/en.json`
- [ ] T012 Wire navigation link from existing admin shell if applicable (`frontend/layouts` or admin index)
