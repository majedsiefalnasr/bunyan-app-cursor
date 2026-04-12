# Technical Plan — Document Management (STAGE_16)

## Overview

Implement document storage and versioning for projects using Laravel filesystem (`public` disk), Eloquent models with morph `documentable` (Project only in this stage), service/repository layering, policies, Form Requests, API Resources, feature tests, and a Nuxt page under the project area.

## Backend modules

| Area        | Files / actions                                                                                                                                                              |
| ----------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Enum        | `app/Enums/DocumentCategory.php`                                                                                                                                             |
| Migrations  | `create_documents_table`, `create_document_versions_table`                                                                                                                   |
| Models      | `Document`, `DocumentVersion` + relations                                                                                                                                    |
| Repos       | `DocumentRepository`, `DocumentVersionRepository`                                                                                                                            |
| Service     | `DocumentService` (upload, version, soft-delete, resolve disk/path)                                                                                                          |
| Policy      | `DocumentPolicy`                                                                                                                                                             |
| Requests    | `IndexProjectDocumentsRequest`, `StoreProjectDocumentRequest`, `ShowDocumentRequest` (or authorize in policy only) — prefer dedicated Form Requests mirroring Media patterns |
| Resources   | `DocumentResource`, `DocumentVersionResource`                                                                                                                                |
| Controllers | `ProjectDocumentController` (index, store), `DocumentController` (show, download, destroy, versions)                                                                         |
| Routes      | Register inside `auth:sanctum` + `role:customer,contractor,supervising_architect,field_engineer,admin` group                                                                 |

## Authorization matrix

| Action                            | Rule                                            |
| --------------------------------- | ----------------------------------------------- |
| list / show / download / versions | `ProjectPolicy::view` on owning project         |
| upload / add version              | `ProjectPolicy::view` on project                |
| delete                            | Admin OR `uploaded_by` OR project `customer_id` |

## Frontend modules

| Area | Files                                        |
| ---- | -------------------------------------------- |
| Page | `frontend/pages/projects/[id]/documents.vue` |
| Nav  | Add button on `pages/projects/[id].vue`      |
| i18n | `documents.*` keys in `ar.json` / `en.json`  |

## Validation

- MIME allowlist: pdf, common images, doc/docx, xls/xlsx (align with safe office list).
- Max 25MB.

## Testing

- `DocumentApiTest` covering RBAC, upload, version bump, download, delete.

## Rollout

- Forward-only migrations; no data backfill required.
