# Notifications — Tasks

- [ ] T001 [P] [US1] Add `NotificationType` enum at `backend/app/Enums/NotificationType.php`
- [ ] T002 [US1] Create migrations `backend/database/migrations/*_create_notifications_table.php` and `*_create_notification_preferences_table.php`
- [ ] T003 [P] [US1] Add `PlatformDatabaseNotification` model at `backend/app/Models/PlatformDatabaseNotification.php` and wire `User::notifications()`
- [ ] T004 [P] [US4] Add `NotificationPreference` model + factory `backend/database/factories/NotificationPreferenceFactory.php`
- [ ] T005 [US1] Implement repositories `backend/app/Repositories/NotificationRepository.php` and `NotificationPreferenceRepository.php`
- [ ] T006 [US1] Implement services `backend/app/Services/NotificationService.php` and `NotificationPreferenceService.php`
- [ ] T007 [US1] Add `GenericDatabaseNotification` at `backend/app/Notifications/GenericDatabaseNotification.php`
- [ ] T008 [US1] Add controllers `backend/app/Http/Controllers/Api/V1/NotificationController.php` and `NotificationPreferenceController.php`
- [ ] T009 [US4] Add requests/resources `backend/app/Http/Requests/Api/V1/UpdateNotificationPreferencesRequest.php`, `NotificationResource.php`, `NotificationPreferenceResource.php`
- [ ] T010 [US1] Register routes in `backend/routes/api.php` with Sanctum + throttle
- [ ] T011 [US1] Feature tests `backend/tests/Feature/Api/V1/NotificationFlowTest.php`
- [ ] T012 [US5] Add `frontend/components/notifications/NotificationBell.vue` and update `frontend/components/shell/AppHeader.vue`
- [ ] T013 [US5] Add pages `frontend/pages/notifications/index.vue` and `frontend/pages/notifications/settings.vue`
- [ ] T014 [US5] Extend i18n `frontend/locales/ar.json` and `frontend/locales/en.json` with notification strings
