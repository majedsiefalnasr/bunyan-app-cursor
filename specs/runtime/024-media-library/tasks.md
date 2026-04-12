# Tasks — Media Library (STAGE_24)

- [ ] T001 [P] [US1] Add `create_media_table` migration at `backend/database/migrations/2026_04_12_170000_create_media_table.php`
- [ ] T002 [P] [US1] Create `App\Models\Media` with morphs, casts, and `uploader` relation at `backend/app/Models/Media.php`
- [ ] T003 [US1] Add `uploadedMedia()` on `User` and `media()` morphMany on `Project` in `backend/app/Models/User.php` and `backend/app/Models/Project.php`
- [ ] T004 [US1] Implement `MediaRepository` at `backend/app/Repositories/MediaRepository.php`
- [ ] T005 [US1] Implement `MediaService` (store, delete, dimensions, optional GD thumb) at `backend/app/Services/MediaService.php`
- [ ] T006 [US1] Add Form Requests `StoreMediaUploadRequest`, `IndexMediaRequest` under `backend/app/Http/Requests/Api/V1/`
- [ ] T007 [US3] Add `MediaPolicy` at `backend/app/Policies/MediaPolicy.php`
- [ ] T008 [US1] Add `MediaResource` at `backend/app/Http/Resources/Api/V1/MediaResource.php`
- [ ] T009 [US1] Add `MediaController` at `backend/app/Http/Controllers/Api/V1/MediaController.php`
- [ ] T010 [US1] Register routes in `backend/routes/api.php` with Sanctum + throttles
- [ ] T011 [US5] Add `media:prune-temporary` Artisan command and `Schedule` entry in `backend/routes/console.php`
- [ ] T012 [US1] Add feature tests `backend/tests/Feature/MediaApiTest.php` covering upload, list scope, show 403, delete, prune command
- [ ] T013 [US6] Add Nuxt page `frontend/pages/media/index.vue` and i18n keys in `frontend/locales/ar.json` and `frontend/locales/en.json`
