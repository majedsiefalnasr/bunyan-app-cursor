# Catalog Pages — Requirements Checklist

## Routes & middleware

- [x] `/categories` behind `auth` middleware
- [x] `/categories/:slug` behind `auth` middleware
- [x] `/products` listing behind `auth` with filters + pagination
- [x] `/products/:slug` detail resolves id or SKU via API
- [x] `/search` (index) behind `auth`; honors `q` query param

## UI / i18n

- [x] Headings and labels use `$t()` with keys in `ar.json` / `en.json`
- [x] Cards use shadow-as-border pattern from `DESIGN.md`
- [x] `ProductCard` exposes `data-testid="product-card"`

## API integration

- [x] List requests use `useApi().apiFetch`
- [x] Paginated responses support both flat `data[]` and `{ data: [], meta }` shapes
- [x] Category detail uses slug segment matching backend route key

## Backend

- [x] `Category` implicit binding uses `slug`
- [x] `Product` resolves binding by id or SKU
- [x] `ProductResource` includes `sku` field
- [x] PHPUnit updated for slug-based category URLs in admin flows

## Tests

- [x] Vitest: `useProductCatalogQuery` param building
- [x] Playwright: catalog auth gate (`middleware.spec.ts` products redirect)
