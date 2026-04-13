# Technical Plan — STAGE_27 Analytics Reports

## Architecture

- **Controllers:** `App\Http\Controllers\Api\V1\Admin\BusinessAnalyticsReportController` (thin)
- **Requests:** `App\Http\Requests\Api\V1\Admin\BusinessAnalyticsReportRequest`, `BusinessAnalyticsExportRequest`
- **Services:** `App\Services\Analytics\BusinessAnalyticsReportService`
- **Repositories:** `App\Repositories\Analytics\BusinessAnalyticsReportRepository`
- **Exports:** `App\Exports\BusinessAnalyticsExport` (Maatwebsite), PDF view `resources/views/reports/analytics-table.blade.php`
- **Routes:** Inside `Route::prefix('admin')->middleware('role:admin')` group:
  - `GET analytics/reports/types`
  - `GET analytics/reports/{type}` (where type allow-list)
  - `GET analytics/reports/{type}/export` + `throttle:30,1`

Full paths: `/api/v1/admin/analytics/reports/...`

## Data Model

No new tables for MVP. Repository reads existing:

- `orders` — sales_summary, orders_summary
- `products` / inventory tables as used by `InventoryController`
- `projects`, `phases`, `tasks` — project_status
- `supplier_profiles`, `orders` — supplier_performance aggregates
- `invoices` / `transactions` where present — financial_summary (stub sections if incomplete)

## Migrations

None required for MVP (read-only aggregates).

## Dependencies

- `composer require maatwebsite/excel` (backend)
- Existing `barryvdh/laravel-dompdf`

## Frontend

- `frontend/pages/admin/reports/index.vue` — type picker, filters (`UForm` fields), `UTable` preview, export buttons
- Composable: extend or add `useBusinessAnalyticsReports` calling API client
- i18n keys under `admin.reports.*`

## Error Handling & Logging

- Validation errors via Form Request
- `Log::info` with `action` = `analytics.report.generated` / `analytics.report.exported`

## Testing

- Feature tests: admin 200, non-admin 403, invalid type 404/422, export format validation
- `php artisan migrate --pretend` in CI gate (no new migrations expected)

## Quickstart

See `quickstart.md`.
