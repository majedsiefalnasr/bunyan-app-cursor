# Testing Guide — Payments

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T21:05:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env
# Set APP_KEY, DB_*, and PAYMENTS_WEBHOOK_SECRET=dev-webhook-secret
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

```bash
cd frontend && npm install && npm run dev
```

## Running Tests

### Backend — payment feature tests

```bash
cd backend && php artisan test --filter=PaymentFlowTest
```

### Frontend — full Vitest

```bash
cd frontend && npm run test
```

### Lint / static analysis (matches pre-commit)

```bash
cd backend && composer run lint && ./vendor/bin/phpstan analyse --memory-limit=512M
cd frontend && npm run lint && npx nuxi typecheck
```

## Manual Test Scenarios

### Scenario 1 — Customer pays a pending order (API)

**Preconditions:** Customer user with Sanctum token; order `id=1` in `pending` with `total_amount=100.00`.

1. `POST http://127.0.0.1:8000/api/v1/payments/initiate`  
   Headers: `Authorization: Bearer <token>`, `Accept: application/json`, `Content-Type: application/json`  
   Body:

   ```json
   { "payable_type": "order", "payable_id": 1, "method": "mada" }
   ```

2. Expect `201`, `data.status` = `processing`, note `data.id` as `PAYMENT_ID`.

3. `POST http://127.0.0.1:8000/api/v1/payments/PAYMENT_ID/capture` with same auth headers → `200`, `data.status` = `completed`.

4. `POST http://127.0.0.1:8000/api/v1/payments/PAYMENT_ID/refund` body `{}` → `200`, `data.status` = `refunded`.

### Scenario 2 — Webhook secret

**Preconditions:** `.env` has `PAYMENTS_WEBHOOK_SECRET=dev-webhook-secret`; a payment exists with `gateway_reference=test-ref`.

```bash
curl -sS -X POST http://127.0.0.1:8000/api/v1/webhooks/payment \
  -H "Content-Type: application/json" \
  -H "X-Payment-Webhook-Secret: dev-webhook-secret" \
  -d '{"gateway_reference":"test-ref","status":"completed"}'
```

Expect `200` and `success: true`. Repeat with wrong secret → `403`.

### Scenario 3 — Nuxt UI (customer)

1. Log in as customer in the Nuxt app.
2. Open `/ar/payments` — list loads (empty or populated).
3. Open `/ar/payments/checkout?orderId=1` — select method, submit — redirects to payments list after success.

## Smoke checklist

- [ ] Contractor cannot call `/api/v1/payments/initiate` (403 from `role` middleware).
- [ ] Customer sees only own rows on `/api/v1/payments/history`.
