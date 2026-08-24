<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\SystemRoute;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        Menu::query()->delete();

        $routes = SystemRoute::pluck('id', 'route_name');

        // Dashboard
        $this->createMenu(
            title: 'Dashboard',
            routeId: $routes['dashboard'] ?? null,
            icon: 'ti ti-home',
            sortOrder: 1
        );

        // Master
        $master = $this->createMenu(
            title: 'Master',
            icon: 'ti ti-database',
            sortOrder: 2
        );

        $this->createMenu(
            title: 'Barang',
            parentId: $master->id,
            routeId: $routes['barang-list'] ?? null,
            icon: 'ti ti-package',
            sortOrder: 1
        );

        // Transaksi
        $transaksi = $this->createMenu(
            title: 'Transaksi',
            icon: 'ti ti-shopping-cart',
            sortOrder: 3
        );

        $this->createMenu(
            title: 'Penjualan',
            parentId: $transaksi->id,
            routeId: $routes['transaksi-list'] ?? null,
            icon: 'ti ti-receipt',
            sortOrder: 1
        );
    }

    private function createMenu(
        string $title,
        ?int $routeId = null,
        ?int $parentId = null,
        ?string $icon = null,
        int $sortOrder = 0,
        bool $isSidebar = true,
        ?string $launcherGroup = null,
    ): Menu {

        return Menu::create([
            'parent_id'       => $parentId,
            'system_route_id' => $routeId,
            'title'           => $title,
            'icon'            => $icon,
            'sort_order'      => $sortOrder,
            'is_sidebar'      => $isSidebar,
            'launcher_group'  => $launcherGroup,
        ]);
    }
}
