# Testing Guide — Commercial Pages

> **Phase:** 07_FRONTEND_APPLICATION > **Generated:** 2026-04-14T08:59:00Z

## Prerequisites

```bash
# Backend
rtk cd backend
rtk composer install
rtk php artisan migrate:fresh --seed

# Frontend
rtk cd ../frontend
rtk npm install
```

## Running Tests

### Frontend (recommended for this stage)

```bash
rtk cd frontend
rtk proxy npm run lint
rtk proxy npm run typecheck
rtk proxy npm run test
```

### Backend (optional; no backend changes in this stage)

```bash
rtk cd backend
rtk php artisan test
```

## Manual Test Scenarios

### Scenario 1 — RFQ create route compatibility

**Preconditions:**

- Frontend dev server running

**Steps:**

1. Visit `/rfqs/create`
2. Confirm you are redirected to `/rfqs/new` (or to login if unauthenticated)

**Expected Result:**

- The app does not remain on `/rfqs/create`

### Scenario 2 — Checkout route compatibility

**Preconditions:**

- Frontend dev server running

**Steps:**

1. Visit `/checkout`
2. Confirm you are redirected to `/payments/checkout` (or to login if unauthenticated)

**Expected Result:**

- The app does not remain on `/checkout`

### Scenario 3 — Supplier quote submission page

**Preconditions:**

- Authenticated user with `supplier` role (or contractor/admin if permitted by backend)
- An RFQ exists with items

**Steps:**

1. Visit `/rfqs/<id>/quote`
2. Fill unit prices for each item
3. Submit

**Expected Result:**

- Quotation is submitted and you are redirected to `/rfqs/<id>`

### Scenario 4 — Payment success page (optional payment refetch)

**Preconditions:**

- Authenticated user
- A payment exists with id `<paymentId>`

**Steps:**

1. Visit `/payment/success?paymentId=<paymentId>`
2. Observe the details card

**Expected Result:**

- Page renders success UI and shows payment details

## API Test Endpoints (observed)

| Method | Endpoint                  | Auth | Expected Status |
| ------ | ------------------------- | ---- | --------------- |
| GET    | `/v1/rfqs`                | ✅   | 200             |
| POST   | `/v1/rfqs/:id/quotations` | ✅   | 200/201         |
| POST   | `/v1/payments/initiate`   | ✅   | 200/201         |
| GET    | `/v1/payments/:id`        | ✅   | 200             |

## Common Issues

| Issue                                     | Cause                           | Fix                                     |
| ----------------------------------------- | ------------------------------- | --------------------------------------- |
| Redirect goes to login                    | Route requires auth middleware  | Login with a seeded account, then retry |
| Payment details not shown on success page | Missing `paymentId` query param | Use `/payment/success?paymentId=<id>`   |
