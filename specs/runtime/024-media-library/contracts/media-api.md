# API Contract — Media (`/api/v1/media`)

## POST `/media/upload`

**Auth:** Bearer Sanctum

**Body:** `multipart/form-data`

| Field         | Rules                                 |
| ------------- | ------------------------------------- |
| file          | required, file, max KB per type       |
| collection    | optional, string, max:64              |
| alt_text_ar   | optional, string, max:500             |
| alt_text_en   | optional, string, max:500             |
| mediable_type | optional, in:App\Models\Project       |
| mediable_id   | optional, integer, exists:projects,id |
| is_temporary  | optional, boolean                     |
| sort_order    | optional, integer, min:0              |

**Success:** `201` with `MediaResource`.

## GET `/media`

**Query:** `page`, `per_page` (max 50), `collection`, `mime_type` (prefix), `temporary` (0/1).

**Success:** `200` paginated `MediaResource` collection.

## GET `/media/{id}`

**Success:** `200` single `MediaResource`.

## DELETE `/media/{id}`

**Success:** `200` with success message; files removed from disk.
