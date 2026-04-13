# Payments — Technical Plan

> **Stage:** Payments | **Branch:** `spec/020-payments`

## Architecture

- **Layers:** Routes → `role:*` middleware + policies → Form Requests → Controllers → `PaymentService` → Repositories → Models.
- **Gateway:** `App\Contracts\Payments\PaymentGatewayContract` implemented by `SandboxPaymentGateway` (config-driven).
- **Webhook:** `PaymentWebhookController` + `ValidatePaymentWebhookSignature` middleware comparing `X-Payment-Webhook-Secret` to `config('payments.webhook_secret')`.
- **Ledger sync (optional v1):** When payment becomes `completed`, upsert `Transaction` type `payment` for the order (idempotent on `payments.id` reference via description or dedicated nullable `payment_id` column — **plan uses `transactions.reference` pattern** `payment:{id}` to avoid migration to transactions table if undesired; alternatively skip ledger sync in v1 — **Decision: skip ledger auto-sync in v1** to reduce scope; document hook for STAGE follow-up).

## Database

Migration `create_payments_table` / `create_payment_attempts_table` (single migration file acceptable):

**payments**

- id, morphs `payable`, `user_id` (payer), `amount` decimal(15,2), `currency` default SAR, `method`, `status`, `gateway_reference` nullable, `paid_at` nullable, timestamps
- indexes: payable morph, user_id, status, created_at

**payment_attempts**

- id, `payment_id` FK, `type`, `amount`, `status`, `gateway_id` string nullable, `gateway_response` json nullable, timestamps

## Enums

- `PaymentStatus`: pending, processing, completed, failed, refunded
- `PaymentMethod`: card, mada, bank_transfer
- `PaymentAttemptType`: charge, refund, void
- `PaymentAttemptStatus`: pending, succeeded, failed

## Endpoints & RBAC

| Endpoint                        | Middleware                        | Policy              |
| ------------------------------- | --------------------------------- | ------------------- |
| POST payments/initiate          | auth:sanctum, role:customer,admin | initiate: own order |
| GET payments/history            | auth:sanctum, role:customer,admin | scoped query        |
| GET payments/{payment}          | auth:sanctum, role:customer,admin | view                |
| POST payments/{payment}/capture | auth:sanctum, role:customer,admin | update              |
| POST payments/{payment}/refund  | auth:sanctum, role:customer,admin | update              |
| POST webhooks/payment           | throttle + webhook secret         | n/a                 |

## Frontend

- Pages under `frontend/pages/payments/` using `useApi` and Nuxt UI.
- i18n keys under `payments.*` in `ar.json` / `en.json`.

## Testing

- `PaymentFlowTest` feature: full sandbox lifecycle.
- Webhook feature tests with secret header.

## Logging

- Use structured context keys per observability rules; financial ops `Log::channel('audit')` when channel exists, else `Log::info` with `action` key (match existing codebase patterns).
