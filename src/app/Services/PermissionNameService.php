<?php

namespace App\Services;

class PermissionNameService
{
    /**
     * Route Action => Permission Action
     */
    private array $actionMap = [
        'list'      => 'view',
        'show'      => 'view',

        'create'    => 'create',
        'store'     => 'create',

        'edit'      => 'update',
        'update'    => 'update',

        'destroy'   => 'delete',
        'delete'    => 'delete',

        'print'     => 'print',
        'export'    => 'export',
        'import'    => 'import',
    ];

    public function fromRoute(string $routeName): string
    {
        $segments = explode('.', $routeName);
        if (count($segments) < 2) {
            return $routeName;
        }
        $action = array_pop($segments);
        $segments[] = $this->actionMap[$action] ?? $action;
        return implode('.', $segments);
    }
}
