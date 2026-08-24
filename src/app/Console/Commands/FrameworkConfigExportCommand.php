<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\SystemRoute;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class FrameworkConfigExportCommand extends Command
{
    protected $signature = 'framework:config-export';

    protected $description = 'Export framework routes, permissions, and menus';

    public function handle(): int
    {
        $systemRoutes = SystemRoute::query()
            ->get()
            ->map(fn (SystemRoute $route) => [
                'route_name' => $route->route_name,
                'display_name' => $route->display_name,
            ])
            ->values()
            ->toArray();

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->values()
            ->toArray();

        $menus = Menu::query()
            ->with('systemRoute')
            ->orderBy('sort_order')
            ->get()
            ->map(function (Menu $menu) {
                $parent = $menu->parent()->with('systemRoute')->first();

                return [
                    'route' => $menu->systemRoute?->route_name,
                    'title' => $menu->title,
                    'icon' => $menu->icon,
                    'sort_order' => $menu->sort_order,
                    'is_sidebar' => $menu->is_sidebar,
                    'launcher_group' => $menu->launcher_group,
                    'parent_route' => $parent?->systemRoute?->route_name,
                    'parent_title' => $parent?->title,
                ];
            })
            ->values()
            ->toArray();

        $launcherGroups = \App\Models\LauncherGroup::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (\App\Models\LauncherGroup $group) => [
                'key' => $group->key,
                'label' => $group->label,
                'icon' => $group->icon,
                'sort_order' => $group->sort_order,
                'is_active' => $group->is_active,
            ])
            ->values()
            ->toArray();

        $data = [
            'version' => 1,
            'generated_at' => now()->toIso8601String(),
            'system_routes' => $systemRoutes,
            'permissions' => $permissions,
            'menus' => $menus,
            'launcher_groups' => $launcherGroups,
        ];

        $file = database_path('framework-data.json');
        file_put_contents(
            $file,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->newLine();
        $this->info('Framework configuration exported.');
        $this->line('File: database/framework-data.json');
        $this->line('Routes      : ' . count($systemRoutes));
        $this->line('Permissions : ' . count($permissions));
        $this->line('Menus       : ' . count($menus));

        return self::SUCCESS;
    }
}
