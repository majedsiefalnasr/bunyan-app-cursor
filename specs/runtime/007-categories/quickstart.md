# Quickstart — Categories

## Backend

```bash
cd backend
php artisan migrate
php artisan db:seed --class=CategorySeeder
```

## API smoke (with admin token)

```bash
curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  http://localhost:8000/api/v1/categories | jq .
```

## Frontend

```bash
cd frontend
npm run dev
```

Open (locale prefix as configured): `/ar/admin/categories` as admin user.
