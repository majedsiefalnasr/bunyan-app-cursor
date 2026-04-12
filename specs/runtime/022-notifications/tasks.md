# Notifications — Tasks

- [x] T001 [P] [US1] Add `NotificationType` enum at `backend/app/Enums/NotificationType.php`
- [x] T002 [US1] Create migrations `backend/database/migrations/*_create_notifications_table.php` and `*_create_notification_preferences_table.php`
- [x] T003 [P] [US1] Add `PlatformDatabaseNotification` model at `backend/app/Models/PlatformDatabaseNotification.php` and wire `User::notifications()`
- [x] T004 [P] [US4] Add `NotificationPreference` model + factory `backend/database/factories/NotificationPreferenceFactory.php`
- [x] T005 [US1] Implement repositories `backend/app/Repositories/NotificationRepository.php` and `NotificationPreferenceRepository.php`
- [x] T006 [US1] Implement services `backend/app/Services/NotificationService.php` and `NotificationPreferenceService.php`
- [x] T007 [US1] Add `GenericDatabaseNotification` at `backend/app/Notifications/GenericDatabaseNotification.php`
- [x] T008 [US1] Add controllers `backend/app/Http/Controllers/Api/V1/NotificationController.php` and `NotificationPreferenceController.php`
- [x] T009 [US4] Add requests/resources `backend/app/Http/Requests/Api/V1/UpdateNotificationPreferencesRequest.php`, `NotificationResource.php`, `NotificationPreferenceResource.php`
- [x] T010 [US1] Register routes in `backend/routes/api.php` with Sanctum + throttle
- [x] T011 [US1] Feature tests `backend/tests/Feature/Api/V1/NotificationFlowTest.php`
- [x] T012 [US5] Add `frontend/components/notifications/NotificationBell.vue` and update `frontend/components/shell/AppHeader.vue`
- [x] T013 [US5] Add pages `frontend/pages/notifications/index.vue` and `frontend/pages/notifications/settings.vue`
- [x] T014 [US5] Extend i18n `frontend/locales/ar.json` and `frontend/locales/en.json` with notification strings
