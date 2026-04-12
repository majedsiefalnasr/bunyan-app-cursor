<?php

use App\Http\Controllers\Api\ErrorHandlingTestController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PhaseController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Authentication Routes
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('auth/login', [UserController::class, 'login'])->name('login');
        Route::post('auth/register', [UserController::class, 'register'])->name('register');
    });

    // Password Reset Routes (Public)
    Route::middleware('throttle:3,1')->group(function () {
        Route::post('auth/forgot-password', [UserController::class, 'forgotPassword'])->name('password.email');
        Route::post('auth/reset-password', [UserController::class, 'resetPassword'])->name('password.reset');
    });

    // Signed link from email; must not require Sanctum (user is not authenticated in the mail client).
    Route::middleware(['throttle:6,1', 'signed'])->group(function () {
        Route::get('auth/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
            ->name('verification.verify');
    });

    // Protected Routes (Require Authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // User Routes
        Route::get('auth/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('auth/profile', [UserController::class, 'update'])->name('profile.update');
        Route::post('auth/logout', [UserController::class, 'logout'])->name('logout');

        // Email Verification Routes
        Route::post('auth/email/resend', [UserController::class, 'resendVerification'])
            ->middleware('throttle:1,1')
            ->name('verification.send');

        // Project Routes
        Route::apiResource('projects', ProjectController::class);

        // Phase Routes (nested under projects)
        Route::apiResource('projects.phases', PhaseController::class);

        // Task Routes (nested under projects.phases)
        Route::apiResource('projects.phases.tasks', TaskController::class);

        // Report Routes
        Route::apiResource('reports', ReportController::class);

        // Transaction Routes (read-only for users)
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

        // Product Routes
        Route::apiResource('products', ProductController::class);

        // Order Routes
        Route::apiResource('orders', OrderController::class);
    });

    if (app()->runningUnitTests()) {
        Route::get('__errors/{type}', [ErrorHandlingTestController::class, 'show']);
    }
});
