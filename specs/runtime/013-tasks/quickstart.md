# Quickstart — Tasks

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan test --filter=ProjectTask
```

```bash
cd frontend && npm install && npm run dev
# Open /projects/{id}/tasks
```
