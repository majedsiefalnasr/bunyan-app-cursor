# Specification — Media Library (STAGE_24)

**Phase:** 05_COMMUNICATION_AND_MEDIA  
**Authority:** `specs/phases/05_COMMUNICATION_AND_MEDIA/STAGE_24_MEDIA_LIBRARY.md`

## Summary

Deliver a **centralized media library** for the Bunyan platform: polymorphic `media` records tied to optional domain models, authenticated upload/list/show/delete under `/api/v1/media`, filesystem storage on the `public` disk (local; S3-ready via config), image dimension capture and optional JPEG thumbnails via PHP GD when available, temporary upload flow with scheduled cleanup, Arabic-first metadata (`alt_text_ar` / `alt_text_en`), thin controllers, `MediaService` + `MediaRepository`, `MediaPolicy` for authorization, Form Request validation, feature tests, and a minimal Nuxt dashboard area to browse and upload media using Nuxt UI and existing API client patterns.

## User stories

1. **US1 — Upload**  
   As an authenticated user, I can upload an allowed file to the library with optional collection label, optional alt text (AR/EN), and optional `mediable_type` / `mediable_id` when I am allowed to attach to that model.

2. **US2 — List**  
   As an authenticated user, I can list my uploads with pagination and filters (`collection`, `mime_type` prefix, `temporary`).

3. **US3 — Details**  
   As an authenticated user, I can fetch metadata for a single media item I am allowed to access.

4. **US4 — Delete**  
   As an authenticated user, I can delete media I own (or admin), removing DB row and stored files (original + thumbnail when present).

5. **US5 — Temporary uploads**  
   As an authenticated user, I can mark an upload as temporary; the system deletes orphaned temporary files after a defined TTL via a scheduled job.

6. **US6 — Dashboard UI**  
   As a user, I can open a Nuxt page to upload (drag-and-drop) and browse my recent media with RTL layout.

## Functional requirements

### Backend

- **Migration (forward-only):** `media` table with columns aligned to the stage schema: `id`, `mediable_type`, `mediable_id` (nullable morph), `collection` (string, indexed), `filename`, `original_filename`, `mime_type`, `disk`, `path`, `thumb_path` (nullable), `size_bytes`, `dimensions_json` (nullable JSON: width/height), `alt_text_ar`, `alt_text_en` (nullable strings), `sort_order` (unsigned int, default 0), `uploaded_by` (FK users), `is_temporary` (boolean, default false), `created_at`, `updated_at`. Indexes on `uploaded_by`, `collection`, morph columns.
- **Storage:** Store files under `media/{uuid}/{stored_filename}` on configured disk (`public` in local/test). Validate MIME (images: jpeg/png/webp/gif; documents: pdf; video: mp4) and max size (e.g. 15MB images, 50MB video — document in spec).
- **Thumbnails:** For raster images, generate optional JPEG thumbnail (max dimension 400px) when GD is available; otherwise leave `thumb_path` null.
- **Layers:** `MediaRepository` for queries/persistence; `MediaService` for orchestration (validation helpers, storage, thumbnail, delete files); thin `MediaController`.
- **Authorization:** `MediaPolicy` — `view`, `delete`, `update` (metadata): owner (`uploaded_by`) or admin; `create` for any authenticated user. Listing scoped to current user unless admin (admin may pass `user_id` filter — optional; default non-admin sees own only).
- **Endpoints (Sanctum; all authenticated dashboard roles):**
  - `POST /api/v1/media/upload` — multipart `file`, optional `collection`, `alt_text_ar`, `alt_text_en`, `mediable_type`, `mediable_id`, `is_temporary`, `sort_order`.
  - `GET /api/v1/media` — paginated index with filters.
  - `GET /api/v1/media/{media}` — show (policy).
  - `DELETE /api/v1/media/{media}` — destroy (policy).
- **Mediable attachment:** When `mediable_type` / `mediable_id` are provided, resolve model class from allowlist (`App\Models\Project`, etc.) and authorize user can `view` that model before saving morph link.
- **Rate limiting:** Upload route throttled (e.g. 30/min per user) in addition to global API middleware where appropriate.

### Frontend

- Route: `pages/media/index.vue` (or under `pages/dashboard/media`) using Nuxt UI (`UCard`, `UButton`, file input / drag overlay), `useApi` composable, Arabic copy via i18n keys.
- Display grid of thumbnails or placeholders; show upload progress state; link to API URLs for previews.

### Non-goals (this stage)

- Full CDN integration and signed URL TTL management (document env for future S3).
- In-browser image cropper and lightbox gallery polish beyond basic preview links.
- Watermarking pipeline.

## Acceptance criteria

- All media routes require Sanctum; policy prevents cross-user access for non-admins.
- Feature tests: upload success, list scoped, show forbidden for other user, delete removes files, temporary cleanup job deletes eligible rows/files.
- `composer run lint` + `composer run test` pass; frontend `npm run lint`, `npm run typecheck`, `npm run test` pass.
- `php artisan migrate --pretend` succeeds.

## Clarifications

### Session 2026-04-12

- **RBAC:** No extra `role:` middleware group; any authenticated user may use the media library. Admin retains broader list access via policy/service.
- **Allowlisted mediable types:** `App\Models\Project` only for morph attachment in this stage (extend later for products/documents).
- **Max upload size:** 15MB for images and documents; 50MB for video/mp4.
- **Thumbnail driver:** PHP GD only when `extension_loaded('gd')`; otherwise skip thumbnail generation without failing upload.
