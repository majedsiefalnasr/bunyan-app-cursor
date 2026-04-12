# Research — Messaging

## Laravel

- Sanctum SPA/API tokens already configured for `/api/v1`.
- Broadcasting: Laravel 11 `install:broadcasting` adds `routes/channels.php` and registers `withBroadcasting` in `bootstrap/app.php` when present in installer output — verify after run.
- File storage: `Storage::disk('public')->putFile()` with `FilesystemAdapter`.

## Nuxt

- `useApi` + `apiFetch` for JSON and `FormData` multipart uploads (`$fetch` supports FormData body).

## References

- Internal: `ProjectController`, `ProjectPolicy`, `BaseRepository`, `ApiResponse` trait.
- Stage sketch: `STAGE_23_MESSAGING.md` endpoint table.
