# Quickstart — Messaging

## Backend

```bash
cd backend
composer install
cp .env.example .env  # if needed
php artisan migrate
php artisan serve
```

Create two users via register/login, obtain Sanctum tokens, `POST /api/v1/conversations` with `participant_ids` and `type=direct`.

## Frontend

```bash
cd frontend
npm install
npm run dev
```

Open `/messages` when logged in.

## Env

- `BROADCAST_CONNECTION=log` for local tests.
- File uploads require `php artisan storage:link` for public disk URLs in non-test env.
