# Technical Plan — Media Library (STAGE_24)

## Architecture

- **HTTP:** `MediaController` extends `BaseController`; returns `sendSuccess` / `authorize` failures via framework + `MediaPolicy`.
- **Validation:** `StoreMediaUploadRequest`, `IndexMediaRequest` (query filters + pagination).
- **Domain:** `App\Models\Media` morphs to `mediable`; `belongsTo` `User` as `uploader`.
- **Persistence:** `MediaRepository` encapsulates `Media::query()` and create/delete.
- **Application:** `MediaService` coordinates storage put/delete, optional GD thumbnail, dimension extraction, mediable resolution + `ProjectPolicy::view` when morph set.
- **Cleanup:** `PruneTemporaryMedia` job (delete records + files older than TTL); `routes/console.php` schedule hourly.
- **API Resources:** `MediaResource` exposes URLs via `Storage::disk($disk)->url()` for `path` and `thumb_path`.

## Routes (`routes/api.php`)

Inside `auth:sanctum`:

- `Route::middleware(['throttle:30,1'])->post('media/upload', ...)`
- `Route::middleware(['throttle:60,1'])->get('media', ...)`
- `get('media/{media}', ...)`
- `delete('media/{media}', ...)`

## Database

Single `media` table per `data-model.md`.

## Frontend

- `frontend/pages/media/index.vue` — list + upload; uses `useApi` and i18n keys under `media.*`.

## Testing

- `tests/Feature/MediaApiTest.php` — actingAs user, Storage::fake, upload, index scope, show 403 cross-user, delete, prune job.

## Risks

- GD unavailable in some CI images: tests assert upload still succeeds; thumbnail may be null.
