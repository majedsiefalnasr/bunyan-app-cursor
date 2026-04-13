# Quickstart — Cost Estimator

## Backend

```bash
cd backend
composer install
php artisan migrate
php artisan serve
```

Create an estimate:

```bash
curl -sS -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -X POST "http://localhost:8000/api/v1/projects/1/estimates" \
  -d '{"title":"سقف تجريبي","markup_percentage":10}'
```

## Frontend

```bash
cd frontend
npm install
npm run dev
```

Open `http://localhost:3000/projects/1/estimates` (after login as a project member).

## Tests

```bash
cd backend && composer run test -- --filter=EstimateApi
cd frontend && npm run test
```
