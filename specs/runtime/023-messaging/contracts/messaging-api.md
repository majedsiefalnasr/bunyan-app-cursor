# Messaging API Contract (v1)

Base: `/api/v1`  
Auth: `Authorization: Bearer {token}`

## POST /conversations

**Body (JSON)**

```json
{
  "type": "direct",
  "participant_ids": [2],
  "project_id": null,
  "title": null
}
```

**Response:** `201` — `data` contains `ConversationResource` with `id`, `type`, `title`, `project_id`, `participants`, `updated_at`.

## GET /conversations

**Response:** `200` — paginated list of conversation summaries for current user.

## GET /conversations/{id}/messages

**Query:** `page`, `per_page`

**Response:** `200` — paginated `MessageResource` collection.

## POST /conversations/{id}/messages

**Body:** `multipart/form-data` or JSON — `body` (string, optional if attachment), `attachment` (file, optional).

## PUT /conversations/{id}/read

**Response:** `200` — success with updated participant `last_read_at`.

## Errors

Uses platform `success` / `error` envelope with registered `ErrorCode` values for 403/404/422.
