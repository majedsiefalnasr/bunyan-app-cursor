# Catalog Pages — Requirements Checklist

## Routes & middleware

- [ ] `/categories` behind `auth` middleware
- [ ] `/categories/:slug` behind `auth` middleware
- [ ] `/products` listing behind `auth` with filters + pagination
- [ ] `/products/:slug` detail resolves id or SKU via API
- [ ] `/search` (index) behind `auth`; honors `q` query param

## UI / i18n

- [ ] Headings and labels use `$t()` with keys in `ar.json` / `en.json`
- [ ] Cards use shadow-as-border pattern from `DESIGN.md`
- [ ] `ProductCard` exposes `data-testid="product-card"`

## API integration

- [ ] List requests use `useApi().apiFetch`
- [ ] Paginated responses support both flat `data[]` and `{ data: [], meta }` shapes
- [ ] Category detail uses slug segment matching backend route key

## Backend

- [ ] `Category` implicit binding uses `slug`
- [ ] `Product` resolves binding by id or SKU
- [ ] `ProductResource` includes `sku` field
- [ ] PHPUnit updated for slug-based category URLs in admin flows

## Tests

- [ ] Vitest: `useProductCatalogQuery` param building
- [ ] Playwright: catalog auth gate and/or mocked listing smoke
