-- ============================================
-- RESTORE DATA - livewire_pos_v3
-- Generated: 2026-08-24
-- Jalankan di MySQL Windows (database: laravel_pos_true)
-- Cara: mysql -u wsluser -p laravel_pos_true < restore_data.sql
--     atau copy-paste ke HeidiSQL/Workbench
-- ============================================

-- Launcher Groups
INSERT INTO launcher_groups (`key`, `label`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('transaksi', 'Transaksi', 'ti ti-receipt', 1, 1, NOW(), NOW()),
('master_data', 'Master Data', 'ti ti-database', 2, 1, NOW(), NOW()),
('laporan', 'Laporan', 'ti ti-chart-bar', 3, 1, NOW(), NOW()),
('sistem', 'Sistem', 'ti ti-settings', 4, 1, NOW(), NOW());

-- System Routes
INSERT INTO system_routes (`route_name`, `display_name`, `created_at`, `updated_at`) VALUES
('default-livewire.update', 'Default Livewire Update', NOW(), NOW()),
('login', 'Login', NOW(), NOW()),
('login.process', 'Login Process', NOW(), NOW()),
('logout', 'Logout', NOW(), NOW()),
('dashboard', 'Dashboard', NOW(), NOW()),
('auth.register.create', 'Auth Register Create', NOW(), NOW()),
('auth.register.list', 'Auth Register List', NOW(), NOW()),
('auth.register.edit', 'Auth Register Edit', NOW(), NOW()),
('auth.permission.matrix', 'Auth Permission Matrix', NOW(), NOW()),
('master.barang.list', 'Master Barang List', NOW(), NOW()),
('master.barang.create', 'Master Barang Create', NOW(), NOW()),
('master.barang.edit', 'Master Barang Edit', NOW(), NOW()),
('master.cabang.list', 'Master Cabang List', NOW(), NOW()),
('master.cabang.create', 'Master Cabang Create', NOW(), NOW()),
('master.cabang.edit', 'Master Cabang Edit', NOW(), NOW()),
('transaksi.penjualan.list', 'Transaksi Penjualan List', NOW(), NOW()),
('transaksi.penjualan.create', 'Transaksi Penjualan Create', NOW(), NOW()),
('transaksi.penjualan.show', 'Transaksi Penjualan Show', NOW(), NOW()),
('transaksi.penjualan.edit', 'Transaksi Penjualan Edit', NOW(), NOW()),
('master.menu.list', 'Master Menu List', NOW(), NOW()),
('master.menu.create', 'Master Menu Create', NOW(), NOW()),
('master.menu.edit', 'Master Menu Edit', NOW(), NOW()),
('system.role.list', 'System Role List', NOW(), NOW()),
('system.list', 'System List', NOW(), NOW()),
('master.launcher-group.list', 'Master Launcher Group List', NOW(), NOW()),
('storage.local', 'Storage Local', NOW(), NOW()),
('storage.local.upload', 'Storage Local Upload', NOW(), NOW());

-- Menus
INSERT INTO menus (`parent_id`, `system_route_id`, `title`, `icon`, `sort_order`, `is_sidebar`, `launcher_group`, `created_at`, `updated_at`) VALUES
(NULL, 5, 'Dashboard', 'ti ti-home', 1, 1, NULL, NOW(), NOW()),
(NULL, NULL, 'Master', 'ti ti-database', 2, 1, NULL, NOW(), NOW()),
(2, 10, 'Master Barang List', 'ti ti-package', 1, 1, 'master_data', NOW(), NOW()),
(NULL, NULL, 'Transaksi', 'ti ti-shopping-cart', 3, 1, NULL, NOW(), NOW()),
(4, 16, 'Transaksi Penjualan List', 'ti ti-receipt', 1, 1, 'transaksi', NOW(), NOW()),
(NULL, NULL, 'Sistem', 'ti ti-devices-cog', 4, 1, 'sistem', NOW(), NOW()),
(6, 24, 'System List', 'ti ti-user-key', 1, 1, 'sistem', NOW(), NOW()),
(6, 20, 'Master Menu List', 'ti ti-apps', 2, 1, 'sistem', NOW(), NOW()),
(2, 13, 'Master Cabang List', 'ti ti-arrow-fork-triple', 1, 1, 'master_data', NOW(), NOW());

-- Roles
INSERT INTO roles (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('admin', 'web', NOW(), NOW()),
('kasir', 'web', NOW(), NOW()),
('Super Admin', 'web', NOW(), NOW());

-- Permissions
INSERT INTO permissions (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('master.barang.delete', 'web', NOW(), NOW()),
('master.barang.export', 'web', NOW(), NOW()),
('master.cabang.delete', 'web', NOW(), NOW()),
('auth.permission.matrix', 'web', NOW(), NOW()),
('auth.register.create', 'web', NOW(), NOW()),
('auth.register.update', 'web', NOW(), NOW()),
('auth.register.view', 'web', NOW(), NOW()),
('dashboard', 'web', NOW(), NOW()),
('default-livewire.update', 'web', NOW(), NOW()),
('login', 'web', NOW(), NOW()),
('login.process', 'web', NOW(), NOW()),
('logout', 'web', NOW(), NOW()),
('master.barang.create', 'web', NOW(), NOW()),
('master.barang.update', 'web', NOW(), NOW()),
('master.barang.view', 'web', NOW(), NOW()),
('master.cabang.create', 'web', NOW(), NOW()),
('master.cabang.update', 'web', NOW(), NOW()),
('master.cabang.view', 'web', NOW(), NOW()),
('master.launcher-group.view', 'web', NOW(), NOW()),
('master.menu.create', 'web', NOW(), NOW()),
('master.menu.update', 'web', NOW(), NOW()),
('master.menu.view', 'web', NOW(), NOW()),
('storage.local', 'web', NOW(), NOW()),
('storage.local.upload', 'web', NOW(), NOW()),
('system.view', 'web', NOW(), NOW()),
('system.role.view', 'web', NOW(), NOW()),
('transaksi.penjualan.create', 'web', NOW(), NOW()),
('transaksi.penjualan.update', 'web', NOW(), NOW()),
('transaksi.penjualan.view', 'web', NOW(), NOW());

-- Role Has Permissions (Super Admin = all)
INSERT INTO role_has_permissions (`permission_id`, `role_id`)
SELECT p.id, r.id FROM permissions p, roles r WHERE r.name = 'Super Admin';

-- ============================================
-- ADMIN USER (harus di-create manual karena password hash)
-- Email: dwebpro@gmail.com | Password: admin123
-- Jalankan di Laravel: php artisan tinker
--   User::create(['name'=>'Dwebpro','email'=>'dwebpro@gmail.com','password'=>bcrypt('admin123'),'cabang_id'=>1]);
--   User::where('email','dwebpro@gmail.com')->first()->assignRole('Super Admin');
-- ============================================
