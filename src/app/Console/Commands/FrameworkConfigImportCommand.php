<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;

class FrameworkConfigImportCommand extends Command
{
    protected $signature = 'framework:config-import';

    protected $description = 'Safely add missing framework configuration without replacing local customizations';

    public function handle(): int
    {
        $file = database_path('framework-data.json');

        if (! file_exists($file)) {
            $this->error('Framework configuration file not found.');
            return self::FAILURE;
        }

        $data = json_decode(
            file_get_contents($file),
            true
        );

        if (! is_array($data)) {
            $this->error('Invalid framework configuration file.');

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Route Sync
        |--------------------------------------------------------------------------
        */

        $this->call('framework:route-sync', ['--safe' => true]);

        /*
        |--------------------------------------------------------------------------
        | 2. Permission Sync
        |--------------------------------------------------------------------------
        */

        $this->call('framework:permission-sync', ['--safe' => true]);

        /*
        |--------------------------------------------------------------------------
        | 3. Launcher Group Sync
        |--------------------------------------------------------------------------
        */

        $launcherGroups = $data['launcher_groups'] ?? [];

        $launcherGroupsCreated = 0;
        $launcherGroupsPreserved = 0;

        foreach ($launcherGroups as $groupData) {
            $group = \App\Models\LauncherGroup::firstOrCreate(
                ['key' => $groupData['key']],
                [
                    'label' => $groupData['label'],
                    'icon' => $groupData['icon'] ?? null,
                    'sort_order' => $groupData['sort_order'] ?? 0,
                    'is_active' => $groupData['is_active'] ?? true,
                ]
            );

            if ($group->wasRecentlyCreated) {
                $launcherGroupsCreated++;
            } else {
                $launcherGroupsPreserved++;
            }
        }

        $this->line("Launcher Groups Created : {$launcherGroupsCreated}");
        $this->line("Launcher Groups Preserved : {$launcherGroupsPreserved}");

        /*
        |--------------------------------------------------------------------------
        | 3. Menu Sync
        |--------------------------------------------------------------------------
        */

        $created = 0;
        $skipped = 0;
        $conflicts = 0;
        $menus = $data['menus'] ?? [];
        /*
        |--------------------------------------------------------------------------
        | PASS 1
        | Create root menus first
        |--------------------------------------------------------------------------
        */
        foreach ($menus as $menuData) {
            // Hanya root menu
            if (! empty($menuData['parent_route']) ||
                ! empty($menuData['parent_title'])) {
                continue;
            }

            $menu = null;

            if (! empty($menuData['route'])) {
                $menu = Menu::whereHas(
                    'systemRoute',
                    fn ($query) => $query->where(
                        'route_name',
                        $menuData['route']
                    )
                )->first();
            }

            if ($menu && $menu->parent_id !== null) {
                $this->warn(
                    "Route conflict for root menu '{$menuData['title']}': route '{$menuData['route']}' is already assigned to a submenu. Existing menu was preserved."
                );
                $conflicts++;
                $skipped++;
                continue;
            }

            if (! $menu) {
                // Fall back to the title so a server-specific route is preserved.
                $menu = Menu::whereNull('parent_id')
                    ->where('title', $menuData['title'])
                    ->first();
            }

            if ($menu) {
                $existingRoute = $menu->systemRoute?->route_name;
                $sourceRoute = $menuData['route'] ?? null;

                if ($sourceRoute !== $existingRoute && ($sourceRoute || $existingRoute)) {
                    $this->warn(
                        "Route conflict for root menu '{$menuData['title']}': keeping server route '{$existingRoute}' instead of '{$sourceRoute}'."
                    );
                    $conflicts++;
                }

                if (filled($menuData['sidebar_heading'] ?? null) && blank($menu->sidebar_heading)) {
                    $menu->update([
                        'sidebar_heading' => $menuData['sidebar_heading'],
                    ]);
                }

                $skipped++;
                continue;
            }

            $systemRouteId = null;

            if (! empty($menuData['route'])) {
                $systemRouteId = \App\Models\SystemRoute::where(
                    'route_name',
                    $menuData['route']
                )->value('id');

                if (! $systemRouteId) {
                    $this->warn(
                        "Route '{$menuData['route']}' was not found for root menu '{$menuData['title']}'. Menu was not created."
                    );
                    $skipped++;
                    continue;
                }
            }

            Menu::create([
                'parent_id' => null,
                'system_route_id' => $systemRouteId,
                'title' => $menuData['title'],
                'sidebar_heading' => $menuData['sidebar_heading'] ?? null,
                'icon' => $menuData['icon'],
                'sort_order' => $menuData['sort_order'] ?? 0,
                'is_sidebar' => $menuData['is_sidebar'] ?? true,
                'launcher_group' => $menuData['launcher_group'] ?? null,
            ]);

            $created++;
        }
        /*
        |--------------------------------------------------------------------------
        | PASS 2
        | Create child menus
        |--------------------------------------------------------------------------
        */

        foreach ($menus as $menuData) {

            if (
                empty($menuData['parent_route']) &&
                empty($menuData['parent_title'])
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve parent
            |--------------------------------------------------------------------------
            */

            $parent = null;

            if (! empty($menuData['parent_route'])) {
                $parent = Menu::query()
                    ->whereNull('parent_id')
                    ->whereHas(
                        'systemRoute',
                        fn ($query) => $query->where(
                            'route_name',
                            $menuData['parent_route']
                        )
                    )
                    ->first();
            }

            if (! $parent && ! empty($menuData['parent_title'])) {
                $parent = Menu::whereNull('parent_id')
                    ->where('title', $menuData['parent_title'])
                    ->first();
            }

            /*
            |--------------------------------------------------------------------------
            | Safety
            |--------------------------------------------------------------------------
            */

            if (! $parent) {
                $this->warn(
                    "Parent not found for menu '{$menuData['title']}'. Menu was not created."
                );

                $conflicts++;
                $skipped++;
                continue;
            }

            $menu = null;

            if (! empty($menuData['route'])) {
                $menu = Menu::whereHas(
                    'systemRoute',
                    fn ($query) => $query->where(
                        'route_name',
                        $menuData['route']
                    )
                )->first();
            }

            if ($menu) {
                if ($menu->parent_id !== $parent->id) {
                    $this->warn(
                        "Menu conflict for '{$menuData['title']}': its route is already assigned under another parent. Existing menu was preserved."
                    );
                    $conflicts++;
                }

                $skipped++;
                continue;
            }

            // Match by parent and title to avoid duplicating unrouted or locally rerouted children.
            $menu = Menu::query()
                ->where('parent_id', $parent->id)
                ->where('title', $menuData['title'])
                ->first();

            if ($menu) {
                $existingRoute = $menu->systemRoute?->route_name;
                $sourceRoute = $menuData['route'] ?? null;

                if ($sourceRoute !== $existingRoute && ($sourceRoute || $existingRoute)) {
                    $this->warn(
                        "Route conflict for child menu '{$menuData['title']}': keeping the existing route instead of '{$sourceRoute}'."
                    );
                    $conflicts++;
                }

                $skipped++;
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve route
            |--------------------------------------------------------------------------
            */

            $systemRouteId = null;

            if (! empty($menuData['route'])) {
                $systemRouteId = \App\Models\SystemRoute::where(
                    'route_name',
                    $menuData['route']
                )->value('id');

                if (! $systemRouteId) {
                    $this->warn(
                        "Route '{$menuData['route']}' was not found for child menu '{$menuData['title']}'. Menu was not created."
                    );
                    $skipped++;
                    continue;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Create child
            |--------------------------------------------------------------------------
            */

            Menu::create([
                'parent_id' => $parent->id,
                'system_route_id' => $systemRouteId,
                'title' => $menuData['title'],
                'icon' => $menuData['icon'],
                'sort_order' => $menuData['sort_order'] ?? 0,
                'is_sidebar' => $menuData['is_sidebar'] ?? true,
                'launcher_group' => $menuData['launcher_group'] ?? null,
            ]);

            $created++;
        }
        $this->newLine();
        $this->info('Framework configuration imported.');
        $this->line("Menus Created : {$created}");
        $this->line("Menus Skipped : {$skipped}");
        $this->line("Conflicts Preserved : {$conflicts}");

        return self::SUCCESS;
    }
}
