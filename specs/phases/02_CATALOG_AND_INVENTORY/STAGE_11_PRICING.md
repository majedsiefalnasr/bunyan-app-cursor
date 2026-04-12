# STAGE_11 — Pricing

> **Phase:** 02_CATALOG_AND_INVENTORY
> **Status:** DRAFT
> **Scope:** Pricing rules, bulk pricing tiers, currency handling
> **Risk Level:** MEDIUM

## Stage Status

Status: DRAFT
Step: clarify
Risk Level: LOW
Last Updated: 2026-04-12T17:12:00Z

Scope Defined:

- Price tiers, calculate API, SAR formatting, price history on base price change, admin UI; admin write path; variant fallback pricing

Deferred Scope:

- Supplier-owned tier editing, discount engine, non-SAR currencies

Architecture Governance Compliance: Clarifications resolved — planning authorized

Notes: All specification ambiguities resolved. Ready for technical planning.

## Objective

Implement pricing engine supporting base prices, bulk/tier pricing, and currency formatting for SAR (Saudi Riyal).

## Scope

### Backend

- Price tier model (quantity-based pricing)
- Pricing service (calculate price based on quantity, tiers)
- Currency formatting helper (SAR, Arabic numeral support)
- Price history tracking
- Discount rules engine (future extension point)

### Frontend

- Price tier configuration form (Supplier)
- Price display component with SAR formatting
- Bulk pricing table display (product detail page)

### API Endpoints

| Method | Route                         | Description                  |
| ------ | ----------------------------- | ---------------------------- |
| GET    | /api/v1/products/{id}/pricing | Get pricing tiers            |
| PUT    | /api/v1/products/{id}/pricing | Update pricing tiers         |
| POST   | /api/v1/pricing/calculate     | Calculate price for quantity |

### Database Schema

| Table         | Columns                                                                                    |
| ------------- | ------------------------------------------------------------------------------------------ |
| price_tiers   | id, product_id, variant_id, min_quantity, max_quantity, unit_price, created_at, updated_at |
| price_history | id, product_id, old_price, new_price, changed_by, changed_at                               |

## Dependencies

- **Upstream:** STAGE_08_PRODUCTS
- **Downstream:** STAGE_17_COST_ESTIMATOR, STAGE_19_ORDERS
