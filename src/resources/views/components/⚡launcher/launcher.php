<?php

use App\Models\LauncherGroup;
use App\Models\Menu;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $activeGroups = LauncherGroup::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->pluck('key');

        $menus = Menu::query()
            ->with(['systemRoute'])
            ->whereNotNull('launcher_group')
            ->whereIn('launcher_group', $activeGroups)
            ->orderBy('sort_order')
            ->get();

        $filtered = $this->filterMenus($menus);
        $grouped = $filtered->groupBy('launcher_group');

        $orderedGroupedMenus = collect();
        foreach ($activeGroups as $group) {
            if ($grouped->has($group)) {
                $orderedGroupedMenus->put($group, $grouped->get($group));
            }
        }

        return $this->view([
            'groupedMenus' => $orderedGroupedMenus,
        ]);
    }

    public function isActive(Menu $menu): bool
    {
        return optional($menu->systemRoute)->route_name === request()->route()?->getName();
    }

    protected function filterMenus(Collection $menus): Collection
    {
        $permissionName = app(\App\Services\PermissionNameService::class);

        return $menus
            ->map(function (Menu $menu) use ($permissionName) {
                if (! $menu->systemRoute) {
                    return $menu;
                }

                $permission = $permissionName->fromRoute(
                    $menu->systemRoute->route_name
                );

                return auth()->user()?->can($permission)
                    ? $menu
                    : null;
            })
            ->filter()
            ->values();
    }
};
