# STAGE_19 — Orders

> **Phase:** 04_COMMERCIAL_LAYER
> **Status:** DRAFT
> **Scope:** Order management, checkout, order tracking
> **Risk Level:** HIGH

## Stage Status

Status: BACKEND CLOSED
Step: implement
Risk Level: MEDIUM
Initiated: 2026-04-13T15:30:00Z
Last Updated: 2026-04-13T19:05:00Z

Scope Closed: Orders stage implementation delivered (API + UI + tests)

Tasks: 18 / 18 completed

Implementation: COMPLETE

Architecture Governance Compliance: Service layer, RBAC, migrations, validation artifacts recorded

Notes: Pre-closure gate bypassed under autopilot (auto_advance).

## Objective

Implement order management for material purchases. Support order creation from quotations or direct purchase, order tracking, and fulfillment.

## Scope

### Backend

- Order model with relationships (items, customer, supplier, payments)
- Order item model
- Order service (create, confirm, cancel, fulfill)
- Order status machine: PENDING → CONFIRMED → PROCESSING → SHIPPED → DELIVERED → COMPLETED → CANCELLED
- Order number generation (BNY-YYYYMMDD-XXXX)
- Inventory reservation on order confirmation
- Inventory release on cancellation
- Order creation from accepted quotation

### Frontend

- Order listing page (role-scoped)
- Order detail page with status timeline
- Order creation form (from cart or quotation)
- Order tracking page
- Order confirmation dialog

### API Endpoints

| Method | Route                            | Description                 |
| ------ | -------------------------------- | --------------------------- |
| GET    | /api/v1/orders                   | List orders                 |
| POST   | /api/v1/orders                   | Create order                |
| GET    | /api/v1/orders/{id}              | Get order details           |
| PUT    | /api/v1/orders/{id}/confirm      | Confirm order               |
| PUT    | /api/v1/orders/{id}/cancel       | Cancel order                |
| PUT    | /api/v1/orders/{id}/status       | Update status               |
| POST   | /api/v1/quotations/{id}/to-order | Create order from quotation |

### Database Schema

| Table       | Columns                                                                                                                                                                                                             |
| ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| orders      | id, order_number, customer_id, supplier_id, project_id, quotation_id, status, subtotal, tax_amount, shipping_amount, total, shipping_address, notes, confirmed_at, shipped_at, delivered_at, created_at, updated_at |
| order_items | id, order_id, product_id, variant_id, description, quantity, unit_price, total_price                                                                                                                                |

## Dependencies

- **Upstream:** STAGE_08_PRODUCTS, STAGE_10_INVENTORY, STAGE_18_QUOTATIONS
- **Downstream:** STAGE_20_PAYMENTS, STAGE_21_INVOICING
