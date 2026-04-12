# Requirements Checklist — Media Library (STAGE_24)

- [x] Specification drafted from stage file and aligned with Bunyan layering
- [x] API routes versioned under `/api/v1/media`
- [x] Sanctum authentication on all media endpoints
- [x] Authorization via `MediaPolicy` (no client-only auth)
- [x] Form Request classes for upload and query validation
- [x] Service + repository separation; controllers remain thin
- [x] File type and size validation server-side
- [x] Arabic/English alt text fields supported
- [x] Error contract (`success`, `data`, `message`, `errors` / `error`) preserved
- [x] Migrations forward-only with `down()` defined
