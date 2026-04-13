# Quickstart — Dashboard

1. Checkout `spec/026-dashboard` and copy `backend/.env` with cache driver of choice.
2. Optional: set `DASHBOARD_CACHE_TTL=60` (seconds).
3. Run `cd backend && php artisan serve` and `cd frontend && npm run dev`.
4. Log in as each role; open `/dashboard` and verify KPI cards load without console errors.
5. `curl -H "Authorization: Bearer TOKEN" http://localhost/api/v1/dashboard` — expect `"success":true`.
