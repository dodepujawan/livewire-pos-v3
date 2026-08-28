<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\SystemRoute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Menu::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

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
            routeId: $routes['master.barang.list'] ?? null,
            icon: 'ti ti-package',
            sortOrder: 1
        );

        $this->createMenu(
            title: 'Cabang',
            parentId: $master->id,
            routeId: $routes['master.cabang.list'] ?? null,
            icon: 'ti ti-building',
            sortOrder: 2
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
            routeId: $routes['transaksi.penjualan.list'] ?? null,
            icon: 'ti ti-receipt',
            sortOrder: 1
        );

        $this->createMenu(
            title: 'Pembelian',
            parentId: $transaksi->id,
            routeId: $routes['transaksi.pembelian.list'] ?? null,
            icon: 'ti ti-truck',
            sortOrder: 2
        );

        $this->createMenu(
            title: 'Piutang',
            parentId: $transaksi->id,
            routeId: $routes['transaksi.piutang.list'] ?? null,
            icon: 'ti ti-user-check',
            sortOrder: 3
        );

        $this->createMenu(
            title: 'Hutang',
            parentId: $transaksi->id,
            routeId: $routes['transaksi.hutang.list'] ?? null,
            icon: 'ti ti-user-x',
            sortOrder: 4
        );

        // Laporan
        $laporan = $this->createMenu(
            title: 'Laporan',
            icon: 'ti ti-bar-chart',
            sortOrder: 4
        );

        $this->createMenu(
            title: 'Laporan Kas',
            parentId: $laporan->id,
            routeId: $routes['laporan.kas.list'] ?? null,
            icon: 'ti ti-wallet',
            sortOrder: 1
        );

        $this->createMenu(
            title: 'Laporan Penjualan',
            parentId: $laporan->id,
            routeId: $routes['laporan.penjualan.list'] ?? null,
            icon: 'ti ti-receipt',
            sortOrder: 2
        );

        $this->createMenu(
            title: 'Laporan Stok',
            parentId: $laporan->id,
            routeId: $routes['laporan.stok.list'] ?? null,
            icon: 'ti ti-box',
            sortOrder: 3
        );

        $this->createMenu(
            title: 'Laporan Buku Besar',
            parentId: $laporan->id,
            routeId: $routes['laporan.buku-besar.list'] ?? null,
            icon: 'ti ti-book',
            sortOrder: 4
        );

        $this->createMenu(
            title: 'Laba Rugi',
            parentId: $laporan->id,
            routeId: $routes['laporan.laba-rugi.list'] ?? null,
            icon: 'ti ti-chart-pie',
            sortOrder: 5
        );

        $this->createMenu(
            title: 'Neraca',
            parentId: $laporan->id,
            routeId: $routes['laporan.neraca.list'] ?? null,
            icon: 'ti ti-balance',
            sortOrder: 6
        );

        $this->createMenu(
            title: 'Arus Kas',
            parentId: $laporan->id,
            routeId: $routes['laporan.arus-kas.list'] ?? null,
            icon: 'ti ti-flow-chart',
            sortOrder: 7
        );

        // Sistem
        $sistem = $this->createMenu(
            title: 'Sistem',
            icon: 'ti ti-settings',
            sortOrder: 5
        );

        $this->createMenu(
            title: 'Pengaturan',
            parentId: $sistem->id,
            routeId: $routes['system.list'] ?? null,
            icon: 'ti ti-sliders',
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
