# Specification — Document Management (STAGE_16)

**Phase:** 03_PROJECT_MANAGEMENT  
**Authority:** `specs/phases/03_PROJECT_MANAGEMENT/STAGE_16_DOCUMENT_MANAGEMENT.md`

## Summary

Deliver **project-scoped document management** for Bunyan: polymorphic `documents` (initial allowlist: `App\Models\Project`) with categories, file storage on the `public` disk (S3-ready via Laravel filesystem config), **version history** via `document_versions`, Sanctum-protected REST endpoints under `/api/v1`, `DocumentPolicy` tied to `ProjectPolicy::view` for read access and stricter rules for delete, thin controllers, `DocumentService` + `DocumentRepository`, Form Request validation, Arabic-first messages, feature tests, and a Nuxt project sub-page to list, filter by category, upload (with optional new version of an existing document), preview links, and version history using Nuxt UI.

## User stories

1. **US1 — List project documents**  
   As a project participant, I can list documents for a project I am allowed to view, with optional category filter and pagination.

2. **US2 — Upload**  
   As a project participant, I can upload a new document (title, category, file). MIME and size are validated server-side.

3. **US3 — New version**  
   As a project participant, I can upload a file that becomes the next version of an existing document in the same project by supplying `document_id` of the prior document.

4. **US4 — Metadata & download**  
   As a project participant, I can fetch document metadata and download the current file bytes when I can view the parent project.

5. **US5 — Version history**  
   As a project participant, I can list all stored versions for a document.

6. **US6 — Delete**  
   As an uploader, project customer, or admin, I can soft-delete a document (hidden from listings; files retained until hard-delete policy is defined — soft delete row only for this stage).

7. **US7 — Project UI**  
   As a user, I can open `/projects/{id}/documents` to manage documents with RTL layout and i18n strings.

## Functional requirements

### Backend

- **Migrations (forward-only):**
  - `documents`: `id`, `documentable_type`, `documentable_id`, `category` (string backed enum), `title`, `original_filename`, `storage_path`, `mime_type`, `size_bytes`, `version` (unsigned int, default 1), `uploaded_by` (FK users), `created_at`, `updated_at`, `deleted_at` (soft deletes), indexes on morph columns and `uploaded_by`.
  - `document_versions`: `id`, `document_id` (FK, cascade on hard delete — use `cascade` for versions when document force-deleted; on soft delete keep rows), `version`, `storage_path`, `size_bytes`, `uploaded_by`, `created_at`, index on `document_id`.
- **Enum:** `DocumentCategory`: Blueprint, Contract, Permit, Invoice, Photo, Report, Other (persisted as string values).
- **Storage:** Store under `documents/{uuid}/v{n}_{safeOriginalName}` on configured disk (`public` in local/test). Allow common office/image/PDF MIME types; max **25MB** per upload for this stage.
- **Layers:** `DocumentRepository`, `DocumentVersionRepository` (optional single repo file with both if small), `DocumentService` orchestrates storage, versioning, soft delete; controllers remain thin.
- **Authorization:** `DocumentPolicy` — `view`, `download`, `versions` require `ProjectPolicy::view` on parent project; `create`/`addVersion` require `ProjectPolicy::view`; `delete` allows admin, project customer (`customer_id`), or original uploader (`uploaded_by`).
- **Endpoints (Sanctum; roles aligned with existing project read group):**  
  Middleware group: `role:customer,contractor,supervising_architect,field_engineer,admin` for all routes below (policy still enforces project scoping).
  - `GET /api/v1/projects/{project}/documents` — paginated list, `filter[category]=`.
  - `POST /api/v1/projects/{project}/documents` — multipart `file`, `title`, `category`, optional `document_id` for versioning.
  - `GET /api/v1/documents/{document}` — metadata.
  - `GET /api/v1/documents/{document}/download` — stream / download response with correct headers.
  - `DELETE /api/v1/documents/{document}` — soft delete.
  - `GET /api/v1/documents/{document}/versions` — list versions newest-first.
- **Rate limiting:** throttle uploads (`30/min` per user) similar to media upload.

### Frontend

- Page: `pages/projects/[id]/documents.vue` with `auth` middleware, Nuxt UI (`UCard`, `UButton`, `USelect` or tabs for category filter, table or list for documents), `useApi`, i18n keys in `ar.json` / `en.json`.
- Link from `pages/projects/[id].vue` to documents page.
- Display upload dropzone, show versions in a slideover or nested list.

### Non-goals (this stage)

- OCR, full-text search, and e-signature workflows.
- Cross-project document sharing and public share links.
- Virus scanning pipeline (document as future hardening).

## Acceptance criteria

- All routes require Sanctum + role middleware; policies prevent cross-project access.
- Versioning: posting with `document_id` increments `documents.version`, appends `document_versions`, updates current `storage_path` to the latest file; prior version files remain on disk for audit.
- Feature tests cover list forbidden cross-tenant, upload happy path, download, versions list, delete permissions, version upload.
- `composer run lint` + `composer run test`; `npm run lint` + `npm run typecheck` + `npm run test` in `frontend/`.
- `php artisan migrate --pretend` succeeds.

## Clarifications

### Session 2026-04-12

- **RBAC route group:** Reuse the same authenticated role set as project phase/task reads (`customer`, `contractor`, `supervising_architect`, `field_engineer`, `admin`); fine-grained access enforced in `DocumentPolicy` using `ProjectPolicy`.
- **Versioning mechanism:** Optional `document_id` on `POST /projects/{project}/documents` creates the next version for that document; omitting `document_id` creates a new document chain.
- **Max upload size:** 25MB per file for documents in this stage.
- **Soft delete:** `DELETE` sets `deleted_at`; listings exclude soft-deleted rows.
