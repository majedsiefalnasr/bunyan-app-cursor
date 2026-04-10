<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return [
            'user' => $user,
            'token' => $user->createToken('api_token')->plainTextToken,
        ];
    }

    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'customer',
            'phone' => $data['phone'] ?? null,
            'active' => true,
        ]);

        return [
            'user' => $user,
            'token' => $user->createToken('api_token')->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
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
}
