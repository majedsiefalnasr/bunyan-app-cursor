<?php

namespace App\Providers;

use App\Contracts\Payments\PaymentGatewayContract;
use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\User;
use App\Services\Payments\Gateways\SandboxPaymentGateway;
use App\Services\RoleService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayContract::class, SandboxPaymentGateway::class);
    }

    public function boot(): void
    {
        $this->registerPermissionGates();
        $this->registerRouteModelBindings();
        $this->registerRateLimiters();
    }

    private function registerRouteModelBindings(): void
    {
        Route::bind('conversation', function (string $value) {
            $user = auth()->user();
            if ($user === null) {
                abort(401);
            }

            return Conversation::query()
                ->whereKey($value)
                ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
                ->firstOrFail();
        });
    }

    private function registerPermissionGates(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->role === UserRole::Admin) {
                return true;
            }

            $userPermissions = app(RoleService::class)->getUserPermissions($user);

            if (in_array($ability, $userPermissions, true)) {
                return true;
            }

            return null;
        });

        Gate::define('viewAnalytics', fn (User $user) => $user->role === UserRole::SupervisingArchitect);
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('rfq-send', function ($request) {
            $userId = (string) ($request->user()?->id ?? 'guest');
            $rfqParam = $request->route('rfq');
            $rfqId = is_object($rfqParam) ? (string) $rfqParam->id : (string) ($rfqParam ?? '0');

            return Limit::perMinute(5)->by("rfq-send:{$userId}:{$rfqId}");
        });

        RateLimiter::for('rfq-quote', function ($request) {
            $userId = (string) ($request->user()?->id ?? 'guest');
            $rfqParam = $request->route('rfq');
            $rfqId = is_object($rfqParam) ? (string) $rfqParam->id : (string) ($rfqParam ?? '0');

            return Limit::perMinute(10)->by("rfq-quote:{$userId}:{$rfqId}");
        });
    }
}
