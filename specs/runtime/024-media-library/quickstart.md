# Quickstart — Media Library

## Backend

```bash
cd backend
composer run migrate
php artisan serve
```

Create a Sanctum token for a test user, then:

```bash
curl -sS -X POST http://127.0.0.1:8000/api/v1/media/upload \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json" \
  -F "file=@/path/to/image.jpg" \
  -F "collection=avatars"
```

## Frontend

```bash
cd frontend
npm run dev
```

Open `/media` while logged in (same session/cookie or token strategy as rest of dashboard).

## Tests

```bash
cd backend && composer run test -- --filter=Media
cd frontend && npm run test
```
