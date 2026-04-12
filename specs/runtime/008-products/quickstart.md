# Quickstart — Products

## Prereqs

- Backend `.env` configured; MySQL running
- Frontend `.env` with API base URL

## Migrate

```bash
cd backend && php artisan migrate
```

## Smoke API

```bash
# Authenticate as any user, then:
curl -sS -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/v1/products | jq
curl -sS -H "Authorization: Bearer $ADMIN_TOKEN" -X POST http://localhost:8000/api/v1/admin/products \
  -H "Content-Type: application/json" \
  -d '{"name":"أسمنت","category_id":1,"price":45,"quantity":10}'
```

## Smoke UI

1. Log in as a non-guest user.
2. Open `/products` — list renders.
3. Open a product card — `/products/{id}` detail renders.
