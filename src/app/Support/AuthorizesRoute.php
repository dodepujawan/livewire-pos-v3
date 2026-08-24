<?php

namespace App\Support;

use App\Services\PermissionNameService;

trait AuthorizesRoute
{
    protected function authorizeRoute(): void
    {
        $routeName = request()->route()?->getName();

        if (blank($routeName)) {
            return;
        }

        $permission = app(PermissionNameService::class)
            ->fromRoute($routeName);

        abort_unless(
            auth()->user()?->can($permission),
            403
        );
    }
}
