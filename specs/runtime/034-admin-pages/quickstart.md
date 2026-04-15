# Quickstart — Admin Pages (Dev)

> **Generated (UTC):** 2026-04-14T14:24:38Z

## Frontend

```bash
cd frontend
npm install
npm run dev
```

## Backend (API)

```bash
cd backend
composer install
php artisan serve
```

## Smoke Checks

- Login as an Admin user.
- Visit:
  - `/admin/users`
  - `/admin/activity-log`
  - `/admin/suppliers`
  - `/admin/reports`
  - `/admin/analytics`
- Login as a non-admin and verify `/admin/*` redirects away with “غير مصرح”.
