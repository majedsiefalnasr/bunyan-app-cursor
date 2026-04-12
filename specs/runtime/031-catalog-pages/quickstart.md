# Quickstart — Catalog Pages

```bash
# Backend (from repo root)
cd backend && composer run dev   # or php artisan serve

# Frontend
cd frontend && npm run dev
```

1. Log in as any seeded user with Sanctum cookie.
2. Open `http://127.0.0.1:3000/ar/categories` — category grid.
3. Open `http://127.0.0.1:3000/ar/products` — filters + pagination.
4. Open `http://127.0.0.1:3000/ar/search?q=cement` — search results.

```bash
cd frontend && npm run test && npm run test:e2e -- --project=chromium tests/e2e/catalog.spec.ts
cd backend && php artisan test --filter=CategoryControllerTest
```
