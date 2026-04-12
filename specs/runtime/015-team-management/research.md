# Research — Team Management

## Laravel

- Route model binding for `User $teamUser` on `projects/{project}/team/{user}` uses `user` parameter name; ensure `{user}` matches controller argument.
- `Str::random(48)` for raw invitation tokens; `hash('sha256', $raw)` for persistence per security checklist.

## Nuxt / Nuxt UI

- Use `UTable` or stacked list with `UBadge` for roles; reuse `useApi` error handling patterns from project pages.

## References

- Existing `ProjectPolicy`, `ProjectService::create`, `ActivityLogService::record`.
