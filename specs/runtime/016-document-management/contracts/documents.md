# API Contract — Documents (v1)

Base prefix: `/api/v1` (Sanctum + role middleware as implemented).

## GET `/projects/{project}/documents`

Query:

- `page`, `per_page`
- `filter[category]=blueprint|contract|...`

Response: paginated `DocumentResource` collection.

## POST `/projects/{project}/documents`

Multipart:

- `file` (required)
- `title` (required string)
- `category` (required enum string)
- `document_id` (optional int) — must belong to same project when provided

Response: `DocumentResource` (201).

## GET `/documents/{document}`

Response: `DocumentResource`.

## GET `/documents/{document}/download`

Response: binary download (`200`) with `Content-Disposition: attachment`.

## DELETE `/documents/{document}`

Response: success envelope (`200`).

## GET `/documents/{document}/versions`

Response: `DocumentVersionResource[]` ordered newest first.
