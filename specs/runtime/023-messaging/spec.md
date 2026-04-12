# Specification — Messaging (STAGE_23)

**Phase:** 05_COMMUNICATION_AND_MEDIA  
**Authority:** `specs/phases/05_COMMUNICATION_AND_MEDIA/STAGE_23_MESSAGING.md`

## Summary

Deliver in-app **messaging**: persisted conversations and messages with optional project scope, participant-based authorization, read receipts, optional file attachments, REST API under `/api/v1/conversations`, service and repository layers, policies and Form Requests, Laravel broadcasting for new messages (Echo-compatible private channels; `log` driver in tests), and Nuxt inbox and thread views aligned with Arabic-first RTL and the Bunyan error contract.

## User stories

1. **US1 — Inbox**  
   As an authenticated user, I can list conversations I participate in, ordered by latest activity.

2. **US2 — Start conversation**  
   As an authenticated user, I can create a direct or group conversation with at least one other valid user; optional `project_id` when the creator can view that project.

3. **US3 — Thread**  
   As a participant, I can list messages in a conversation with pagination.

4. **US4 — Send**  
   As a participant, I can send a text message or a message with an uploaded attachment.

5. **US5 — Read receipt**  
   As a participant, I can mark a conversation as read (updates my `last_read_at`).

6. **US6 — Real-time**  
   As a participant with a websocket client, I receive broadcast payloads for new messages on a private channel scoped to the conversation (infrastructure documented; `log`/`null` acceptable in CI).

7. **US7 — Dashboard UI**  
   As a user, I can open messaging pages in Nuxt to browse conversations and view a thread.

## Functional requirements

### Backend

- **Tables (forward-only migrations):** `conversations` (`project_id` nullable FK, `title` nullable, `type` enum `direct` \| `group`, timestamps); `conversation_participants` (`conversation_id`, `user_id`, `last_read_at`, `joined_at`); `messages` (`conversation_id`, `sender_id`, `body` nullable for file-only, `type` `text` \| `file`, `attachment_path` nullable, `deleted_at` soft delete, timestamps).
- **Direct conversations:** exactly two distinct participants; server rejects duplicates or self-only.
- **Group conversations:** title required when `type` is `group`; minimum two distinct other participants (creator included in participant rows).
- **Project scope:** if `project_id` is set, creator must pass `ProjectPolicy::view` for that project.
- **Layers:** `ConversationRepository`, `MessageRepository` (queries only); `ConversationService`, `MessagingService` or combined service for orchestration; thin `ConversationController` (and optionally nested resource for messages — may be methods on same controller).
- **Authorization:** `ConversationPolicy` for `view`, `create`, `send`, `markRead`; resolve conversations for route binding only when the current user is a participant (avoid IDOR).
- **Endpoints (Sanctum; all authenticated roles that can use dashboard):**
  - `GET /api/v1/conversations` — list for current user.
  - `POST /api/v1/conversations` — create; body: `participant_ids`, `type`, optional `title`, optional `project_id`.
  - `GET /api/v1/conversations/{conversation}/messages` — paginated messages.
  - `POST /api/v1/conversations/{conversation}/messages` — send; multipart supported for attachment.
  - `PUT /api/v1/conversations/{conversation}/read` — mark read for current user.
- **Broadcasting:** `MessageSent` (or equivalent) implements `ShouldBroadcast`; channel `conversation.{id}` authorized for participants only; `BROADCAST_CONNECTION=log` in PHPUnit.

### Frontend

- Pages: `messages/index.vue` (inbox list), `messages/[id].vue` (thread + composer), optional `messages/new.vue` or modal for new conversation.
- Use `useApi` / patterns from projects pages; Nuxt UI; Arabic strings via i18n keys.

### Non-goals (this stage)

- Full push notification delivery to mobile devices.
- Message editing and unsend beyond soft-delete admin tooling.
- Typing indicators and presence.

## Acceptance criteria

- All messaging endpoints enforce Sanctum + policy; non-participants cannot read or post; appropriate 403/404 per existing API conventions.
- Feature tests cover create, list, messages index, send, read, and negative cases (non-participant).
- `composer run lint` and `composer run test` pass; frontend `npm run lint`, `npm run typecheck`, `npm run test` pass.

## Clarifications

### Session 2026-04-12

- **RBAC:** Messaging is available to all authenticated roles (no extra `role:` middleware group); authorization is participant-based via policy, not coarse role gates.
- **Read endpoint:** Stage table documents `PUT .../read`; implementation matches that verb and path.
- **Attachments:** Stored on the default `public` disk under `messages/{id}/filename`; max size 5MB; images and common document MIME types only.
- **Broadcasting:** Laravel `install:broadcasting` scaffolding with Reverb skipped; Echo client wiring deferred to env-specific frontend config; backend event + channel authorization required.
