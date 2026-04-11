# Data Model — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z

## Tables

### users (EXISTS — Stage 02)

No schema changes required. Existing structure supports all auth features.

```sql
CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password        VARCHAR(255) NOT NULL,
    role            VARCHAR(255) NOT NULL COMMENT 'customer, contractor, supervising_architect, field_engineer, admin',
    phone           VARCHAR(255) NULL,
    active          TINYINT(1) NOT NULL DEFAULT 1,
    remember_token  VARCHAR(100) NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    deleted_at      TIMESTAMP NULL,
    INDEX idx_users_role (role),
    INDEX idx_users_active (active),
    INDEX idx_users_email (email)
);
```

### personal_access_tokens (EXISTS — Stage 02)

Sanctum token storage. No changes needed.

```sql
CREATE TABLE personal_access_tokens (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type  VARCHAR(255) NOT NULL,
    tokenable_id    BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    token           VARCHAR(64) NOT NULL UNIQUE,
    abilities       TEXT NULL,
    last_used_at    TIMESTAMP NULL,
    expires_at      TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    INDEX idx_pat_tokenable (tokenable_type, tokenable_id)
);
```

### password_reset_tokens (NEW — This Stage)

Laravel password reset broker storage. Standard Laravel table.

```sql
CREATE TABLE password_reset_tokens (
    email       VARCHAR(255) NOT NULL PRIMARY KEY,
    token       VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NULL
);
```

**Migration:** `create_password_reset_tokens_table.php`

## Eloquent Relationships

### User Model (updates)

```
User
├── tokens()          → hasMany(PersonalAccessToken)  [via HasApiTokens]
├── projects()        → hasMany(Project)              [existing]
├── roles()           → belongsToMany(Role)           [existing]
└── notifications()   → morphMany(Notification)       [via Notifiable]
```

The `MustVerifyEmail` interface adds:

- `hasVerifiedEmail()` → checks `email_verified_at IS NOT NULL`
- `markEmailAsVerified()` → sets `email_verified_at` to now
- `sendEmailVerificationNotification()` → dispatches VerifyEmail notification

## Error Codes (Additions to ErrorCode Enum)

| Code                        | HTTP | Severity | Description                                        |
| --------------------------- | ---- | -------- | -------------------------------------------------- |
| AUTH_ACCOUNT_INACTIVE       | 403  | warning  | User account is deactivated                        |
| AUTH_PASSWORD_RESET_SENT    | 200  | info     | Password reset email sent (always returns success) |
| AUTH_EMAIL_ALREADY_VERIFIED | 422  | warning  | Email already verified                             |
| AUTH_INVALID_RESET_TOKEN    | 422  | warning  | Password reset token is invalid or expired         |
| AUTH_EMAIL_NOT_VERIFIED     | 422  | warning  | Email not yet verified (for future enforcement)    |

## API Response Shapes

### Login Success

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "...",
      "email": "...",
      "role": "customer",
      "phone": "...",
      "active": true,
      "email_verified_at": "...",
      "created_at": "...",
      "updated_at": "..."
    },
    "token": "1|abc123..."
  },
  "message": "تم تسجيل الدخول بنجاح",
  "errors": [],
  "error": null
}
```

### Login Failure

```json
{
  "success": false,
  "data": null,
  "message": null,
  "errors": [],
  "error": {
    "code": "AUTH_INVALID_CREDENTIALS",
    "message": "بيانات الاعتماد غير صحيحة",
    "details": { "email": ["بيانات الاعتماد غير صحيحة"] }
  }
}
```
