# Technical Plan — Pricing

## Overview

Add `price_tiers` and `price_histories` tables, `PricingService` + dedicated repositories, thin HTTP controllers for read/sync/calculate, and Nuxt surfaces for admin tier management and customer tier display. Extend `ProductService::update` to persist `PriceHistory` when `price` changes.

## Backend

| Layer        | Change                                                                                                                         |
| ------------ | ------------------------------------------------------------------------------------------------------------------------------ |
| Migrations   | `create_price_tiers_table`; `create_price_histories_table`                                                                     |
| Models       | `PriceTier`, `PriceHistory`; relations on `Product`, `ProductVariant`, `User`                                                  |
| Repositories | `PriceTierRepository`, `PriceHistoryRepository`                                                                                |
| Service      | `PricingService` (list tiers, replace tiers with overlap validation, calculate line)                                           |
| HTTP         | `PricingController` or split: `ProductPricingController` + `PricingCalculationController`; Form Requests; API Resources        |
| Routes       | `GET products/{product}/pricing`; `PUT admin/products/{product}/pricing`; `POST pricing/calculate` under `auth:sanctum` + RBAC |
| Product flow | Inject `PriceHistoryRepository` into `ProductService` to write history rows when `price` changes                               |

## Calculation algorithm

1. Resolve `Product` and optional `ProductVariant` (must belong to product).
2. If variant-specific tiers exist, search bands for `variant_id = X`; else use `variant_id IS NULL` tiers.
3. If a tier matches `quantity`, `unit_price` = tier `unit_price`.
4. Else `unit_price` = `product.price` + (`variant.price_modifier` ?? 0).
5. `line_total` = `unit_price * quantity` (2 d.p.); `currency` = `SAR`.

## Frontend

| Page                                    | Purpose                                     |
| --------------------------------------- | ------------------------------------------- |
| `pages/admin/products/[id]/pricing.vue` | Admin tier editor (replace list)            |
| `pages/products/[id].vue`               | Show SAR-formatted base price + tiers table |

| Composable          | Purpose                    |
| ------------------- | -------------------------- |
| `useSarPriceFormat` | Format decimals for Arabic |

## Testing

- `tests/Feature/Api/V1/PricingTest.php` (or split): GET pricing, PUT tiers admin + overlap error, POST calculate, unauthorized paths, inactive product rules.
- Optional unit test for overlap helper if extracted.

## Rollout

1. `php artisan migrate`
2. Smoke: admin save tiers → product detail shows table → calculate API.
