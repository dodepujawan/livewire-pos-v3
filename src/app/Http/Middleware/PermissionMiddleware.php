<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PermissionNameService;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (blank($routeName)) {
            return $next($request);
        }

        $permission = app(PermissionNameService::class)
            ->fromRoute($routeName);

        if (! auth()->user()?->can($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
