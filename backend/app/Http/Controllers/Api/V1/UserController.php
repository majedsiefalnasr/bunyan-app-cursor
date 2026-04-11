<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UserController extends BaseController
{
    public function __construct(
        private AuthService $authService,
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->email, $request->password);

        if ($result['status'] === 'invalid_credentials') {
            return $this->sendError(
                ErrorCode::AUTH_INVALID_CREDENTIALS->value,
                __('errors.codes.'.ErrorCode::AUTH_INVALID_CREDENTIALS->value.'.message'),
                ['email' => [__('auth.failed')]],
                ErrorCode::AUTH_INVALID_CREDENTIALS->httpStatus(),
            );
        }

        if ($result['status'] === 'account_inactive') {
            return $this->sendError(
                ErrorCode::AUTH_ACCOUNT_INACTIVE->value,
                __('errors.codes.'.ErrorCode::AUTH_ACCOUNT_INACTIVE->value.'.message'),
                null,
                ErrorCode::AUTH_ACCOUNT_INACTIVE->httpStatus(),
            );
        }

        return $this->sendSuccess([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], __('auth.login_success'));
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->sendSuccess([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], __('auth.register_success'), 201);
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->sendSuccess(
            new UserResource($request->user()),
            __('auth.profile_fetched'),
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->sendSuccess(
            new UserResource($user->fresh()),
            __('auth.profile_updated'),
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->sendSuccess(null, __('auth.logout_success'));
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->email);

        return $this->sendSuccess(null, __('auth.password_reset_sent'), 200);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword(
            $request->only('email', 'token', 'password', 'password_confirmation')
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->sendSuccess(null, __('auth.password_reset_success'), 200);
        }

        return $this->sendError(
            ErrorCode::AUTH_INVALID_RESET_TOKEN->value,
            __('errors.codes.'.ErrorCode::AUTH_INVALID_RESET_TOKEN->value.'.message', [], app()->getLocale()),
            null,
            ErrorCode::AUTH_INVALID_RESET_TOKEN->httpStatus(),
        );
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->route('id'));

        if (! hash_equals((string) $user->getKey(), (string) $request->route('id'))) {
            return $this->sendError(
                ErrorCode::AUTH_UNAUTHORIZED->value,
                __('errors.codes.'.ErrorCode::AUTH_UNAUTHORIZED->value.'.message', [], app()->getLocale()),
                null,
                ErrorCode::AUTH_UNAUTHORIZED->httpStatus(),
            );
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            return $this->sendError(
                ErrorCode::AUTH_UNAUTHORIZED->value,
                __('errors.codes.'.ErrorCode::AUTH_UNAUTHORIZED->value.'.message', [], app()->getLocale()),
                null,
                ErrorCode::AUTH_UNAUTHORIZED->httpStatus(),
            );
        }

        $verified = $this->authService->verifyEmail($user);

        if (! $verified) {
            return $this->sendError(
                ErrorCode::AUTH_EMAIL_ALREADY_VERIFIED->value,
                __('errors.codes.'.ErrorCode::AUTH_EMAIL_ALREADY_VERIFIED->value.'.message', [], app()->getLocale()),
                null,
                ErrorCode::AUTH_EMAIL_ALREADY_VERIFIED->httpStatus(),
            );
        }

        return $this->sendSuccess(null, __('auth.email_verified'), 200);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $resent = $this->authService->resendVerification($request->user());

        if (! $resent) {
            return $this->sendError(
                ErrorCode::AUTH_EMAIL_ALREADY_VERIFIED->value,
                __('errors.codes.'.ErrorCode::AUTH_EMAIL_ALREADY_VERIFIED->value.'.message', [], app()->getLocale()),
                null,
                ErrorCode::AUTH_EMAIL_ALREADY_VERIFIED->httpStatus(),
            );
        }

        return $this->sendSuccess(null, __('auth.verification_sent'), 200);
    }
}
