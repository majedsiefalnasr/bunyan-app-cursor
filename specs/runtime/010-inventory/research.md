# Research — Inventory

## Laravel 11

- Use `DB::transaction()` + `lockForUpdate()` on inventory row during adjust to prevent lost updates under concurrency.
- `Schema::create` forward-only; `down()` drops in reverse FK order (movements before inventories if FK from movements to inventory — movements reference product/variant only, not inventory id, so order flexible).

## Sanctum + Policies

- `AuthorizesRequests` in `BaseController` with `$this->authorize('manageInventory', $product)`.
- `Gate::before` admin bypass remains; contractor evaluated in `ProductPolicy::manageInventory`.

## Nuxt 3

- Admin pages use `definePageMeta({ middleware: ['auth', 'role'], roles: ['admin'] })` per existing `admin/categories.vue`.
