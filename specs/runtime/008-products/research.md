# Research — Products

## Laravel 11

- Constructor injection for `ProductService` is auto-resolved by the container when type-hinted on `ProductController`.
- `Resource::collection` with paginator preserves pagination meta in JSON (already used).

## Nuxt 3

- `useApi()` composable is the standard HTTP client for authenticated fetches.
- `definePageMeta({ middleware: 'auth' })` matches other catalog-adjacent pages (e.g. projects).

## Existing code reuse

- `ProductRepository` and `BaseRepository` already provide CRUD primitives.
- Global `POST /api/v1/media/upload` (`MediaController`) remains the binary ingress; product gallery rows store canonical paths.

## References

- `backend/app/Services/CategoryService.php` — transaction + validation style reference.
- `backend/routes/api.php` — RBAC grouping patterns.
