# Research — Pricing

## Laravel

- Use `decimal:2` casts for monetary fields on `PriceTier` and history columns to match `Product`.
- Use database transactions in `PricingService::syncTiers` when deleting and re-inserting tier rows for a product scope.

## Sanctum / RBAC

- Reuse existing `auth:sanctum` + `role:admin` route groups in `routes/api.php` for admin tier replacement.
- Authorize reads with `$this->authorize('view', $product)`.

## Nuxt / i18n

- Use Western numerals for money unless product standard says otherwise; currency symbol ر.س or `SAR` suffix in Arabic copy — align with existing product pages.

## References

- `backend/app/Http/Controllers/Api/V1/ProductController.php` — thin controller pattern.
- `backend/app/Services/ProductService.php` — extension point for price history.
