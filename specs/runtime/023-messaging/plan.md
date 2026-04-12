# Technical Plan — Messaging

## Architecture

- **HTTP:** Laravel 11 `routes/api.php` under `auth:sanctum`; dedicated `throttle:60,1` group for messaging mutating routes.
- **Layers:** `ConversationRepository`, `MessageRepository` extend `BaseRepository`; `ConversationService` handles create/list/scoped resolve; `MessageService` handles send + broadcast dispatch; controllers delegate only.
- **AuthZ:** `ConversationPolicy`; route-model binding via `Conversation` scoped where participant `user_id` = `auth()->id()`.
- **Broadcasting:** Run `php artisan install:broadcasting --without-reverb --without-node`; add `MessageSent` implementing `ShouldBroadcast`; `routes/channels.php` authorizes `PrivateChannel('conversation.{id}')` for participants; `phpunit.xml` sets `BROADCAST_CONNECTION=log`.
- **Files:** `store` on `public` disk, path `messages/{message_id}/...`.

## Migrations

1. `create_conversations_table` — FK nullable `project_id` → `projects`, `title`, `type` string, timestamps.
2. `create_conversation_participants_table` — unique `[conversation_id, user_id]`, `last_read_at`, `joined_at`.
3. `create_messages_table` — FKs, `body` text nullable, `type`, `attachment_path`, soft deletes.

## API Surface

| Method | Path                                          | Handler                               |
| ------ | --------------------------------------------- | ------------------------------------- |
| GET    | /api/v1/conversations                         | `ConversationController@index`        |
| POST   | /api/v1/conversations                         | `ConversationController@store`        |
| GET    | /api/v1/conversations/{conversation}/messages | `ConversationMessageController@index` |
| POST   | /api/v1/conversations/{conversation}/messages | `ConversationMessageController@store` |
| PUT    | /api/v1/conversations/{conversation}/read     | `ConversationController@markRead`     |

## Frontend

- `pages/messages/index.vue`, `pages/messages/[id].vue`; link from `dashboard/index.vue`.
- i18n keys under `messages.*`.

## Testing

- `tests/Feature/MessagingTest.php` — multi-user Sanctum tokens, participant vs outsider.

## Risks

- Binding scope must return 404 for non-participants (not 403) to avoid leaking existence — **Decision:** use 404 for non-member `show`-like reads per existing IDOR-hardening pattern.
