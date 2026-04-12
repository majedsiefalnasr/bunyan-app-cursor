# Tasks — Messaging

- [x] T001 [P] [US1] Add migration `backend/database/migrations/*_create_conversations_table.php` for `conversations`.
- [x] T002 [P] [US1] Add migration `backend/database/migrations/*_create_conversation_participants_table.php` for `conversation_participants`.
- [x] T003 [P] [US3] Add migration `backend/database/migrations/*_create_messages_table.php` for `messages`.
- [x] T004 [US1] Add Eloquent models `backend/app/Models/Conversation.php`, `ConversationParticipant.php`, `Message.php` with relationships and casts.
- [x] T005 [US1] Add `backend/app/Repositories/ConversationRepository.php` and `MessageRepository.php` extending `BaseRepository`.
- [x] T006 [US2] Add `backend/app/Services/ConversationService.php` for create/list/participant scope.
- [x] T007 [US4] Add `backend/app/Services/MessageService.php` and `backend/app/Events/MessageSent.php` broadcasting private channel.
- [x] T008 [US1] Add `backend/app/Policies/ConversationPolicy.php` and route-model binding scope for participants.
- [x] T009 [US2] Add Form Requests under `backend/app/Http/Requests/Api/V1/` for store conversation, store message, mark read.
- [x] T010 [US1] Add API Resources `ConversationResource.php`, `MessageResource.php` under `backend/app/Http/Resources/Api/V1/`.
- [x] T011 [US1] Add `ConversationController.php`, `ConversationMessageController.php` and register routes in `backend/routes/api.php`.
- [x] T012 [US6] Run broadcasting install / wire `routes/channels.php` and `bootstrap/app.php` for channel authorization.
- [x] T013 [US1] Add `backend/tests/Feature/MessagingTest.php` covering CRUD paths and forbidden access.
- [x] T014 [US7] Add Nuxt pages `frontend/pages/messages/index.vue`, `frontend/pages/messages/[id].vue`, i18n keys, link from `frontend/pages/dashboard/index.vue`.
