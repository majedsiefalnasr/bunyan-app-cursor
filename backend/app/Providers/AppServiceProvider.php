<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPermissionGates();
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
    }
}
