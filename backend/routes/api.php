<?php

use App\Http\Controllers\Api\ErrorHandlingTestController;
use App\Http\Controllers\Api\V1\ActivityLogController;
use App\Http\Controllers\Api\V1\Admin\BoqTemplateController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\ConversationMessageController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\EstimateController;
use App\Http\Controllers\Api\V1\EstimateItemController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\PendingApprovalController;
use App\Http\Controllers\Api\V1\PhaseController;
use App\Http\Controllers\Api\V1\PricingCalculationController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductPricingController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectDocumentController;
use App\Http\Controllers\Api\V1\ProjectEstimateCompareController;
use App\Http\Controllers\Api\V1\ProjectEstimateController;
use App\Http\Controllers\Api\V1\ProjectInvitationAcceptController;
use App\Http\Controllers\Api\V1\ProjectTaskController;
use App\Http\Controllers\Api\V1\ProjectTeamController;
use App\Http\Controllers\Api\V1\ProjectWorkflowController;
use App\Http\Controllers\Api\V1\QuotationOrderController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RfqController;
use App\Http\Controllers\Api\V1\RfqQuotationController;
use App\Http\Controllers\Api\V1\SupplierProfileController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TaskWorkspaceController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WorkflowDefinitionController;
use App\Http\Controllers\Api\V1\WorkflowInstanceActionController;
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

    Route::middleware(['throttle:30,1', 'payment.webhook'])->group(function () {
        Route::post('webhooks/payment', [PaymentWebhookController::class, 'handle'])->name('webhooks.payment');
    });

    // Protected Routes (Require Authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // User Profile Routes (all authenticated users)
        Route::get('auth/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('auth/profile', [UserController::class, 'update'])->name('profile.update');
        Route::post('auth/logout', [UserController::class, 'logout'])->name('logout');

        Route::middleware('throttle:60,1')->group(function () {
            Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
            Route::put('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
            Route::put('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
            Route::get('notification-preferences', [NotificationPreferenceController::class, 'show'])->name('notification-preferences.show');
            Route::put('notification-preferences', [NotificationPreferenceController::class, 'update'])->name('notification-preferences.update');
        });

        Route::middleware('role:contractor,admin')->group(function () {
            Route::post('suppliers', [SupplierProfileController::class, 'store'])->name('suppliers.store');
            Route::put('suppliers/{supplierProfile}', [SupplierProfileController::class, 'update'])->name('suppliers.update');
        });

        Route::middleware('role:admin')->group(function () {
            Route::put('suppliers/{supplierProfile}/verify', [SupplierProfileController::class, 'verify'])->name('suppliers.verify');
        });

        // RFQs & supplier quotations (طلبات التسعير وعروض الأسعار)
        Route::middleware('role:customer,contractor,admin')->group(function () {
            Route::get('rfqs', [RfqController::class, 'index'])->name('rfqs.index');
            Route::get('rfqs/{rfq}', [RfqController::class, 'show'])->name('rfqs.show');
            Route::get('rfqs/{rfq}/compare', [RfqController::class, 'compare'])->name('rfqs.compare');
            Route::get('rfqs/{rfq}/quotations', [RfqQuotationController::class, 'index'])->name('rfqs.quotations.index');
        });

        Route::middleware('role:customer')->group(function () {
            Route::post('rfqs', [RfqController::class, 'store'])->name('rfqs.store');
            Route::middleware('throttle:rfq-send')->post('rfqs/{rfq}/send', [RfqController::class, 'send'])->name('rfqs.send');
            Route::post('rfqs/{rfq}/evaluate', [RfqController::class, 'beginEvaluation'])->name('rfqs.evaluate');
            Route::post('rfqs/{rfq}/close', [RfqController::class, 'close'])->name('rfqs.close');
            Route::put('rfqs/{rfq}/quotations/{quotation}/accept', [RfqQuotationController::class, 'accept'])
                ->name('rfqs.quotations.accept')
                ->scopeBindings();
        });

        Route::middleware(['role:contractor', 'throttle:rfq-quote'])->group(function () {
            Route::post('rfqs/{rfq}/quotations', [RfqQuotationController::class, 'store'])->name('rfqs.quotations.store');
        });

        Route::post('auth/email/resend', [UserController::class, 'resendVerification'])
            ->middleware('throttle:1,1')
            ->name('verification.send');

        // Public-read resources (all authenticated roles)
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('products/{product}/pricing', [ProductPricingController::class, 'show'])->name('products.pricing.show');
        Route::post('pricing/calculate', PricingCalculationController::class)->name('pricing.calculate');

        Route::middleware(['role:admin,contractor', 'throttle:120,1'])->prefix('inventory')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
            Route::get('low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
            Route::put('{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
            Route::get('{product}/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        });

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
        Route::get('projects/{project}/timeline', [ProjectController::class, 'timeline'])->name('projects.timeline');
        Route::get('projects/{project}/team', [ProjectTeamController::class, 'index'])->name('projects.team.index');

        Route::middleware('throttle:30,1')->post('invitations/{token}/accept', [ProjectInvitationAcceptController::class, 'accept'])
            ->where('token', '[A-Za-z0-9]+')
            ->name('invitations.accept');

        Route::middleware('role:customer,contractor,supervising_architect,admin')->group(function () {
            Route::post('projects/{project}/team', [ProjectTeamController::class, 'store'])->name('projects.team.store');
            Route::put('projects/{project}/team/{user}', [ProjectTeamController::class, 'update'])->name('projects.team.update');
            Route::delete('projects/{project}/team/{user}', [ProjectTeamController::class, 'destroy'])->name('projects.team.destroy');
        });

        Route::middleware('throttle:120,1')->get('{entity}/{id}/activity', [ActivityLogController::class, 'forSubject'])
            ->whereNumber('id')
            ->whereIn('entity', ['projects', 'orders'])
            ->name('activity-log.for-subject');

        Route::middleware('role:customer,contractor,supervising_architect,admin')->group(function () {
            Route::put('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status');
        });

        // Phases & Tasks (read — all project-related roles)
        Route::middleware('role:customer,contractor,supervising_architect,field_engineer,admin')->group(function () {
            Route::middleware('throttle:60,1')->group(function () {
                Route::get('projects/{project}/documents', [ProjectDocumentController::class, 'index'])->name('projects.documents.index');
                Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
                Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
                Route::get('documents/{document}/versions', [DocumentController::class, 'versions'])->name('documents.versions.index');
            });
            Route::middleware('throttle:30,1')->post('projects/{project}/documents', [ProjectDocumentController::class, 'store'])->name('projects.documents.store');
            Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

            Route::get('projects/{project}/phases', [PhaseController::class, 'index'])->name('projects.phases.index');
            Route::get('projects/{project}/phases/{phase}', [PhaseController::class, 'show'])->name('projects.phases.show');
            Route::get('projects/{project}/phases/{phase}/tasks', [TaskController::class, 'index'])->name('projects.phases.tasks.index');
            Route::get('projects/{project}/phases/{phase}/tasks/{task}', [TaskController::class, 'show'])->name('projects.phases.tasks.show');
            Route::get('projects/{project}/tasks', [ProjectTaskController::class, 'index'])->name('projects.tasks.index');
            Route::get('tasks/{task}', [TaskWorkspaceController::class, 'show'])->name('tasks.show');

            Route::middleware('throttle:60,1')->group(function () {
                Route::get('projects/{project}/estimates', [ProjectEstimateController::class, 'index'])->name('projects.estimates.index');
                Route::get('projects/{project}/estimates/compare', [ProjectEstimateCompareController::class, 'compare'])->name('projects.estimates.compare');
                Route::get('estimates/{estimate}', [EstimateController::class, 'show'])->name('estimates.show');
                Route::get('estimates/{estimate}/export', [EstimateController::class, 'export'])->name('estimates.export');
            });
        });

        Route::middleware('role:customer,contractor,supervising_architect,admin')->group(function () {
            Route::middleware('throttle:30,1')->group(function () {
                Route::post('projects/{project}/estimates', [ProjectEstimateController::class, 'store'])->name('projects.estimates.store');
                Route::post('estimates/{estimate}/calculate', [EstimateController::class, 'calculate'])->name('estimates.calculate');
                Route::post('estimates/{estimate}/approve', [EstimateController::class, 'approve'])->name('estimates.approve');
                Route::post('estimates/{estimate}/reject', [EstimateController::class, 'reject'])->name('estimates.reject');
                Route::post('estimates/{estimate}/items', [EstimateItemController::class, 'store'])->name('estimates.items.store');
            });
            Route::put('estimates/{estimate}', [EstimateController::class, 'update'])->name('estimates.update');
            Route::scopeBindings()->group(function () {
                Route::put('estimates/{estimate}/items/{estimateItem}', [EstimateItemController::class, 'update'])->name('estimates.items.update');
                Route::delete('estimates/{estimate}/items/{estimateItem}', [EstimateItemController::class, 'destroy'])->name('estimates.items.destroy');
            });
        });

        // Reports (read — roles with report.view)
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');

        // Transactions (read-only for users)
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

        Route::middleware('throttle:60,1')->group(function () {
            Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
            Route::post('conversations', [ConversationController::class, 'store'])->name('conversations.store');
            Route::put('conversations/{conversation}/read', [ConversationController::class, 'markRead'])->name('conversations.read');
            Route::get('conversations/{conversation}/messages', [ConversationMessageController::class, 'index'])->name('conversations.messages.index');
            Route::post('conversations/{conversation}/messages', [ConversationMessageController::class, 'store'])->name('conversations.messages.store');
        });

        Route::middleware('throttle:30,1')->post('media/upload', [MediaController::class, 'store'])->name('media.upload');

        Route::middleware('throttle:60,1')->group(function () {
            Route::get('media', [MediaController::class, 'index'])->name('media.index');
            Route::get('media/{media}', [MediaController::class, 'show'])->name('media.show');
            Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
        });

        // Customer-specific routes
        Route::middleware('role:customer,admin')->group(function () {
            Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
        });

        Route::middleware(['role:customer,contractor,admin', 'throttle:60,1'])->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        });

        Route::middleware(['role:customer,contractor,admin', 'throttle:60,1'])->group(function () {
            Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
            Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        });

        Route::middleware(['role:customer,admin', 'throttle:30,1'])->group(function () {
            Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
            Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        });

        Route::middleware(['role:admin', 'throttle:30,1'])->group(function () {
            Route::put('invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');
        });

        Route::middleware(['role:customer,admin', 'throttle:30,1'])->group(function () {
            Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
            Route::put('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
            Route::put('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
            Route::post('quotations/{quotation}/to-order', [QuotationOrderController::class, 'store'])->name('quotations.to-order');

            Route::middleware('throttle:60,1')->prefix('payments')->group(function () {
                Route::post('initiate', [PaymentController::class, 'initiate'])->name('payments.initiate');
                Route::get('history', [PaymentController::class, 'history'])->name('payments.history');
                Route::get('{payment}', [PaymentController::class, 'show'])
                    ->whereNumber('payment')
                    ->name('payments.show');
                Route::post('{payment}/capture', [PaymentController::class, 'capture'])
                    ->whereNumber('payment')
                    ->name('payments.capture');
                Route::post('{payment}/refund', [PaymentController::class, 'refund'])
                    ->whereNumber('payment')
                    ->name('payments.refund');
            });
        });

        Route::middleware(['role:admin', 'throttle:60,1'])->group(function () {
            Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        });

        // Contractor-specific routes
        Route::middleware('role:contractor,admin')->group(function () {
            Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
            Route::post('projects/{project}/phases', [PhaseController::class, 'store'])->name('projects.phases.store');
            Route::put('projects/{project}/phases/{phase}', [PhaseController::class, 'update'])->name('projects.phases.update');
            Route::post('projects/{project}/phases/{phase}/tasks', [TaskController::class, 'store'])->name('projects.phases.tasks.store');
            Route::put('projects/{project}/phases/{phase}/tasks/{task}', [TaskController::class, 'update'])->name('projects.phases.tasks.update');
        });

        Route::middleware('role:contractor,supervising_architect,admin')->group(function () {
            Route::post('projects/{project}/tasks', [ProjectTaskController::class, 'store'])->name('projects.tasks.store');
            Route::put('tasks/{task}', [TaskWorkspaceController::class, 'update'])->name('tasks.update');
            Route::put('tasks/{task}/assign', [TaskWorkspaceController::class, 'assign'])->name('tasks.assign');
            Route::put('tasks/{task}/status', [TaskWorkspaceController::class, 'transitionStatus'])->name('tasks.status');
            Route::post('tasks/{task}/comments', [TaskWorkspaceController::class, 'storeComment'])->name('tasks.comments.store');
        });

        // Supervising Architect routes
        Route::middleware('role:supervising_architect,admin')->group(function () {
            Route::delete('projects/{project}/phases/{phase}', [PhaseController::class, 'destroy'])->name('projects.phases.destroy');
            Route::delete('projects/{project}/phases/{phase}/tasks/{task}', [TaskController::class, 'destroy'])->name('projects.phases.tasks.destroy');
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('workflows', [WorkflowDefinitionController::class, 'index'])->name('workflows.index');
            Route::post('workflows', [WorkflowDefinitionController::class, 'store'])->name('workflows.store');
            Route::get('workflows/{workflowConfiguration}', [WorkflowDefinitionController::class, 'show'])->name('workflows.show');
        });

        Route::middleware('role:customer,contractor,supervising_architect,admin')->group(function () {
            Route::post('projects/{project}/workflow/start', [ProjectWorkflowController::class, 'start'])->name('projects.workflow.start');
            Route::put('workflow-instances/{workflowInstance}/approve', [WorkflowInstanceActionController::class, 'approve'])->name('workflow-instances.approve');
            Route::put('workflow-instances/{workflowInstance}/reject', [WorkflowInstanceActionController::class, 'reject'])->name('workflow-instances.reject');
        });

        Route::get('approvals/pending', [PendingApprovalController::class, 'index'])->name('approvals.pending');

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
            Route::post('products/{product}/variants', [ProductController::class, 'storeVariant'])->name('admin.products.variants.store');
            Route::post('products/{product}/media', [ProductController::class, 'storeMedia'])->name('admin.products.media.store');
            Route::put('products/{product}/pricing', [ProductPricingController::class, 'sync'])->name('admin.products.pricing.sync');

            Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('admin.projects.destroy');
            Route::delete('reports/{report}', [ReportController::class, 'destroy'])->name('admin.reports.destroy');

            Route::get('suppliers', [SupplierProfileController::class, 'adminIndex'])->name('admin.suppliers.index');

            Route::get('activity-log', [ActivityLogController::class, 'adminIndex'])->name('admin.activity-log.index');

            Route::apiResource('boq-templates', BoqTemplateController::class)->names([
                'index' => 'admin.boq-templates.index',
                'store' => 'admin.boq-templates.store',
                'show' => 'admin.boq-templates.show',
                'update' => 'admin.boq-templates.update',
                'destroy' => 'admin.boq-templates.destroy',
            ]);
        });
    });

    if (app()->runningUnitTests()) {
        Route::get('__errors/{type}', [ErrorHandlingTestController::class, 'show']);
    }
});
