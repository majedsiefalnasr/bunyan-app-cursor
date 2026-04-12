# Data Model — Document Management

## documents

| Column                | Type               | Notes                         |
| --------------------- | ------------------ | ----------------------------- |
| id                    | bigint PK          |                               |
| documentable_type     | string             | `App\Models\Project`          |
| documentable_id       | bigint             | FK logical via morph          |
| category              | string             | `DocumentCategory` enum value |
| title                 | string(255)        |                               |
| original_filename     | string(255)        |                               |
| storage_path          | string(500)        | path on disk                  |
| mime_type             | string(128)        |                               |
| size_bytes            | unsignedBigInteger |                               |
| version               | unsignedInteger    | default 1                     |
| uploaded_by           | FK users           |                               |
| created_at/updated_at | timestamps         |                               |
| deleted_at            | timestamp nullable | soft deletes                  |

Indexes: (`documentable_type`, `documentable_id`), (`uploaded_by`), (`category`), (`deleted_at`).

## document_versions

| Column       | Type               | Notes             |
| ------------ | ------------------ | ----------------- |
| id           | bigint PK          |                   |
| document_id  | bigint FK          | cascade on delete |
| version      | unsignedInteger    | matches snapshot  |
| storage_path | string(500)        | immutable per row |
| size_bytes   | unsignedBigInteger |                   |
| uploaded_by  | FK users           |                   |
| created_at   | timestamp          |                   |

Index: (`document_id`, `version` unique) recommended to prevent duplicate version numbers.

## Relationships

- `Project::morphMany(Document::class, 'documentable')`
- `Document::morphTo()` documentable
- `Document::hasMany(DocumentVersion::class)`
- `Document::belongsTo(User::class, 'uploaded_by')`
