# PR — Document Management

## Summary

**Stage:** Document Management  
**Phase:** 03_PROJECT_MANAGEMENT  
**Branch:** `spec/016-document-management` → `develop`  
**Tasks:** 14 / 14 completed

## What Changed

### Backend

- Added `DocumentCategory` enum, `documents` and `document_versions` tables (soft deletes on documents).
- Implemented `DocumentService` with upload, optional `document_id` versioning, soft delete, and download streaming from the `public` disk.
- Added `DocumentPolicy` (view via parent `Project`; delete for admin, uploader, or project customer).
- Added `ProjectDocumentController` and `DocumentController` with Form Requests and API Resources.
- Registered routes under Sanctum + participant role middleware with throttling on upload/list/download.
- Added `DocumentApiTest` feature coverage.

### Frontend

- New `pages/projects/[id]/documents.vue` (list, upload, versions modal, download, delete).
- Linked from `pages/projects/[id].vue` with new i18n keys (`projects.open_documents`, documents copy).

### Database

- `2026_04_12_180000_create_documents_table.php`
- `2026_04_12_180001_create_document_versions_table.php`

## Breaking Changes

- None (additive API and UI).

## Testing

- [x] Backend feature tests (`php artisan test --filter=DocumentApiTest`)
- [x] Full backend suite (`php artisan test`) — pass in workspace run
- [x] Frontend tests (`npm run test`)
- [x] Lint (`composer run lint`, `npm run lint`)
- [x] Type check (`npm run typecheck`; PHPStan via pre-commit hook on commit)
- [ ] `php artisan migrate --pretend` — requires valid DB credentials in this environment (documented in `VALIDATION_REPORT.md`)

## Checklist

- [x] RBAC middleware on new routes
- [x] Form Request validation on list/upload
- [x] Arabic-first strings (API messages + `ar.json` keys)
- [x] Error contract via `BaseController`
- [x] Eager load `uploadedBy` on document listings

## Related

- Stage File: `specs/phases/03_PROJECT_MANAGEMENT/STAGE_16_DOCUMENT_MANAGEMENT.md`
- Testing Guide: `specs/runtime/016-document-management/guides/TESTING_GUIDE.md`
