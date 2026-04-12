# Testing Guide — Categories (STAGE_07)

## Preconditions

- Backend: `cd backend && cp .env.example .env` (if needed), `composer install`, configure DB, `php artisan migrate`, `php artisan db:seed`.
- Frontend: `cd frontend && npm install`, set `NUXT_PUBLIC_API_BASE_URL` to your Laravel API base (including `/api`).
- Admin user exists with `role = admin` and a valid Sanctum token.

## Automated checks (from repo root)

```bash
npm run lint
npm run typecheck
npm run analyze
npm run test
```

Backend-only:

```bash
cd backend && composer run lint && php artisan test
```

## API manual checks

Base URL example: `http://127.0.0.1:8000/api`.

1. **Guest denied**

```bash
curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/api/v1/categories
```

Expect `401`.

2. **Authenticated list (active only)**  
   Replace `$TOKEN` with a non-admin user token.

```bash
curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  http://127.0.0.1:8000/api/v1/categories | jq '.success, (.data|length)'
```

Expect `success: true` and only active roots after seeding.

3. **Admin `include_inactive`**

```bash
curl -s -H "Authorization: Bearer $ADMIN" -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/v1/categories?include_inactive=1" | jq '.data[].slug' | head
```

Expect seeded slugs such as `building-materials`, `electrical`, `plumbing`, `finishes`.

4. **Create category (admin)**

```bash
curl -s -X POST -H "Authorization: Bearer $ADMIN" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name_ar":"تجربة","name_en":"Manual Test","slug":"manual-test-cat"}' \
  http://127.0.0.1:8000/api/v1/categories | jq '.success, .data.slug'
```

5. **Reorder**  
   Use an existing category `id` from step 3.

```bash
curl -s -X PUT -H "Authorization: Bearer $ADMIN" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"sort_order":0}' \
  http://127.0.0.1:8000/api/v1/categories/ID/reorder | jq '.success'
```

## Frontend manual checks

1. Log in as admin in the Nuxt app (locale-prefixed route, e.g. `/ar/...`).
2. Open `/ar/admin/categories` (or equivalent with your i18n prefix).
3. Confirm tree lists Arabic names and inactive badges when `include_inactive` data is present.
4. Drag a row onto another row **with the same parent** and confirm toast success + order refresh.
5. Open “تصنيف جديد”, fill Arabic/English names, optionally pick a parent in the selector, save, confirm it appears after refresh.

## Negative checks

- Non-admin `GET /api/v1/categories?include_inactive=1` → `403`.
- `DELETE` on a category that still has children → `422` with validation-style payload.
