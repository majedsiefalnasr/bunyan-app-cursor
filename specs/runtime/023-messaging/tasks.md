# Tasks — Messaging

- [ ] T001 [P] [US1] Add migration `backend/database/migrations/*_create_conversations_table.php` for `conversations`.
- [ ] T002 [P] [US1] Add migration `backend/database/migrations/*_create_conversation_participants_table.php` for `conversation_participants`.
- [ ] T003 [P] [US3] Add migration `backend/database/migrations/*_create_messages_table.php` for `messages`.
- [ ] T004 [US1] Add Eloquent models `backend/app/Models/Conversation.php`, `ConversationParticipant.php`, `Message.php` with relationships and casts.
- [ ] T005 [US1] Add `backend/app/Repositories/ConversationRepository.php` and `MessageRepository.php` extending `BaseRepository`.
- [ ] T006 [US2] Add `backend/app/Services/ConversationService.php` for create/list/participant scope.
- [ ] T007 [US4] Add `backend/app/Services/MessageService.php` and `backend/app/Events/MessageSent.php` broadcasting private channel.
- [ ] T008 [US1] Add `backend/app/Policies/ConversationPolicy.php` and route-model binding scope for participants.
- [ ] T009 [US2] Add Form Requests under `backend/app/Http/Requests/Api/V1/` for store conversation, store message, mark read.
- [ ] T010 [US1] Add API Resources `ConversationResource.php`, `MessageResource.php` under `backend/app/Http/Resources/Api/V1/`.
- [ ] T011 [US1] Add `ConversationController.php`, `ConversationMessageController.php` and register routes in `backend/routes/api.php`.
- [ ] T012 [US6] Run broadcasting install / wire `routes/channels.php` and `bootstrap/app.php` for channel authorization.
- [ ] T013 [US1] Add `backend/tests/Feature/MessagingTest.php` covering CRUD paths and forbidden access.
- [ ] T014 [US7] Add Nuxt pages `frontend/pages/messages/index.vue`, `frontend/pages/messages/[id].vue`, i18n keys, link from `frontend/pages/dashboard/index.vue`.
