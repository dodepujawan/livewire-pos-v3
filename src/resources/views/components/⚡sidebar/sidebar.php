<?php

use App\Models\Menu;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\PermissionNameService;
use Illuminate\Support\Collection;

new class extends Component
{
    public array $openedMenus = [];
    public string $currentRoute = '';

    public function render()
    {
        $menus = Menu::query()
            ->with([
                'systemRoute',
                'children.systemRoute',
            ])
            ->whereNull('parent_id')
            ->where('is_sidebar', true)
            ->orderBy('sort_order')
            ->get();

        return $this->view([
            'menus' => $this->filterMenus($menus),
        ]);
    }

    public function toggleMenu(int $menuId): void
    {
        if (in_array($menuId, $this->openedMenus)) {
            $this->openedMenus = array_diff(
                $this->openedMenus,
                [$menuId]
            );
            return;
        }
        $this->openedMenus[] = $menuId;
    }

    public function isActive(Menu $menu): bool
    {
        return optional($menu->systemRoute)->route_name === $this->currentRoute;
    }

    public function hasActiveChild(Menu $menu): bool
    {
        foreach ($menu->children as $child) {
            if ($this->isActive($child)) {
                return true;
            }
        }
        return false;
    }

    public function autoExpandParent(): void
    {
        $menus = Menu::query()
            ->with(['children.systemRoute'])
            ->whereNull('parent_id')
            ->get();
        foreach ($menus as $menu) {
            if ($this->hasActiveChild($menu)) {
                $this->openedMenus[] = $menu->id;
            }
        }
    }

    protected function filterMenus(Collection $menus): Collection
    {
        $permissionName = app(PermissionNameService::class);

        return $menus
            ->map(function (Menu $menu) use ($permissionName) {

                $children = $menu->children
                    ->filter(function (Menu $child) use ($permissionName) {

                        if (! $child->systemRoute) {
                            return true;
                        }

                        $permission = $permissionName->fromRoute(
                            $child->systemRoute->route_name
                        );

                        return auth()->user()?->can($permission);
                    })
                    ->values();

                $menu->setRelation('children', $children);

                // Parent tetap tampil kalau masih punya child
                if ($children->isNotEmpty()) {
                    return $menu;
                }

                // Parent tanpa route disembunyikan jika tidak punya child
                if (! $menu->systemRoute) {
                    return null;
                }

                // ### Untuk Dashboard
                    if ($menu->systemRoute?->route_name === 'dashboard') {
                        return $menu;
                    }
                // ### End Of Untuk Dashboard
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

    public function mount(): void
    {
        $this->currentRoute = request()->route()?->getName() ?? '';
        $this->autoExpandParent();
    }

    #[On('refresh-sidebar')]
    public function refreshRoute(): void
    {
        $this->currentRoute = request()->route()?->getName() ?? '';
        $this->autoExpandParent();
    }
};
