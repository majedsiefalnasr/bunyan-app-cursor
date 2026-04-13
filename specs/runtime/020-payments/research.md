# Research — Payments

## Laravel

- Route model binding for `Payment` with implicit policy authorization in controller.
- `JsonResource` for API shaping consistent with `OrderResource`.
- Form Requests: `authorize()` delegates to `$this->user()->can(...)`.

## Nuxt 3

- `useAsyncData` + `useApi().apiFetch` for SSR-safe lists.
- Nuxt UI `UTable` for history.

## Sandbox gateway

- No external HTTP in tests: gateway is synchronous PHP class returning deterministic IDs.

## Env

- `PAYMENTS_WEBHOOK_SECRET` — required in non-local prod; `.env.example` entry.
