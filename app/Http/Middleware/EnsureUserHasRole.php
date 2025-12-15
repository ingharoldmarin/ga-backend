<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:admin') or multiple: ->middleware('role:admin,coordinator')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        // If no roles passed, deny by default
        if (empty($roles)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $hasRole = $user->roles()->whereIn('name', $roles)->exists();
        if (!$hasRole) {
            abort(Response::HTTP_FORBIDDEN, 'No autorizado');
        }

        return $next($request);
    }
}

