# Research — Dashboard

## Laravel

- **Caching:** `Cache::remember` with closure; use `int` TTL from config for per-user keys.
- **Pagination:** `paginate($perPage)` on query builder; wrap in `ActivityLogResource::collection` consistent with `ActivityLogController`.

## Existing Codebase

- `ActivityLog` + `ActivityLogResource` for activity rows.
- `UserRole` enum drives branching in services.
- `Order::scopeCompleted` exists; also include `delivered` status for revenue per spec clarification.
- `SupplierProfile` links contractor `user_id` to `orders.supplier_id`.

## Nuxt

- `useApi().apiFetch` for authenticated JSON GET.
- `useAsyncData` or `onMounted` + `ref` for dashboard snapshot on `pages/dashboard/index.vue`.

## Redis / Cache Driver

- Use framework default cache store (`file` or `redis` per env); no custom Redis wiring required for MVP.
