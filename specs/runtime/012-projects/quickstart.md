# Quickstart — Projects

## Backend

```bash
cd backend
composer install
php artisan migrate
php artisan test --filter=Project
```

## Sample API calls (with Sanctum token)

```http
GET /api/v1/projects
GET /api/v1/projects/1
POST /api/v1/projects
PUT /api/v1/projects/1
PUT /api/v1/projects/1/status
GET /api/v1/projects/1/timeline
```

## Frontend

```bash
cd frontend
npm install
npm run dev
```

Open `/projects` after logging in as a customer or other stakeholder.
