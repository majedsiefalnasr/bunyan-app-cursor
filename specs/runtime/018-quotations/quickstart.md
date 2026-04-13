# Quickstart — Quotations

## Backend

From repo root:

```bash
cd backend
composer install
php artisan migrate
php artisan test
```

## Frontend

From repo root:

```bash
cd frontend
npm install
npm run dev
```

## Manual Smoke Flow (Happy Path)

1. Login as **Customer**.
2. Create an RFQ draft with 2–3 items.
3. Send RFQ.
4. Login as **Supplier** and submit quotation with per-item pricing.
5. Back as **Customer**, view RFQ details and compare quotations.
6. Award a quotation and confirm RFQ is **AWARDED**.
