# Quickstart — STAGE_27

```bash
cd backend && composer install && php artisan test --filter=BusinessAnalytics
cd frontend && npm ci && npm run test -- --runInBand --testPathPattern=admin/reports || true
```

Manual smoke (admin token):

```http
GET /api/v1/admin/analytics/reports/types
Authorization: Bearer <admin_sanctum_token>

GET /api/v1/admin/analytics/reports/sales_summary?date_from=2026-01-01&date_to=2026-12-31
GET /api/v1/admin/analytics/reports/sales_summary/export?format=pdf&date_from=2026-01-01&date_to=2026-12-31
```
