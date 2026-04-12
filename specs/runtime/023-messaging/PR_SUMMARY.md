# PR — Messaging

## Summary

**Stage:** Messaging  
**Phase:** 05_COMMUNICATION_AND_MEDIA  
**Branch:** `spec/023-messaging` → `develop`  
**Tasks:** 14 / 14 completed

## What Changed

### Backend

- Added conversations, participants, and messages schema (migrations `2026_04_12_160000`–`160002`).
- Implemented `ConversationService`, `MessageService`, repositories, policies, form requests, API resources, and controllers under `/api/v1/conversations`.
- Added `MessageSent` broadcast event and private channel authorization in `routes/channels.php`.
- Registered participant-scoped `conversation` route binding in `AppServiceProvider`.
- Published Laravel broadcasting config and `bootstrap/app.php` channels wiring.
- Feature tests in `tests/Feature/MessagingTest.php`.

### Frontend

- New pages `messages/index.vue` and `messages/[id].vue` using `useApi`, auth middleware, Nuxt UI, and DESIGN.md-style surfaces.
- Navigation item `nav.messages` and locale strings in `locales/ar.json` and `locales/en.json`.

### Database

- `conversations`, `conversation_participants`, `messages` tables with FKs and indexes.

## Breaking Changes

- None.

## Testing

- [x] Unit + feature tests pass (`composer run test`)
- [x] Frontend tests pass (`npm run test`)
- [x] Lint passes (`composer run lint`, `npm run lint`)
- [x] Type check passes (`composer run analyze`, `npm run typecheck`)
- [x] Migration pretend validated with sqlite override (see `reports/LOCAL_CI_REPORT.md`)

## Checklist

- [x] Sanctum + participant authorization on all new routes
- [x] Form Request validation on mutating endpoints
- [x] Arabic/RTL strings via i18n keys
- [x] Error contract via existing `BaseController` / `ApiResponse`
- [x] Eager loading on conversation list and message index
- [x] Migration forward-only with `down()`

## Related

- Runtime artifacts: `specs/runtime/023-messaging/`
- Stage definition: `specs/phases/05_COMMUNICATION_AND_MEDIA/STAGE_23_MESSAGING.md`
