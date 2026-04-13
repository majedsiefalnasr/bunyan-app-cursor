# Tasks — STAGE_27 Analytics Reports

- [x] T001 Add `maatwebsite/excel` to `backend/composer.json` / lockfile
- [x] T002 [P] Create `BusinessAnalyticsReportRepository` in `backend/app/Repositories/Analytics/BusinessAnalyticsReportRepository.php`
- [x] T003 Create `BusinessAnalyticsReportService` in `backend/app/Services/Analytics/BusinessAnalyticsReportService.php`
- [x] T004 Create Form Requests `BusinessAnalyticsReportRequest`, `BusinessAnalyticsExportRequest` under `backend/app/Http/Requests/Api/V1/Admin/`
- [x] T005 Create `BusinessAnalyticsReportController` in `backend/app/Http/Controllers/Api/V1/Admin/BusinessAnalyticsReportController.php`
- [x] T006 Register admin routes in `backend/routes/api.php` with throttle on export
- [x] T007 Add Blade view `backend/resources/views/reports/analytics-table.blade.php` for PDF
- [x] T008 Add `BusinessAnalyticsExport` Excel export class under `backend/app/Exports/`
- [x] T009 [P] Feature tests `backend/tests/Feature/Admin/BusinessAnalyticsReportControllerTest.php`
- [x] T010 Add admin reports page `frontend/pages/admin/reports/index.vue`
- [x] T011 [P] Add i18n keys in `frontend/locales/ar.json` and `frontend/locales/en.json`
- [x] T012 Wire navigation link from existing admin shell if applicable (`frontend/layouts` or admin index)
