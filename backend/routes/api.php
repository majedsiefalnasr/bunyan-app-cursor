<?php

use App\Http\Controllers\Api\V1\{
    UserController,
    ProjectController,
    PhaseController,
    TaskController,
    ReportController,
    TransactionController,
    ProductController,
    OrderController,
};
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Authentication Routes
    Route::post('auth/login', [UserController::class, 'login'])->name('login');
    Route::post('auth/register', [UserController::class, 'register'])->name('register');

    // Protected Routes (Require Authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // User Routes
        Route::get('auth/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('auth/profile', [UserController::class, 'update'])->name('profile.update');
        Route::post('auth/logout', [UserController::class, 'logout'])->name('logout');

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
});
