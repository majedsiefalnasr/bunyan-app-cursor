# Tasks — Document Management (STAGE_16)

- [ ] T001 [P] [US2] Add `DocumentCategory` enum at `backend/app/Enums/DocumentCategory.php`
- [ ] T002 [US2] Create `documents` migration in `backend/database/migrations/`
- [ ] T003 [US3] Create `document_versions` migration in `backend/database/migrations/`
- [ ] T004 [P] [US1] Add `Document` + `DocumentVersion` models and `Project` morph relation in `backend/app/Models/`
- [ ] T005 [P] [US1] Add `DocumentRepository` + `DocumentVersionRepository` in `backend/app/Repositories/`
- [ ] T006 [US2] Implement `DocumentService` in `backend/app/Services/DocumentService.php`
- [ ] T007 [US1] Add `DocumentPolicy` in `backend/app/Policies/DocumentPolicy.php`
- [ ] T008 [P] [US2] Add Form Requests under `backend/app/Http/Requests/Api/V1/` for document operations
- [ ] T009 [P] [US4] Add `DocumentResource` + `DocumentVersionResource` under `backend/app/Http/Resources/Api/V1/`
- [ ] T010 [US1] Add `ProjectDocumentController` + `DocumentController` and register routes in `backend/routes/api.php`
- [ ] T011 [US1] Add `backend/tests/Feature/Api/V1/DocumentApiTest.php` covering RBAC + flows
- [ ] T012 [US7] Add `frontend/pages/projects/[id]/documents.vue` using Nuxt UI + `useApi`
- [ ] T013 [US7] Link documents page from `frontend/pages/projects/[id].vue` and add i18n keys in `frontend/i18n/locales/*.json`
- [ ] T014 [US2] Wire service provider / policy discovery if not auto-discovered (verify Laravel policy mapping)
