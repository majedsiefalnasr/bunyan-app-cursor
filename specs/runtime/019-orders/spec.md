# Orders — Specification

> **Stage:** Orders  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Branch:** `spec/019-orders`  
> **Generated:** 2026-04-13T15:35:00Z  
> **Source:** `specs/phases/04_COMMERCIAL_LAYER/STAGE_19_ORDERS.md`

## Overview

Deliver commercial **order** management for building materials: create orders from a cart-style payload or from an **accepted RFQ quotation**, track lifecycle with a strict status machine, expose role-scoped APIs and Arabic-first Nuxt UI. Integrate with **inventory** via reservation on confirmation and release on cancellation.

The repository already contains a minimal `orders` / `order_items` API and models; this stage **extends** schema and behavior to match the commercial layer stage file without rewriting unrelated domains.

## Personas & Roles

- **Customer (العميل):** creates orders, confirms/cancels own pending orders, converts accepted quotations to orders, views tracking.
- **Supplier (المورّد):** modeled as **contractor** with `SupplierProfile`; lists orders where `orders.supplier_id` matches their profile; may not mutate customer-owned transitions unless explicitly allowed (fulfillment updates reserved for admin in this slice unless specified otherwise).
- **Admin (الإدارة):** full visibility, status corrections, support actions.

## In Scope

### Backend

- Persisted `order_number` unique human-readable `BNY-YYYYMMDD-XXXX` (daily sequence).
- Nullable links: `quotation_id`, `supplier_id`, `project_id` (existing).
- Monetary breakdown: `subtotal`, `tax_amount`, `shipping_amount`, with `total_amount` remaining canonical line sum + adjustments (documented in plan).
- Timestamps: `confirmed_at`, `shipped_at`, `delivered_at` (nullable).
- Status enum aligned to: **pending → confirmed → processing → shipped → delivered → completed** with **cancelled** terminal; retain **refunded** for payment integration hooks.
- **OrderService** owns creation, totals, transitions, quotation conversion, inventory reserve/release (delegating to **InventoryService** extension).
- Thin **OrderController** + dedicated Form Requests for create, status update, confirm, cancel, quotation conversion.
- `POST /api/v1/quotations/{quotation}/to-order` (customer owner of RFQ + accepted quotation).
- `PUT /api/v1/orders/{order}/confirm`, `PUT /api/v1/orders/{order}/cancel`, `PUT /api/v1/orders/{order}/status` with transition validation.
- RBAC: `auth:sanctum` + role middleware + policies on every mutating route.
- Optional `variant_id` on `order_items` when catalog uses variants.

### Frontend

- `pages/orders/index.vue` — list with role-appropriate columns (customer vs supplier vs admin).
- `pages/orders/[id].vue` — detail + status timeline + confirm/cancel for eligible states.
- Composable `useOrders()` mirroring existing API patterns (`useApi`, Laravel envelope).
- i18n keys under `order.*` (Arabic strings in locale files).

## Out of Scope

- Payment capture, invoicing, refunds processing (downstream stages).
- Multi-warehouse pick/pack workflows beyond default `warehouse_location = default`.
- Push notifications / email templates for shipment (hooks only if trivial).

## User Stories

### US1 — Customer creates an order from items

As a customer, I can `POST /orders` with line items (product, quantity, unit price) and optional `project_id`, receiving an order in **pending** state with generated `order_number`.

### US2 — Customer confirms order

As a customer, I can confirm a **pending** order; system moves to **confirmed**, sets `confirmed_at`, and **reserves** inventory for each line on the default warehouse row.

### US3 — Customer cancels before fulfillment

As a customer, I can cancel from **pending** or **confirmed**; reservations released if applicable; terminal **cancelled**.

### US4 — Customer creates order from accepted quotation

As a customer, I can call `POST /quotations/{id}/to-order` for my RFQ’s **accepted** quotation; order lines mirror quotation items; `quotation_id` and `supplier_id` populated.

### US5 — Supplier lists related orders

As a supplier (contractor profile), I can list orders tied to my `supplier_id`.

### US6 — Admin / workflow status updates

As an admin, I can advance statuses along the allowed graph (e.g. confirmed → processing → shipped → delivered → completed).

## Order Status Machine

Allowed transitions (simplified matrix in `OrderService`):

- `pending` → `confirmed` | `cancelled`
- `confirmed` → `processing` | `cancelled`
- `processing` → `shipped` | `cancelled` (admin-heavy)
- `shipped` → `delivered`
- `delivered` → `completed`
- `cancelled`, `completed`, `refunded` — terminal (no further transitions except admin override documented as forbidden in v1)

## API Surface (v1)

| Method | Route                                     | Roles                                    | Description           |
| ------ | ----------------------------------------- | ---------------------------------------- | --------------------- |
| GET    | `/api/v1/orders`                          | customer, contractor (supplier), admin   | Paginated list        |
| POST   | `/api/v1/orders`                          | customer, admin                          | Create                |
| GET    | `/api/v1/orders/{order}`                  | owner customer, supplier on order, admin | Detail                |
| PUT    | `/api/v1/orders/{order}/confirm`          | customer (owner), admin                  | Confirm + reserve     |
| PUT    | `/api/v1/orders/{order}/cancel`           | customer (owner), admin                  | Cancel + release      |
| PUT    | `/api/v1/orders/{order}/status`           | admin (v1)                               | Controlled transition |
| POST   | `/api/v1/quotations/{quotation}/to-order` | customer (RFQ owner), admin              | Convert quotation     |

## Non-Functional

- Arabic-first copy; RTL layout on UI.
- Structured logging on transitions and inventory reserve/release.
- Standard Bunyan API success/error envelope.

## Dependencies

- Upstream: products, inventory (`reserved_quantity`), RFQ/quotations (accepted path).
- Downstream: payments, invoicing.
