# Quickstart — API Foundation

## Prerequisites

- PHP 8.2+, Composer, MySQL (or sqlite for tests per project config)

## Run API locally

```bash
cd backend
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan serve
```

## Smoke checks

```bash
curl -sS http://127.0.0.1:8000/api/v1/health | jq .
curl -sS -I http://127.0.0.1:8000/up
```

Expected: `GET /api/v1/health` returns JSON with `success: true` and a `data` object including `service` and `time`.

## Correlation ID

```bash
curl -sS -H "X-Correlation-ID: demo-123" http://127.0.0.1:8000/api/v1/health | jq .
```

Logs should include `correlation_id` of `demo-123` when structured logging is enabled.
