<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class FrameworkConfigImportCommand extends Command
{
    protected $signature = 'framework:config-import';

    protected $description = 'Import framework routes, permissions, and new menus';

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

        $this->call('framework:route-sync');

        /*
        |--------------------------------------------------------------------------
        | 2. Permission Sync
        |--------------------------------------------------------------------------
        */

        $this->call('framework:permission-sync');

        /*
        |--------------------------------------------------------------------------
        | 3. Launcher Group Sync
        |--------------------------------------------------------------------------
        */

        $launcherGroups = $data['launcher_groups'] ?? [];

        foreach ($launcherGroups as $groupData) {
            \App\Models\LauncherGroup::updateOrCreate(
                ['key' => $groupData['key']],
                [
                    'label' => $groupData['label'],
                    'icon' => $groupData['icon'] ?? null,
                    'sort_order' => $groupData['sort_order'] ?? 0,
                    'is_active' => $groupData['is_active'] ?? true,
                ]
            );
        }

        $this->line('Launcher Groups Synced : ' . count($launcherGroups));

        /*
        |--------------------------------------------------------------------------
        | 3. Menu Sync
        |--------------------------------------------------------------------------
        */

        $created = 0;
        $skipped = 0;
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
            } else {
                // Root menu tanpa route
                $menu = Menu::whereNull('parent_id')
                    ->where('title', $menuData['title'])
                    ->first();
            }

            if ($menu) {
                $skipped++;
                continue;
            }

            $systemRouteId = null;

            if (! empty($menuData['route'])) {
                $systemRouteId = \App\Models\SystemRoute::where(
                    'route_name',
                    $menuData['route']
                )->value('id');
            }

            Menu::create([
                'parent_id' => null,
                'system_route_id' => $systemRouteId,
                'title' => $menuData['title'],
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
            | Check existing menu
            |--------------------------------------------------------------------------
            */

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
                $skipped++;
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve parent
            |--------------------------------------------------------------------------
            */

            $parent = null;

            if (! empty($menuData['parent_route'])) {

                $parent = Menu::whereHas(
                    'systemRoute',
                    fn ($query) => $query->where(
                        'route_name',
                        $menuData['parent_route']
                    )
                )->first();

            } elseif (! empty($menuData['parent_title'])) {

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
                    "Parent not found for menu: {$menuData['title']}"
                );

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

        return self::SUCCESS;
    }
}
