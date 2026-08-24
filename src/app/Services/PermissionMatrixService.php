<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;

class PermissionMatrixService
{
    public static function build(): array
    {
        $matrix = [];
        $actions = [];

        foreach (Permission::orderBy('name')->get() as $permission) {

            $segments = explode('.', $permission->name);
            if (count($segments) < 2) {
                continue;
            }
            $action = array_pop($segments);
            $resource = implode('.', $segments);

            if (in_array($resource, self::$ignoredResources, true)) {
                continue;
            }

            $actions[$action] = $action;

            $label = ucwords(str_replace('.', ' ', $resource));

            if (! isset($matrix[$resource])) {
                $matrix[$resource] = [
                    'resource' => $resource,
                    'label' => $label,
                    'actions' => [],
                ];
            }

            $matrix[$resource]['actions'][$action] = $permission->name;
        }

        $ordered = [
            'view',
            'create',
            'update',
            'delete',
            'print',
            'export',
            'import',
        ];
        $actions = array_values(array_filter(
            $ordered,
            fn ($action) => isset($actions[$action])
        ));

        return [
            'resources' => array_values($matrix),
            'actions'   => $actions,
        ];
    }

    protected static array $ignoredResources = [
        'login',
        'logout',
        'dashboard',
        'register',
        'password',
        'storage',
        'storage.local',
        'default-livewire',
        'auth.permission',
        // 'auth.register',
    ];
}
