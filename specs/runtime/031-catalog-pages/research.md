# Research — Catalog Pages

## Laravel implicit route binding

- `getRouteKeyName()` on `Category` directs `{category}` to `slug` column (unique, seeded).
- Custom `resolveRouteBinding()` on `Product` supports numeric id and `sku` without a schema migration.

## Nuxt UI (v4)

- `UPagination` with `v-model:page` and `:items-per-page` / `:total` per Nuxt UI docs (aligned with `admin/users.vue` patterns).
- `UAccordion`, `UCheckbox`, `UInput`, `UCard` for filter sidebar and cards.

## Playwright (existing repo)

- `NUXT_PUBLIC_API_BASE_URL=''` in e2e webServer → intercept `/v1/*` on dev origin.
- Auth via `auth_token` cookie matching `useCookie` in Pinia store.
