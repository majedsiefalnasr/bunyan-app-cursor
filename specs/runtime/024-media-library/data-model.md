# Data Model — Media Library

## Table: `media`

| Column            | Type            | Notes                                    |
| ----------------- | --------------- | ---------------------------------------- |
| id                | bigint PK       |                                          |
| mediable_type     | string nullable | Morph                                    |
| mediable_id       | bigint nullable | Morph                                    |
| collection        | string          | default `default`, indexed               |
| filename          | string          | Stored basename                          |
| original_filename | string          | Client name                              |
| mime_type         | string          |                                          |
| disk              | string          | default `public`                         |
| path              | string          | Relative to disk root                    |
| thumb_path        | string nullable | Relative JPEG thumb when generated       |
| size_bytes        | unsigned bigint |                                          |
| dimensions_json   | json nullable   | `{"width":n,"height":n}`                 |
| alt_text_ar       | string nullable |                                          |
| alt_text_en       | string nullable |                                          |
| sort_order        | unsigned int    | default 0                                |
| uploaded_by       | FK → users.id   | cascade on delete (set null or restrict) |
| is_temporary      | boolean         | default false, indexed                   |
| created_at        | timestamp       |                                          |
| updated_at        | timestamp       |                                          |

### Indexes

- (`uploaded_by`, `created_at`)
- (`collection`)
- (`mediable_type`, `mediable_id`)
- (`is_temporary`, `created_at`) for prune queries

## Relationships

- `Media::mediable()` morphTo
- `Media::uploader()` belongsTo User

## Allowlisted morph map

- `App\Models\Project` → table `projects`
