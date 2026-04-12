<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            throw new AuthorizationException(
                __('errors.codes.AUTH_UNAUTHORIZED.message')
            );
        }

        foreach ($permissions as $permission) {
            if (! Gate::forUser($user)->check($permission)) {
                throw new AuthorizationException(
                    __('errors.codes.RBAC_PERMISSION_DENIED.message')
                );
            }
        }

        return $next($request);
    }
}
