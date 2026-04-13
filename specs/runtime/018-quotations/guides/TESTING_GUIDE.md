# Testing Guide — Quotations / RFQs (STAGE_18)

## Preconditions

- Backend `.env` with working `DB_*` (MySQL).
- `cd backend && php artisan migrate --no-interaction`
- Frontend `NUXT_PUBLIC_API_BASE_URL` pointing at the Laravel API root including `/api` (e.g. `http://127.0.0.1:8000/api`).

## Automated

```bash
cd backend && composer run lint && composer run test
cd ../frontend && npm run lint && npm run typecheck && npm run test
```

**Focused RFQ suites:**

```bash
cd backend && php artisan test tests/Feature/Api/V1/RfqApiTest.php tests/Feature/Api/V1/QuotationApiTest.php tests/Unit/Services/QuotationServiceAcceptTest.php
cd ../frontend && npm run test -- tests/unit/components/QuotationComparisonTable.spec.ts
```

## Manual — API (Sanctum)

Use `Authorization: Bearer <token>` and JSON as appropriate.

1. **Customer** — `POST /api/v1/rfqs` with `title` + `items[]` (`description`, `quantity`, `unit`) → `201`, draft RFQ.
2. **Customer** — `POST /api/v1/rfqs/{id}/send` with optional `response_deadline` if not set on draft → `200`, status moves toward **quoting** with targets.
3. **Contractor** (verified supplier, targeted) — `POST /api/v1/rfqs/{id}/quotations` with `items: [{ rfq_item_id, unit_price }]` → `201` / revise on repeat.
4. **Customer** — `POST /api/v1/rfqs/{id}/evaluate` while **quoting** → `200`, status **evaluation**; second call → `422`.
5. **Customer** — `PUT /api/v1/rfqs/{id}/quotations/{quotationId}/accept` in **evaluation** → `200`, RFQ **awarded**, other quotations rejected.
6. **Customer** — `POST /api/v1/rfqs/{id}/close` after **awarded** → `200`, **closed**.
7. **Customer** — `GET /api/v1/rfqs/{id}/compare` → comparison payload (`items`, `quotations`, `cells`).
8. **Negative:** mismatched `rfq` / `quotation` in nested accept route → `404` (scoped bindings).

## Manual — UI

- **Customer:** `/ar/rfqs` list → new RFQ → detail: send (set deadline if needed) → compare → begin evaluation → award from detail.
- **Contractor:** `/ar/contractor/rfqs` → open invitation → submit line unit prices while RFQ is **quoting**.
- **Dashboard:** Customer and contractor cards link to the above when logged in with the matching role.

## Rate limits (sanity)

- Repeated `POST .../send` on the same RFQ should eventually return **429**; another RFQ’s send should still succeed (per-RFQ bucket).
