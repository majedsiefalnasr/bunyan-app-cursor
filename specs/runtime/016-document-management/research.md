# Research — Document Management

## Laravel filesystem

- Use `Storage::disk(config('filesystems.default'))` with `public` for local/tests.
- `store()` with generated UUID directory segments avoids collisions; mirror `MediaService` path style using `documents/{uuid}/...`.

## Sanctum + policies

- Follow `MediaController` + `StoreMediaUploadRequest` authorization style: FormRequest `authorize()` delegates to model policy where appropriate; documents additionally require resolved `Project` context.

## Nuxt UI upload

- Use native `<input type="file">` with Tailwind drag overlay pattern consistent with `pages/media` if present; otherwise minimal `UButton` trigger + hidden input.

## Prior art in repo

- `MediaService`, `MediaRepository`, `MediaPolicy`, `MediaApiTest` provide the closest blueprint for upload validation, storage, and tests.
