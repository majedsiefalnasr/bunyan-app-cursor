# Quickstart — Document Management

## Backend

```bash
cd backend
composer install
php artisan migrate
php artisan serve
```

Create a Sanctum token for a seeded user (if available) or register/login via API, then:

```bash
curl -H "Authorization: Bearer $TOKEN" \
  -F "file=@./README.md" \
  -F "title=عقد" \
  -F "category=contract" \
  https://localhost/api/v1/projects/1/documents
```

## Frontend

```bash
cd frontend
npm install
npm run dev
```

Open `/projects/1/documents` while authenticated as a project participant.
