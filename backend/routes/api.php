<?php

use App\Http\Controllers\Api\ErrorHandlingTestController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PhaseController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SupplierProfileController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('health', HealthController::class)->name('health');

    Route::middleware('throttle:60,1')->group(function () {
        Route::get('suppliers', [SupplierProfileController::class, 'index'])->name('suppliers.index');
        Route::get('suppliers/{supplierProfile}', [SupplierProfileController::class, 'show'])->name('suppliers.show');
        Route::get('suppliers/{supplierProfile}/products', [SupplierProfileController::class, 'products'])->name('suppliers.products');
    });

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
        // User Profile Routes (all authenticated users)
        Route::get('auth/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('auth/profile', [UserController::class, 'update'])->name('profile.update');
        Route::post('auth/logout', [UserController::class, 'logout'])->name('logout');

        Route::middleware('role:contractor,admin')->group(function () {
            Route::post('suppliers', [SupplierProfileController::class, 'store'])->name('suppliers.store');
            Route::put('suppliers/{supplierProfile}', [SupplierProfileController::class, 'update'])->name('suppliers.update');
        });

        Route::middleware('role:admin')->group(function () {
            Route::put('suppliers/{supplierProfile}/verify', [SupplierProfileController::class, 'verify'])->name('suppliers.verify');
        });

        Route::post('auth/email/resend', [UserController::class, 'resendVerification'])
            ->middleware('throttle:1,1')
            ->name('verification.send');

        // Public-read resources (all authenticated roles)
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

        Route::middleware('role:admin')->group(function () {
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{category}/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // Projects (all roles can view)
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

        // Phases & Tasks (read — all project-related roles)
        Route::middleware('role:customer,contractor,supervising_architect,field_engineer,admin')->group(function () {
            Route::get('projects/{project}/phases', [PhaseController::class, 'index'])->name('projects.phases.index');
            Route::get('projects/{project}/phases/{phase}', [PhaseController::class, 'show'])->name('projects.phases.show');
            Route::get('projects/{project}/phases/{phase}/tasks', [TaskController::class, 'index'])->name('projects.phases.tasks.index');
            Route::get('projects/{project}/phases/{phase}/tasks/{task}', [TaskController::class, 'show'])->name('projects.phases.tasks.show');
        });

        // Reports (read — roles with report.view)
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');

        // Transactions (read-only for users)
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

        // Customer-specific routes
        Route::middleware('role:customer,admin')->group(function () {
            Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
            Route::apiResource('orders', OrderController::class)->names([
                'index' => 'orders.index',
                'store' => 'orders.store',
                'show' => 'orders.show',
                'update' => 'orders.update',
                'destroy' => 'orders.destroy',
            ]);
        });

        // Contractor-specific routes
        Route::middleware('role:contractor,admin')->group(function () {
            Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
            Route::post('projects/{project}/phases', [PhaseController::class, 'store'])->name('projects.phases.store');
            Route::put('projects/{project}/phases/{phase}', [PhaseController::class, 'update'])->name('projects.phases.update');
            Route::post('projects/{project}/phases/{phase}/tasks', [TaskController::class, 'store'])->name('projects.phases.tasks.store');
            Route::put('projects/{project}/phases/{phase}/tasks/{task}', [TaskController::class, 'update'])->name('projects.phases.tasks.update');
        });

        // Supervising Architect routes
        Route::middleware('role:supervising_architect,admin')->group(function () {
            Route::delete('projects/{project}/phases/{phase}', [PhaseController::class, 'destroy'])->name('projects.phases.destroy');
            Route::delete('projects/{project}/phases/{phase}/tasks/{task}', [TaskController::class, 'destroy'])->name('projects.phases.tasks.destroy');
        });

        // Field Engineer routes
        Route::middleware('role:field_engineer,admin')->group(function () {
            Route::post('reports', [ReportController::class, 'store'])->name('reports.store');
            Route::put('reports/{report}', [ReportController::class, 'update'])->name('reports.update');
        });

        // Admin-only routes
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            // Role management
            Route::get('roles', [RoleController::class, 'index'])->name('admin.roles.index');
            Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('admin.roles.permissions');
            Route::get('users', [RoleController::class, 'users'])->name('admin.users.index');
            Route::post('users/{user}/role', [RoleController::class, 'assignRole'])->name('admin.users.assign-role');
            Route::delete('users/{user}/role', [RoleController::class, 'removeRole'])->name('admin.users.remove-role');

            // Admin CRUD on resources
            Route::post('products', [ProductController::class, 'store'])->name('admin.products.store');
            Route::put('products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
            Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

            Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('admin.projects.destroy');
            Route::delete('reports/{report}', [ReportController::class, 'destroy'])->name('admin.reports.destroy');

            Route::get('suppliers', [SupplierProfileController::class, 'adminIndex'])->name('admin.suppliers.index');
        });
    });

    if (app()->runningUnitTests()) {
        Route::get('__errors/{type}', [ErrorHandlingTestController::class, 'show']);
    }
});
