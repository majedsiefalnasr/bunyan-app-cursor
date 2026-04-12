# Quickstart — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z

## Quick Reference

### Backend Files to Modify

| File                                             | Action                                  |
| ------------------------------------------------ | --------------------------------------- |
| `app/Services/AuthService.php`                   | Enhance with all auth methods           |
| `app/Http/Controllers/Api/V1/UserController.php` | Refactor to thin controller             |
| `app/Http/Requests/Api/V1/RegisterRequest.php`   | Remove role, add password_confirmation  |
| `app/Http/Requests/Api/V1/LoginRequest.php`      | Fix password min to 8                   |
| `app/Http/Resources/Api/V1/UserResource.php`     | Add email_verified_at                   |
| `app/Enums/ErrorCode.php`                        | Add auth error codes                    |
| `app/Models/User.php`                            | Add MustVerifyEmail                     |
| `routes/api.php`                                 | Add password reset, verification routes |
| `resources/lang/ar/errors.php`                   | Add error translations                  |
| `resources/lang/en/errors.php`                   | Add error translations                  |

### Backend Files to Create

| File                                                           | Purpose                             |
| -------------------------------------------------------------- | ----------------------------------- |
| `app/Repositories/UserRepository.php`                          | User data access                    |
| `app/Http/Requests/Api/V1/ForgotPasswordRequest.php`           | Forgot password validation          |
| `app/Http/Requests/Api/V1/ResetPasswordRequest.php`            | Reset password validation           |
| `database/migrations/*_create_password_reset_tokens_table.php` | Password reset storage              |
| `config/sanctum.php`                                           | Token expiration config             |
| `resources/lang/ar/auth.php`                                   | Auth message translations (Arabic)  |
| `resources/lang/en/auth.php`                                   | Auth message translations (English) |
| `tests/Unit/Services/AuthServiceTest.php`                      | Service unit tests                  |
| `tests/Feature/Api/V1/AuthenticationTest.php`                  | API feature tests                   |

### Frontend Files to Modify

| File                     | Action                              |
| ------------------------ | ----------------------------------- |
| `types/auth.ts`          | Fix role enum, add fields           |
| `stores/auth.ts`         | Add API actions, cookie persistence |
| `composables/useAuth.ts` | Add login/register, fix redirects   |
| `composables/useApi.ts`  | Fix redirect paths                  |
| `pages/auth/login.vue`   | Full rewrite with Nuxt UI           |
| `layouts/auth.vue`       | Design system refinements           |

### Frontend Files to Create

| File                                 | Purpose                 |
| ------------------------------------ | ----------------------- |
| `pages/auth/register.vue`            | Registration page       |
| `pages/auth/forgot-password.vue`     | Forgot password page    |
| `pages/auth/reset-password.vue`      | Reset password page     |
| `pages/auth/verify-email.vue`        | Email verification page |
| `app.config.ts`                      | Nuxt UI color config    |
| `tests/unit/stores/auth.test.ts`     | Store tests             |
| `tests/unit/middleware/auth.test.ts` | Middleware tests        |

### Key Commands

```bash
# Backend
cd backend
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan make:migration create_password_reset_tokens_table
php artisan migrate
php artisan test

# Frontend
cd frontend
npm run lint
npm run typecheck
npm run test
```
