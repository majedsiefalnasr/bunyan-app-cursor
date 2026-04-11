<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ErrorDetailFiltering
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            return $response;
        }

        $data = $response->getData(true);
        if (! is_array($data) || ($data['success'] ?? true) !== false) {
            return $response;
        }

        $user = $request->user();
        $isAdmin = $user instanceof User && $user->role === UserRole::Admin;
        $allowDebug = $isAdmin && App::environment('local', 'testing');

        if ($allowDebug) {
            return $response;
        }

        if (isset($data['error']['details']) && is_array($data['error']['details']) && array_key_exists('_debug', $data['error']['details'])) {
            unset($data['error']['details']['_debug']);
        }

        $response->setData($data);

        return $response;
    }
}
