<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password as PasswordFacade;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @return array{status: string, user?: User, token?: string}
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            Log::warning('Login failed: invalid credentials', [
                'action' => 'auth.login_failed',
                'email' => $email,
            ]);

            return ['status' => 'invalid_credentials'];
        }

        if (! $user->active) {
            Log::warning('Login failed: account inactive', [
                'action' => 'auth.login_inactive',
                'user_id' => $user->id,
            ]);

            return ['status' => 'account_inactive'];
        }

        $token = $user->createToken('api_token')->plainTextToken;

        Log::info('User logged in', [
            'action' => 'auth.login',
            'user_id' => $user->id,
        ]);

        return [
            'status' => 'success',
            'user' => $user,
            'token' => $token,
        ];
    }

    public function register(array $data): array
    {
        /** @var User $user */
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
            'phone' => $data['phone'] ?? null,
            'active' => true,
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        event(new Registered($user));

        Log::info('User registered', [
            'action' => 'auth.register',
            'user_id' => $user->id,
        ]);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();

        Log::info('User logged out', [
            'action' => 'auth.logout',
            'user_id' => $user->id,
        ]);
    }

    public function refreshToken(User $user): string
    {
        $user->tokens()->delete();

        return $user->createToken('api_token')->plainTextToken;
    }

    public function validateToken(string $token): ?User
    {
        return auth('sanctum')->check() ? auth()->user() : null;
    }

    public function forgotPassword(string $email): void
    {
        $status = PasswordFacade::sendResetLink(['email' => $email]);

        Log::info('Password reset requested', [
            'email' => $email,
            'status' => $status,
            'action' => 'auth.forgot_password',
        ]);
    }

    public function resetPassword(array $data): string
    {
        $status = PasswordFacade::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete();

                Log::info('Password reset completed', [
                    'user_id' => $user->id,
                    'action' => 'auth.password_reset',
                ]);
            }
        );

        return $status;
    }

    public function verifyEmail(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->markEmailAsVerified();

        Log::info('Email verified', [
            'user_id' => $user->id,
            'action' => 'auth.email_verified',
        ]);

        return true;
    }

    public function resendVerification(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();

        Log::info('Verification email resent', [
            'user_id' => $user->id,
            'action' => 'auth.verification_resent',
        ]);

        return true;
    }
}
