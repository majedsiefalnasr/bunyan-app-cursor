# Research — Orders

## Laravel

- Use `DB::transaction` for confirm/cancel + inventory mutation to keep ACID guarantees.
- `lockForUpdate()` on parent product row already used in `InventoryService::adjust`; mirror for reservation deltas on `inventories.reserved_quantity`.

## Nuxt / i18n

- Follow `useRfqs` / `pages/rfqs` patterns: `useApi` + Laravel envelope parsing.
- Use `definePageMeta` with `middleware: ['auth']` and role arrays matching backend access.

## Existing Code

- `OrderController` currently mutates Eloquent directly — replaced by `OrderService` + `OrderRepository` per ADR layering.
