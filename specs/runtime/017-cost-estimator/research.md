# Research — Cost Estimator

## Laravel

- **Policies:** `AuthorizesRequests` + `$this->authorize('view', $estimate)` with `Estimate` mapping to `EstimatePolicy`.
- **CSV response:** `StreamedResponse` with `Content-Type: text/csv; charset=UTF-8` and UTF-8 BOM `\xEF\xBB\xBF` prefix for Excel.
- **Route model binding:** Default `{estimate}` resolves by id; policy ensures project membership.

## Nuxt

- **`useApi` / `apiFetch`:** Same pattern as `documents.vue` — bearer from Sanctum state.
- **Nuxt UI:** `UCard`, `UTable`, `UButton`, `UBadge` for status colors (neutral palette).

## References

- Existing `DocumentService` / `ProjectDocumentController` for thin controller + service layout.
- `ProjectPolicy::view` for cross-role project access including `field_engineer` with reports.
