<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return $this->sendError('بيانات دخول غير صحيحة', ['email' => ['بيانات اعتماد غير صحيحة']], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->sendSuccess([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'تم تسجيل الدخول بنجاح', 200);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role ?? 'customer',
            'phone' => $request->phone,
            'active' => true,
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->sendSuccess([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'تم إنشاء الحساب بنجاح', 201);
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->sendSuccess(
            new UserResource($request->user()),
            'تم جلب الملف الشخصي بنجاح',
            200
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->sendSuccess(
            new UserResource($user),
            'تم تحديث الملف الشخصي بنجاح',
            200
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->sendSuccess(null, 'تم تسجيل الخروج بنجاح', 200);
    }
}
