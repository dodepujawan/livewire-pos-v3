<?php
/**
 * RESTORE DATA - livewire_pos_v3
 * Jalankan: php restore_data.php
 * Akan create: cabang PUSAT, roles, permissions, admin user, menus, launcher groups
 */
require __DIR__ . '/src/vendor/autoload.php';
$app = require __DIR__ . '/src/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RESTORING DATA ===" . PHP_EOL;

// 1. Cabang
$cabang = App\Models\Cabang::firstOrCreate(
    ['kode_cabang' => 'PUSAT'],
    ['nama_cabang' => 'Cabang Pusat', 'is_aktif' => true]
);
echo '[OK] Cabang: ' . $cabang->kode_cabang . PHP_EOL;

// 2. Roles
$adminRole = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
$kasirRole = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'kasir']);
$superRole = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
$superRole->syncPermissions(Spatie\Permission\Models\Permission::all());
echo '[OK] Roles: admin, kasir, Super Admin (' . $superRole->permissions()->count() . ' perms)' . PHP_EOL;

// 3. User admin
$user = App\Models\User::firstOrCreate(
    ['email' => 'dwebpro@gmail.com'],
    ['name' => 'Dwebpro', 'password' => bcrypt('admin123'), 'cabang_id' => $cabang->id]
);
$user->syncRoles(['Super Admin']);
echo '[OK] User: ' . $user->email . ' (password: admin123)' . PHP_EOL;

// 4. Launcher groups
$groups = [
    ['key' => 'transaksi', 'label' => 'Transaksi', 'icon' => 'ti ti-receipt', 'sort_order' => 1],
    ['key' => 'master_data', 'label' => 'Master Data', 'icon' => 'ti ti-database', 'sort_order' => 2],
    ['key' => 'laporan', 'label' => 'Laporan', 'icon' => 'ti ti-chart-bar', 'sort_order' => 3],
    ['key' => 'sistem', 'label' => 'Sistem', 'icon' => 'ti ti-settings', 'sort_order' => 4],
];
foreach ($groups as $g) {
    App\Models\LauncherGroup::updateOrCreate(['key' => $g['key']], $g);
}
echo '[OK] Launcher groups: ' . count($groups) . PHP_EOL;

// 5. Menus
$routes = App\Models\SystemRoute::pluck('id', 'route_name');
App\Models\Menu::query()->delete();
$dashboard = App\Models\Menu::create(['system_route_id' => $routes['dashboard'] ?? null, 'title' => 'Dashboard', 'icon' => 'ti ti-home', 'sort_order' => 1]);
$master = App\Models\Menu::create(['title' => 'Master', 'icon' => 'ti ti-database', 'sort_order' => 2]);
App\Models\Menu::create(['parent_id' => $master->id, 'system_route_id' => $routes['barang-list'] ?? null, 'title' => 'Barang', 'icon' => 'ti ti-package', 'sort_order' => 1, 'launcher_group' => 'master_data']);
App\Models\Menu::create(['parent_id' => $master->id, 'system_route_id' => $routes['master.cabang.list'] ?? null, 'title' => 'Cabang', 'icon' => 'ti ti-building-store', 'sort_order' => 2, 'launcher_group' => 'master_data']);
$transaksi = App\Models\Menu::create(['title' => 'Transaksi', 'icon' => 'ti ti-shopping-cart', 'sort_order' => 3]);
App\Models\Menu::create(['parent_id' => $transaksi->id, 'system_route_id' => $routes['transaksi.penjualan.list'] ?? null, 'title' => 'Penjualan', 'icon' => 'ti ti-receipt', 'sort_order' => 1, 'launcher_group' => 'transaksi']);
$sistem = App\Models\Menu::create(['title' => 'Sistem', 'icon' => 'ti ti-settings', 'sort_order' => 4]);
App\Models\Menu::create(['parent_id' => $sistem->id, 'system_route_id' => $routes['system.list'] ?? null, 'title' => 'System', 'icon' => 'ti ti-user-cog', 'sort_order' => 1, 'launcher_group' => 'sistem']);
App\Models\Menu::create(['parent_id' => $sistem->id, 'system_route_id' => $routes['master.menu.list'] ?? null, 'title' => 'Menu', 'icon' => 'ti ti-menu-2', 'sort_order' => 2, 'launcher_group' => 'sistem']);
echo '[OK] Menus: created' . PHP_EOL;

echo PHP_EOL . "=== RESTORE COMPLETE ===" . PHP_EOL;
echo "Login: dwebpro@gmail.com / admin123" . PHP_EOL;
