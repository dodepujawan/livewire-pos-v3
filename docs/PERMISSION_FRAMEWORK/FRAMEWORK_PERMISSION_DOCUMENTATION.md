# POS SPA Framework --- Documentation v1
# ### TRUTH SOURCES ###

## 1. Status

Dokumentasi ini mengikuti kondisi kode framework saat ini.

  Komponen                     Status
  ---------------------------- --------
  Laravel 12                   ✅
  Livewire 4 MFC               ✅
  Route Synchronization        ✅
  System Route                 ✅
  Dynamic Menu                 ✅
  Dynamic Sidebar              ✅
  Permission Synchronization   ✅
  Permission Matrix            ✅
  Role Management              ✅
  Dynamic Authorization        ✅
  Automated Core Testing       ✅
  Documentation                ✅
  Deployment Automation        ✅
  API Support                  ⬜ not needed ATM
  Framework Generator          ⬜ not needed
Milestone 9 (Developer Experience / Generator) sengaja belum menjadi
bagian framework core v1.

------------------------------------------------------------------------

# 2. Prinsip Framework

Framework menggunakan route sebagai sumber utama untuk halaman.

Alur utama:

``` text
Laravel Route
     ↓
Route Sync
     ↓
system_routes
     ↓
PermissionNameService
     ↓
Spatie Permission
     ↓
Role
     ↓
Authorization
     ↓
Sidebar
```

Menu disimpan terpisah dari route sehingga administrator dapat mengatur
struktur sidebar tanpa mengubah kode route.

------------------------------------------------------------------------

# 3. Struktur Utama

Struktur framework yang relevan:

``` text
app/
├── Console/
│   └── Commands/
│       ├── RouteSyncCommand.php
│       └── PermissionSyncCommand.php
│
├── Http/
│   └── Middleware/
│       └── PermissionMiddleware.php
│
├── Models/
│   ├── Menu.php
│   └── SystemRoute.php
│
├── Services/
│   ├── PermissionNameService.php
│   ├── PermissionMatrixService.php
│   └── PermissionScannerService.php
│
└── Support/
    └── AuthorizesRoute.php

database/
├── migrations/
│   ├── create_permission_tables.php
│   ├── create_system_routes_table.php
│   └── create_menus_table.php
│
└── seeders/
    ├── DatabaseSeeder.php
    ├── MenuSeeder.php
    ├── RoleSeeder.php
    ├── SuperAdminSeeder.php
    └── UserSeeder.php

resources/views/pages/
├── auth/
├── master/
├── system/
└── transaksi/

routes/
└── web.php

bootstrap/
└── app.php
```

------------------------------------------------------------------------

# 4. Route Convention

Route merupakan fondasi framework.

Contoh:

``` php
Route::livewire(
    '/barang',
    'pages::master.barang-list'
)->name('master.barang.list');
```

Konvensi:

``` text
master.barang.list
│      │      │
│      │      └── Page Action
│      └───────── Resource
└──────────────── Resource
```

Jumlah segmen tidak dibatasi.

Segmen terakhir dianggap sebagai action halaman.

Contoh:

``` text
master.barang.list
master.barang.create
master.barang.edit

transaksi.penjualan.list
transaksi.penjualan.create
transaksi.penjualan.show
transaksi.penjualan.edit
```

Untuk halaman yang ingin menjadi sumber menu/sidebar, gunakan action
`list`.

Catatan pada `routes/web.php` project saat ini:

``` text
Route yang ingin ditampilkan sebagai halaman/menu utama Livewire menggunakan .list
```

------------------------------------------------------------------------

# 5. Route Synchronization

File:

``` text
app/Console/Commands/RouteSyncCommand.php
```

Command:

``` bash
php artisan framework:route-sync
```

Fungsi:

1.  membaca seluruh named route Laravel;
2.  mengabaikan route internal tertentu;
3.  menyimpan route ke `system_routes`;
4.  membuat `display_name`;
5.  memperbarui route yang sudah ada;
6.  menghapus route yang sudah tidak ada jika route tersebut tidak
    digunakan oleh menu.

Route internal yang diabaikan:

``` text
livewire.*
ignition.*
debugbar.*
sanctum.*
```

Display name dibuat dari route name.

Contoh:

``` text
master.barang.list
        ↓
Master Barang List
```

Command tidak menghapus `system_routes` yang masih memiliki menu.

------------------------------------------------------------------------

# 6. SystemRoute

Model:

``` text
app/Models/SystemRoute.php
```

Kolom utama:

``` text
route_name
display_name
```

Relasi:

``` php
public function menus(): HasMany
```

`system_routes` merupakan daftar halaman yang ditemukan framework.

------------------------------------------------------------------------

# 7. Dynamic Menu

Model:

``` text
app/Models/Menu.php
```

Kolom:

``` text
parent_id
system_route_id
title
icon
sort_order
is_sidebar
```

Relasi:

``` text
Menu
├── parent
├── children
└── systemRoute
```

Aturan:

-   Root menu dapat tidak memiliki route.
-   Child menu dapat mempunyai parent.
-   Route disimpan melalui `system_route_id`.
-   Judul menu disimpan di `menus.title`.
-   Icon disimpan di database.
-   Urutan disimpan di `sort_order`.
-   `is_sidebar` menentukan apakah menu digunakan pada sidebar.

Struktur contoh:

``` text
Master
├── Barang
└── ...

Transaksi
└── Penjualan
```

------------------------------------------------------------------------

# 8. Menu Seeder

File:

``` text
database/seeders/MenuSeeder.php
```

Seeder saat ini membuat contoh menu:

``` text
Dashboard
Master
└── Barang
Transaksi
└── Penjualan
```

MenuSeeder membaca `SystemRoute` untuk mencari route ID.

Penting:

`MenuSeeder` saat ini masih berisi struktur menu contoh project dan
perlu disesuaikan apabila framework dipakai untuk project baru.

Jangan menganggap MenuSeeder sebagai generator menu otomatis.

------------------------------------------------------------------------

# 9. Sidebar

Sidebar menggunakan data `Menu` dari database.

Kemampuan:

-   Database driven
-   Parent / Child
-   Sort order
-   Icon
-   Active menu
-   Active parent
-   Expand / collapse
-   Permission filtering
-   Livewire navigation

Sidebar tidak menggunakan daftar menu hard-code sebagai sumber utama.

Authorization sidebar menggunakan:

``` text
App\Services\PermissionNameService
```

dan authorization layer framework.

------------------------------------------------------------------------

# 10. Permission Naming

File:

``` text
app/Services/PermissionNameService.php
```

Service ini merupakan pusat konversi:

``` text
Route Name
    ↓
Permission Name
```

Mapping:

``` text
list    → view
show    → view

create  → create
store   → create

edit    → update
update  → update

destroy → delete
delete  → delete

print   → print
export  → export
import  → import
```

Contoh:

``` text
master.barang.list
        ↓
master.barang.view

master.barang.edit
        ↓
master.barang.update

transaksi.penjualan.show
        ↓
transaksi.penjualan.view
```

Jika action tidak ada pada mapping, action tersebut dipertahankan.

Contoh:

``` text
transaksi.penjualan.approval
        ↓
transaksi.penjualan.approval
```

------------------------------------------------------------------------

# 11. Permission Synchronization

File:

``` text
app/Console/Commands/PermissionSyncCommand.php
```

Command:

``` bash
php artisan framework:permission-sync
```

Alur:

``` text
system_routes
     ↓
PermissionNameService
     ↓
Route Permissions

PermissionScannerService
     ↓
Additional Permissions

Route Permissions + Additional Permissions
     ↓
unique
     ↓
Spatie Permission
```

Command menggunakan guard:

``` text
web
```

Permission yang belum ada akan dibuat.

Permission yang sudah ada akan dipertahankan.

Permission yang sudah tidak berasal dari sumber sinkronisasi akan
dihapus hanya jika permission tersebut:

``` text
tidak memiliki role
```

Permission yang masih digunakan oleh role tidak dihapus oleh cleanup
tersebut.

------------------------------------------------------------------------

# 12. Additional Permissions

Tidak semua permission merupakan halaman.

Contoh action bisnis:

``` text
delete
print
export
import
approval
posting
closing
cancel
```

Untuk action bisnis yang bukan Page, deklarasikan:

``` php
protected array $additionalPermissions = [
    'master.barang.delete',
    'master.barang.export',
];
```

PermissionScannerService akan mencari deklarasi tersebut.

------------------------------------------------------------------------

# 13. PermissionScannerService

File:

``` text
app/Services/PermissionScannerService.php
```

Scanner membaca handler yang digunakan oleh route.

Didukung:

``` text
Livewire MFC Component
Laravel Controller
```

## Livewire

Scanner membaca metadata:

``` text
livewire_component
```

Kemudian mencari file component MFC.

Scanner membaca source code dan mengekstrak:

``` php
protected array $additionalPermissions = [
    ...
];
```

Scanner tidak melakukan instantiate anonymous Livewire component.

Ini penting karena arsitektur Livewire MFC menggunakan:

``` php
new class extends Component
```

## Controller

Scanner menggunakan reflection untuk membaca property:

``` php
protected array $additionalPermissions = [];
```

------------------------------------------------------------------------

# 14. Permission Matrix

File:

``` text
app/Services/PermissionMatrixService.php
```

Permission Matrix mengelompokkan permission berdasarkan resource.

Contoh:

``` text
master.barang
├── view
├── create
├── update
├── delete
├── print
├── export
└── import
```

Urutan action yang digunakan:

``` text
view
create
update
delete
print
export
import
```

Permission internal seperti login, storage, dan resource tertentu yang
tidak relevan untuk matrix dikecualikan melalui `ignoredResources`.

------------------------------------------------------------------------

# 15. Role

Role menggunakan Spatie Permission.

Seeder yang tersedia:

``` text
database/seeders/RoleSeeder.php
database/seeders/SuperAdminSeeder.php
database/seeders/UserSeeder.php
```

Role dapat:

-   dibuat;
-   diubah;
-   dihapus;
-   diberi permission;
-   dicabut permission;
-   digunakan oleh user.

`Super Admin` dilindungi oleh UI role management.

------------------------------------------------------------------------

# 16. Super Admin

File:

``` text
database/seeders/SuperAdminSeeder.php
```

Seeder membuat atau mengambil:

``` text
Super Admin
```

Kemudian memberikan seluruh permission yang tersedia.

``` php
$role->syncPermissions(
    Permission::all()
);
```

Dengan demikian Super Admin mendapatkan permission yang tersedia ketika
seeder dijalankan.

------------------------------------------------------------------------

# 17. User dan Role

File:

``` text
database/seeders/UserSeeder.php
```

Seeder membuat role:

``` text
admin
```

dan user admin contoh:

``` text
email: admin@gmail.com
```

dengan password yang didefinisikan pada seeder.

**Catatan keamanan:** kredensial contoh dalam seeder harus diganti atau
dihapus sebelum digunakan pada production.

------------------------------------------------------------------------

# 18. DatabaseSeeder

File:

``` text
database/seeders/DatabaseSeeder.php
```

Saat ini memanggil:

``` text
MenuSeeder
SuperAdminSeeder
```

`RoleSeeder` dan `UserSeeder` tersedia tetapi tidak dipanggil oleh
`DatabaseSeeder` saat ini.

Ini merupakan kondisi project saat ini dan bukan dianggap sebagai
automation final.

------------------------------------------------------------------------

# 19. Authorization

File utama:

``` text
app/Http/Middleware/PermissionMiddleware.php
```

Alias didaftarkan di:

``` text
bootstrap/app.php
```

Alias:

``` text
permission
```

Penggunaan:

``` php
Route::prefix('master')
    ->middleware(['auth', 'permission'])
    ->group(function () {
        ...
    });
```

Authorization membaca nama route.

Contoh:

``` text
master.barang.list
        ↓
PermissionNameService
        ↓
master.barang.view
        ↓
auth()->user()->can(...)
```

Jika user tidak memiliki permission:

``` text
403 Forbidden
```

Jika route tidak memiliki route name, middleware saat ini melewati
authorization tersebut.

Karena itu route yang menggunakan authorization framework harus
mempunyai named route.

------------------------------------------------------------------------

# 20. AuthorizesRoute Trait

File:

``` text
app/Support/AuthorizesRoute.php
```

Trait menyediakan:

``` php
authorizeRoute()
```

Fungsinya menggunakan route name dan `PermissionNameService` untuk
memeriksa permission user.

Trait ini merupakan authorization helper untuk digunakan ketika
authorization perlu dilakukan dari dalam component/class, terpisah dari
route middleware.

------------------------------------------------------------------------

# 21. Route Middleware vs Additional Permission

Gunakan middleware untuk authorization terhadap Page:

``` text
master.barang.list
master.barang.create
master.barang.edit
```

Gunakan `additionalPermissions` untuk action bisnis yang bukan Page:

``` text
master.barang.delete
master.barang.export
transaksi.penjualan.cancel
transaksi.penjualan.posting
```

Jangan membuat mapping route → permission baru di component.

Gunakan:

``` text
PermissionNameService
```

sebagai pusat mapping.

------------------------------------------------------------------------

# 22. Membuat Page Baru

Contoh membuat halaman Barang:

``` text
resources/views/pages/master/
└── ⚡barang-list/
    ├── barang-list.php
    └── barang-list.blade.php
```

Route:

``` php
Route::livewire(
    '/barang',
    'pages::master.barang-list'
)->name('master.barang.list');
```

Kemudian jalankan:

``` bash
php artisan framework:route-sync
```

Setelah itu:

``` bash
php artisan framework:permission-sync
```

Hasil:

``` text
system_routes
master.barang.list

permission
master.barang.view
```

Setelah permission tersedia, menu dapat dibuat melalui menu management.

------------------------------------------------------------------------

# 23. Membuat Action Bisnis

Misalnya ada tombol Export.

Export bukan Page baru.

Deklarasikan:

``` php
protected array $additionalPermissions = [
    'master.barang.export',
];
```

Kemudian:

``` bash
php artisan framework:permission-sync
```

Permission:

``` text
master.barang.export
```

akan tersedia untuk Role Permission Matrix.

------------------------------------------------------------------------

# 24. Testing

Testing core saat ini menggunakan PHPUnit/Pest Laravel.

Menjalankan seluruh test:

``` bash
php artisan test
```

Current core test coverage:

``` text
Application redirect
Route → Permission mapping
Multiple route segments
Permission middleware allow
Permission middleware deny
```

Current result:

``` text
10 passed
11 assertions
```

Testing tidak dimaksudkan untuk mengejar jumlah test sebanyak mungkin.

Tujuannya adalah menjaga bagian framework yang paling kritis agar
perubahan berikutnya tidak merusaknya.

------------------------------------------------------------------------

# 25. Deployment / Clone Checklist

Untuk project baru, prinsipnya:

``` text
1. Install dependency
2. Configure .env
3. Configure database
4. Run migrations
5. Seed required data
6. Synchronize routes
7. Synchronize permissions
8. Configure initial roles/users
9. Configure menus
10. Run tests
```

Command utama:

``` bash
php artisan migrate
php artisan db:seed
php artisan framework:route-sync
php artisan framework:permission-sync
php artisan test
```

**Catatan penting:** automation lengkap untuk deployment belum menjadi
bagian final saat ini. `DatabaseSeeder`, `MenuSeeder`, `RoleSeeder`, dan
`UserSeeder` masih memiliki peran yang berbeda dan sebagian masih
membutuhkan penyesuaian untuk project baru.

------------------------------------------------------------------------

# 26. Framework Configuration Sync

Framework menyediakan mekanisme untuk membawa konfigurasi
route, permission, dan menu dari environment development ke
environment deployment tanpa memasukkan database secara langsung.

## Export dari Local

Setelah route, permission, dan menu selesai dikonfigurasi:

```bash
php artisan make:command FrameworkConfigExportCommand

php artisan framework:route-sync
php artisan framework:permission-sync
php artisan framework:config-export

# Untuk pull
php artisan make:command FrameworkConfigImportCommand
git pull
php artisan framework:config-import

Menu yang sudah ada tidak akan dibuat ulang.
Menu baru akan dibuat berdasarkan route atau parent information
yang tersedia di framework-data.json.
Root menu diproses terlebih dahulu, kemudian child menu diproses
setelah parent tersedia.
Prinsip Sinkronisasi
Configuration sync bersifat additive dan aman terhadap konfigurasi
menu yang sudah ada.
Menu sudah ada
    → Skip
Menu belum ada
    → Create
Import tidak dimaksudkan untuk menghapus atau menimpa konfigurasi
menu yang sudah diatur administrator pada environment tujuan.
Idempotency
Command import dapat dijalankan lebih dari satu kali.
Contoh:
php artisan framework:config-import
Jika konfigurasi sudah tersedia, menu akan dilewati dan tidak
digandakan.

# 26a. Production Checklist

Sebelum production:

``` text
[ ] .env production sudah benar
[ ] APP_DEBUG=false
[ ] Database production benar
[ ] Migration selesai
[ ] Route sync selesai
[ ] Permission sync selesai
[ ] Role sudah dibuat
[ ] User sudah memiliki role
[ ] Menu sudah disusun
[ ] Permission middleware aktif
[ ] php artisan test berhasil
```

Jangan menggunakan password contoh dari development untuk production.

------------------------------------------------------------------------

# 27. Known Limitations

Framework saat ini belum mencakup:

``` text
API authorization layer
Framework generator
CRUD generator
Automatic deployment wizard
Automatic seeder orchestration
GitHub release automation
Version management
```

API tidak merupakan requirement untuk framework web/Livewire saat ini.

API dapat ditambahkan sebagai layer terpisah di masa depan tanpa
mengubah konsep utama route/menu web framework.

------------------------------------------------------------------------

# 28. Framework Decision Rules

Sebelum menambahkan fitur framework baru, pertimbangkan:

``` text
Apakah fitur benar-benar dibutuhkan?
Apakah ada solusi lebih sederhana?
Apakah scalable?
Apakah mudah di-maintain?
Apakah perubahan 2 tahun lagi dapat dilakukan tanpa membongkar arsitektur?
```

Framework tidak dibuat untuk memiliki sebanyak mungkin file atau
service.

Setiap abstraction harus memiliki alasan.

------------------------------------------------------------------------

# 29. Important Rules --- Jangan Dilupakan

### Route

``` text
Nama route adalah fondasi authorization.
```

### Permission

``` text
Jangan membuat convertPermission() baru.
Gunakan PermissionNameService.
```

### Page

``` text
Page action terakhir menentukan mapping permission.
```

### Business Action

``` text
Gunakan additionalPermissions.
```

### Sidebar

``` text
Menu berasal dari database.
```

### SystemRoute

``` text
Jangan menghapus route yang masih digunakan Menu.
```

### Permission Cleanup

``` text
Permission yang masih digunakan Role tidak dihapus oleh cleanup.
```

------------------------------------------------------------------------

# 30. Quick Reference

## Route

``` bash
php artisan framework:route-sync
```

## Permission

``` bash
php artisan framework:permission-sync
```

## Test

``` bash
php artisan test
```

## File penting

``` text
app/Console/Commands/RouteSyncCommand.php
app/Console/Commands/PermissionSyncCommand.php

app/Services/PermissionNameService.php
app/Services/PermissionMatrixService.php
app/Services/PermissionScannerService.php

app/Http/Middleware/PermissionMiddleware.php
app/Support/AuthorizesRoute.php

app/Models/SystemRoute.php
app/Models/Menu.php

bootstrap/app.php
routes/web.php
```

------------------------------------------------------------------------

# 31. Current Framework Flow

``` text
                 ROUTES
                    │
                    ▼
          RouteSyncCommand
                    │
                    ▼
             system_routes
                    │
          ┌─────────┴─────────┐
          ▼                   ▼
       MENUS          PermissionNameService
          │                   │
          ▼                   ▼
       SIDEBAR           PERMISSIONS
                              │
                              ▼
                            ROLES
                              │
                              ▼
                         AUTHORIZATION
                              │
                     ┌────────┴────────┐
                     ▼                 ▼
                 Middleware       Livewire Trait
```

------------------------------------------------------------------------

# 32. Final v1 Principle

Framework ini merupakan fondasi aplikasi POS berbasis Laravel +
Livewire.

Tujuan utamanya bukan menjadi framework universal untuk semua jenis
aplikasi.

Tujuannya adalah menyediakan fondasi yang:

``` text
predictable
scalable
repeatable
maintainable
```

dan dapat digunakan kembali pada project berikutnya tanpa membangun
ulang sistem:

``` text
Route
Menu
Sidebar
Permission
Role
Authorization
```

dari awal.



<!-- UNTUK PROGRAMMER APABILA ^ BULAN KEDEPAN LUPA CEK INI -->
Menu.php
SystemRoute.php
create_menus_table.php
create_system_routes_table.php

menu-list/
menu-create/
menu-edit/

sidebar/
layouts/

web.php

PermissionNameService.php
PermissionMatrixService.php
PermissionMiddleware.php
AuthorizesRoute.php
PermissionSyncCommand.php
RouteSyncCommand.php

FrameworkConfigExportCommand.php
FrameworkConfigImportCommand.php
framework-data.json

MenuSeeder.php

PROJECT_RULES.md
FRAMEWORK_PERMISSION_DOCUMENTATION.md
docs/CHANGELOG.md
MODULE_*.md