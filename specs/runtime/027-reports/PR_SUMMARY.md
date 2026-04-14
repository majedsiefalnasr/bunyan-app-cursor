# PR — STAGE_27 — Reports

## Summary

**Stage:** STAGE_27 — Reports  
**Phase:** 06_REPORTING_AND_ANALYTICS  
**Branch:** `spec/027-reports` → `develop`  
**Tasks:** 12 / 12 completed

## What Changed

### Backend

- Added Maatwebsite Excel and wired admin analytics routes under `/api/v1/admin/analytics/reports/*`.
- New `BusinessAnalyticsReportRepository`, `BusinessAnalyticsReportService`, thin `BusinessAnalyticsReportController`, Form Requests, PDF Blade view, and XLSX export class.
- Feature tests `BusinessAnalyticsReportControllerTest`.

### Frontend

- New admin page `/admin/reports` with filters, table preview, and export actions.
- Sidebar link and `analyticsReports` i18n keys (ar/en).

### Database

- No new migrations (read-only reporting over existing tables).

## Breaking Changes

- None. Field-engineer `Report` CRUD remains at `/api/v1/reports`; business analytics uses a separate path prefix.

## Testing

- [x] Targeted feature tests (`php artisan test --filter=BusinessAnalyticsReportControllerTest`)
- [x] Frontend tests (`npm run test`)
- [x] Lint passes (`composer run lint`, `npm run lint`)
- [x] Type check passes (`npm run typecheck`, backend `phpstan` via pre-commit)
- [ ] Full `php artisan test` (recommended in CI with MySQL)
- [ ] `php artisan migrate --pretend` when DB is available

## Checklist

- [x] RBAC middleware applied on all new routes (`role:admin`)
- [x] Form Request validation on new endpoints
- [x] Arabic/RTL support verified (PDF template RTL, UI strings via i18n)
- [x] Error contract followed on JSON responses
- [x] Aggregates scoped via Eloquent/Query Builder (no raw user input in SQL identifiers)
- [x] OpenAPI draft updated in runtime contracts folder (stage artifact)

## Related

- Stage File: `specs/phases/06_REPORTING_AND_ANALYTICS/STAGE_27_REPORTS.md`
- Testing Guide: `specs/runtime/027-reports/guides/TESTING_GUIDE.md`
