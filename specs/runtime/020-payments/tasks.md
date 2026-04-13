# Payments — Tasks

- [x] T001 [US1] Add migration `create_payments_and_payment_attempts_tables` in `backend/database/migrations/`
- [x] T002 [P] Add enums `PaymentStatus`, `PaymentMethod`, `PaymentAttemptType`, `PaymentAttemptStatus` in `backend/app/Enums/`
- [x] T003 Add models `Payment`, `PaymentAttempt` with casts and relations in `backend/app/Models/`
- [x] T004 [US1] Add `payments()` morphMany on `Order` in `backend/app/Models/Order.php`
- [x] T005 [P] Add `PaymentGatewayContract` and `SandboxPaymentGateway` under `backend/app/Contracts/Payments/` and `backend/app/Services/Payments/Gateways/`
- [x] T006 Add `PaymentRepository` and `PaymentAttemptRepository` in `backend/app/Repositories/`
- [x] T007 [US1] Implement `PaymentService` (initiate, capture, refund, webhook) in `backend/app/Services/Payments/PaymentService.php`
- [x] T008 Add `PaymentPolicy` and register authorization in `backend/app/Policies/PaymentPolicy.php`
- [x] T009 [P] Add Form Requests `InitiatePaymentRequest`, `RefundPaymentRequest` in `backend/app/Http/Requests/Api/V1/Payments/`
- [x] T010 [P] Add `PaymentResource` and `PaymentAttemptResource` in `backend/app/Http/Resources/Api/V1/`
- [x] T011 Add `PaymentController` and `PaymentWebhookController` in `backend/app/Http/Controllers/Api/V1/`
- [x] T012 Add `ValidatePaymentWebhookSignature` middleware in `backend/app/Http/Middleware/`
- [x] T013 Wire routes in `backend/routes/api.php`, `bootstrap/app.php` if needed, and `backend/config/payments.php`
- [x] T014 [US1–US6] Add `PaymentFlowTest` in `backend/tests/Feature/Payments/PaymentFlowTest.php`
- [x] T015 [US7] Add `usePayments.ts` composable in `frontend/composables/usePayments.ts`
- [x] T016 [US7] Add Nuxt pages `frontend/pages/payments/index.vue`, `frontend/pages/payments/[id].vue`, `frontend/pages/payments/checkout.vue`
- [x] T017 [US7] Add i18n keys in `frontend/locales/ar.json` and `frontend/locales/en.json`
