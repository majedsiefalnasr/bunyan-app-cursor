# Quickstart — Payments (dev)

1. `cd backend && cp .env.example .env` — set `PAYMENTS_WEBHOOK_SECRET=test-secret`
2. `php artisan migrate`
3. Seed or create a **customer** user and an **order** in `pending` status owned by that user.
4. Obtain Sanctum token via `POST /api/v1/auth/login`.
5. `POST /api/v1/payments/initiate` with body:
   ```json
   { "payable_type": "order", "payable_id": 1, "method": "mada" }
   ```
6. `POST /api/v1/payments/{id}/capture` then optional `refund` with `{ "amount": "10.00" }`.
7. Webhook: `POST /api/v1/webhooks/payment` header `X-Payment-Webhook-Secret: test-secret` with JSON `{ "gateway_reference": "...", "status": "completed" }` (shape implemented in controller).
