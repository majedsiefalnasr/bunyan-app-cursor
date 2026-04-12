# Research — Projects

## Laravel 12

- FormRequest `authorize()` delegates to policies; route middleware `role:` remains for coarse RBAC; fine-grained checks in policy + service for transitions.
- `Schema::table` additive migrations with `down()` reversing column adds and status value remap.

## Nuxt 3 / Nuxt UI

- `useApi().apiFetch` for authenticated JSON calls; `useRouter` for navigation after create.
- `UBadge`, `UButton`, `UCard`, `UForm`, `UInput`, `USelect` for dashboard pages.

## References

- Existing `CategoryService` / `CategoryRepository` patterns in codebase (parallel structure).
- `DESIGN.md` — shadow-as-border, Geist, achromatic palette for new pages.
