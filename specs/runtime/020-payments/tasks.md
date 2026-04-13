# Payments — Tasks

- [ ] T001 [US1] Add migration `create_payments_and_payment_attempts_tables` in `backend/database/migrations/`
- [ ] T002 [P] Add enums `PaymentStatus`, `PaymentMethod`, `PaymentAttemptType`, `PaymentAttemptStatus` in `backend/app/Enums/`
- [ ] T003 Add models `Payment`, `PaymentAttempt` with casts and relations in `backend/app/Models/`
- [ ] T004 [US1] Add `payments()` morphMany on `Order` in `backend/app/Models/Order.php`
- [ ] T005 [P] Add `PaymentGatewayContract` and `SandboxPaymentGateway` under `backend/app/Contracts/Payments/` and `backend/app/Services/Payments/Gateways/`
- [ ] T006 Add `PaymentRepository` and `PaymentAttemptRepository` in `backend/app/Repositories/`
- [ ] T007 [US1] Implement `PaymentService` (initiate, capture, refund, webhook) in `backend/app/Services/Payments/PaymentService.php`
- [ ] T008 Add `PaymentPolicy` and register authorization in `backend/app/Policies/PaymentPolicy.php`
- [ ] T009 [P] Add Form Requests `InitiatePaymentRequest`, `RefundPaymentRequest` in `backend/app/Http/Requests/Api/V1/Payments/`
- [ ] T010 [P] Add `PaymentResource` and `PaymentAttemptResource` in `backend/app/Http/Resources/Api/V1/`
- [ ] T011 Add `PaymentController` and `PaymentWebhookController` in `backend/app/Http/Controllers/Api/V1/`
- [ ] T012 Add `ValidatePaymentWebhookSignature` middleware in `backend/app/Http/Middleware/`
- [ ] T013 Wire routes in `backend/routes/api.php`, `bootstrap/app.php` if needed, and `backend/config/payments.php`
- [ ] T014 [US1–US6] Add `PaymentFlowTest` in `backend/tests/Feature/Payments/PaymentFlowTest.php`
- [ ] T015 [US7] Add `usePayments.ts` composable in `frontend/composables/usePayments.ts`
- [ ] T016 [US7] Add Nuxt pages `frontend/pages/payments/index.vue`, `frontend/pages/payments/[id].vue`, `frontend/pages/payments/checkout.vue`
- [ ] T017 [US7] Add i18n keys in `frontend/i18n/locales/ar.json` and `frontend/i18n/locales/en.json`
