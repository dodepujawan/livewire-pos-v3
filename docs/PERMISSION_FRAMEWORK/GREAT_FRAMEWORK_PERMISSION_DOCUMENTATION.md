# POS SPA Framework — Great Documentation

> **Tujuan:** Dokumentasi lengkap, praktis, dan copy-paste ready untuk framework POS SPA. Semua alur, file penting, kode inti, dan langkah implementasi launcher ditulis satu tempat agar mudah diikuti maupun dijadikan acuan project baru.

---

## 1. Status Framework

```text
Komponen                     Status
---------------------------  --------
Laravel 13                   ✅
Livewire 4 MFC               ✅
Route Synchronization        ✅
System Route                 ✅
Dynamic Menu                 ✅
Dynamic Sidebar              ✅
Permission Synchronization   ✅
Permission Matrix            ✅
Role Listing / Assignment    ✅ (Role CRUD partial)
Dynamic Authorization        ✅
Config Export/Import         ✅
Desktop Launcher             ✅
Documentation                ✅
API Support                  ⬜ not needed ATM
Framework Generator          ⬜ not needed
```

---

## 2. Prinsip Dasar

Framework menggunakan **route sebagai sumber kebenaran**.

```text
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
Sidebar / Launcher
```

Menu disimpan terpisah dari route sehingga administrator dapat mengatur struktur sidebar tanpa mengubah kode route.

---

## 3. Struktur Folder Penting

```text
app/
├── Console/Commands/
│   ├── RouteSyncCommand.php
│   ├── PermissionSyncCommand.php
│   ├── FrameworkConfigExportCommand.php
│   └── FrameworkConfigImportCommand.php
├── Http/Middleware/
│   └── PermissionMiddleware.php
├── Models/
│   ├── Menu.php
│   ├── SystemRoute.php
│   └── LauncherGroup.php
├── Services/
│   ├── PermissionNameService.php
│   ├── PermissionMatrixService.php
│   └── PermissionScannerService.php
├── Support/
│   └── AuthorizesRoute.php
└── Http/Controllers/Auth/LoginController.php

database/
├── migrations/
│   ├── create_menus_table.php
│   ├── 2026_09_26_000001_add_sidebar_heading_to_menus_table.php
│   ├── create_system_routes_table.php
│   └── create_launcher_groups_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── MenuSeeder.php
    ├── LauncherGroupSeeder.php
    └── SuperAdminSeeder.php

resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── navbar.blade.php
│   └── sidebar.blade.php
├── components/
│   ├── ⚡sidebar/
│   │   ├── sidebar.php
│   │   └── sidebar.blade.php
│   └── ⚡launcher/
│       ├── launcher.php
│       └── launcher.blade.php
├── pages/
│   ├── master/
│   │   ├── ⚡menu-list/
│   │   ├── ⚡menu-create/
│   │   ├── ⚡menu-edit/
│   │   └── ⚡launcher-group-manager/
│   ├── system/
│   └── transaksi/
└── dashboard/
    └── index.blade.php

routes/
└── web.php

docs/
├── PERMISSION_FRAMEWORK/
│   ├── GREAT_FRAMEWORK_PERMISSION_DOCUMENTATION.md
│   ├── FRAMEWORK_PERMISSION_DOCUMENTATION.md
│   ├── FUTURE_DESKTOP_LAUNCHER.md
│   └── DESKTOP_LAUNCHER-PROGRESS.md
├── PROJECT_RULES_v2.md
└── CHANGELOG.md
```

---

## 4. Route Convention

Route merupakan fondasi framework.

```php
Route::livewire(
    '/barang',
    'pages::master.barang-list'
)->name('master.barang.list');
```

Konvensi:

```text
master.barang.list
│      │      │
│      │      └── Page Action
│      └───────── Resource
└──────────────── Resource
```

Jumlah segmen tidak dibatasi. Segmen terakhir dianggap sebagai action halaman.

Contoh:

```text
master.barang.list
master.barang.create
master.barang.edit

transaksi.penjualan.list
transaksi.penjualan.create
transaksi.penjualan.show
transaksi.penjualan.edit
```

Untuk halaman yang ingin menjadi sumber menu/sidebar, gunakan action `list`.

---

## 5. Route Synchronization

File: `app/Console/Commands/RouteSyncCommand.php`

Command:
```bash
php artisan framework:route-sync
```

Fungsi:
1. Membaca seluruh named route Laravel
2. Mengabaikan route internal tertentu
3. Menyimpan route ke `system_routes`
4. Membuat `display_name`
5. Memperbarui route yang sudah ada
6. Menghapus route yang sudah tidak ada jika route tersebut tidak digunakan oleh menu

Mode normal membuat `display_name` dari nama route, memperbarui record route yang sudah ada, lalu menghapus route stale yang tidak lagi ditemukan **hanya jika route tersebut tidak dipakai oleh menu**.

Untuk sinkronisasi tambahan yang mempertahankan konfigurasi environment saat ini:
```bash
php artisan framework:route-sync --safe
```

Mode `--safe` hanya menambahkan route yang belum tercatat. Route yang sudah ada tidak diperbarui dan route stale tidak dihapus. Mode ini dipakai oleh `framework:config-import`.

Route prefix yang saat ini diabaikan:
```text
livewire.*
ignition.*
debugbar.*
sanctum.*
```

Daftar ini persis dengan filter command. Prefix seperti `default-livewire.*` dan `storage.*` tidak termasuk filter dan dapat ikut tersinkron.

Display name dibuat dari route name:
```text
master.barang.list → Master Barang List
```

Command tidak menghapus `system_routes` yang masih memiliki menu.

---

## 6. SystemRoute Model

File: `app/Models/SystemRoute.php`

Kolom utama:
- `route_name`
- `display_name`

Relasi:
```php
public function menus(): HasMany
```

`system_routes` merupakan daftar halaman yang ditemukan framework.

---

## 7. Dynamic Menu

File: `app/Models/Menu.php`

Kolom:
- `parent_id`
- `system_route_id`
- `title`
- `icon`
- `sort_order`
- `is_sidebar`
- `launcher_group`
- `sidebar_heading` (nullable, maksimal 100 karakter)

Relasi:
```text
Menu
├── parent
├── children
├── systemRoute
└── launcherGroup
```

Aturan:
- Root menu dapat tidak memiliki route
- Child menu dapat mempunyai parent
- Route disimpan melalui `system_route_id`
- `system_route_id` nullable tetapi unique; satu `SystemRoute` hanya dapat dipakai oleh satu menu
- Foreign key parent dan route membatasi penghapusan record yang masih direferensikan
- Judul menu disimpan di `menus.title`
- Icon disimpan di database
- Urutan disimpan di `sort_order`
- `is_sidebar` menentukan apakah menu digunakan pada sidebar
- `launcher_group` menentukan apakah menu muncul di launcher dan group nya apa
- `sidebar_heading` menyimpan teks bebas yang ditampilkan di atas menu root pada sidebar
- `sidebar_heading` hanya berlaku ketika `parent_id` kosong; submenu tidak dapat menyimpannya
- Menu root dapat berupa link langsung atau dropdown; keduanya dapat memiliki `sidebar_heading`

Struktur contoh:
```text
Master
├── Barang
└── ...

Transaksi
└── Penjualan
```

---

## 8. Menu Seeder

File: `database/seeders/MenuSeeder.php`

Seeder membuat contoh menu:
```text
Dashboard
Master
├── Barang
└── Cabang
Transaksi
├── Penjualan
├── Pembelian
├── Piutang
└── Hutang
Laporan
├── Laporan Kas
├── Laporan Penjualan
├── Laporan Stok
├── Laporan Buku Besar
├── Laba Rugi
├── Neraca
└── Arus Kas
Sistem
└── Pengaturan
```

MenuSeeder membaca `SystemRoute` untuk mencari route ID.

**Peringatan data:** `MenuSeeder` saat ini menonaktifkan pemeriksaan foreign key dan menjalankan `Menu::query()->delete()` sebelum membuat menu contoh. `DatabaseSeeder` memanggil `MenuSeeder`, sehingga `php artisan db:seed` dapat menghapus lalu mengganti seluruh data menu yang sudah ada.

MenuSeeder masih berisi struktur contoh project dan perlu disesuaikan bila framework dipakai untuk project baru. Jangan menganggapnya sebagai generator menu otomatis atau sebagai cara aman untuk menerapkan konfigurasi ke database yang sudah berisi data.

---

## 9. Sidebar

Sidebar menggunakan data `Menu` dari database.

Kemampuan:
- Database driven
- Parent / Child
- Sort order
- Icon
- Active menu
- Active parent
- Expand / collapse
- Permission filtering
- Livewire navigation
- Optional `sidebar_heading` di atas menu root
- Expand/collapse dijalankan lokal oleh Alpine agar tidak menunggu request Livewire untuk setiap klik
- Hover dan active state memakai aksen amber; submenu memiliki transisi dan mengikuti preferensi reduced-motion
- Posisi scroll sidebar disimpan selama sesi dan dipulihkan setelah sidebar ditutup, dibuka, atau navigasi Livewire

Daftar menu dan permission tetap difilter di server menggunakan `PermissionNameService`; pemindahan state accordion ke Alpine tidak memindahkan authorization ke browser.

Parent root tanpa route disembunyikan bila tidak mempunyai child yang terlihat. Child tanpa route ditampilkan sebagai teks non-link. Dashboard mempunyai pengecualian khusus dan ditampilkan tanpa permission check di filter sidebar saat ini; route middleware tetap merupakan lapisan authorization yang terpisah.

---

## 10. Permission Naming

File: `app/Services/PermissionNameService.php`

Service ini merupakan pusat konversi:
```text
Route Name → Permission Name
```

Mapping:
```text
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
```text
master.barang.list → master.barang.view
master.barang.edit → master.barang.update
transaksi.penjualan.show → transaksi.penjualan.view
```

Jika action tidak ada pada mapping, action tersebut dipertahankan.

---

## 11. Permission Synchronization

File: `app/Console/Commands/PermissionSyncCommand.php`

Command:
```bash
php artisan framework:permission-sync
```

Alur:
```text
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

Command menggunakan guard: `web`

- Permission yang belum ada akan dibuat
- Permission yang sudah ada akan dipertahankan
- Permission yang sudah tidak berasal dari sumber sinkronisasi akan dihapus hanya jika permission tersebut tidak memiliki role
- Permission yang masih digunakan oleh role tidak dihapus oleh cleanup tersebut

Untuk menambah permission tanpa menghapus permission lama:
```bash
php artisan framework:permission-sync --safe
```

Mode `--safe` menambahkan permission yang belum ada dan mempertahankan seluruh permission yang sudah ada, termasuk permission yang belum ditugaskan ke role. `framework:config-import` menggunakan mode ini secara otomatis.

---

## 12. Additional Permissions

Tidak semua permission merupakan halaman.

Contoh action bisnis:
```text
delete, print, export, import, approval, posting, closing, cancel
```

Untuk action bisnis yang bukan Page, deklarasikan:
```php
protected array $additionalPermissions = [
    'master.barang.delete',
    'master.barang.export',
];
```

PermissionScannerService akan mencari deklarasi tersebut.

Deklarasi ini hanya mendaftarkan permission ke katalog Spatie agar dapat ditugaskan ke role. Deklarasi ini **tidak otomatis melindungi method/action**. Setiap action bisnis tetap harus memanggil pemeriksaan authorization yang sesuai, misalnya `can()` atau helper authorization, sebelum menjalankan operasi.

---

## 13. PermissionScannerService

File: `app/Services/PermissionScannerService.php`

Scanner membaca handler yang digunakan oleh route.

Didukung:
- Livewire MFC Component
- Laravel Controller

### Livewire
Scanner membaca metadata `livewire_component`, mengharapkan nama component dengan pola `pages::...`, lalu membentuk path `resources/views/pages/.../⚡{nama}/{nama}.php`. File harus menggunakan deklarasi literal:
```php
protected array $additionalPermissions = [
    'master.barang.export',
];
```

Scanner mengekstrak string dari deklarasi tersebut menggunakan regex; deklarasi yang dibangun secara dinamis tidak otomatis ditemukan.

Scanner tidak melakukan instantiate anonymous Livewire component. Ini penting karena arsitektur Livewire MFC menggunakan `new class extends Component`.

### Controller
Scanner menggunakan reflection dan container Laravel untuk membuat instance controller, lalu membaca property `$additionalPermissions`. Pastikan controller dapat di-resolve oleh container tanpa efek samping yang tidak diinginkan.

---

## 14. Permission Matrix

File: `app/Services/PermissionMatrixService.php`

Permission Matrix mengelompokkan permission berdasarkan resource.

Contoh:
```text
master.barang
├── view
├── create
├── update
├── delete
├── print
├── export
└── import
```

Urutan action: view, create, update, delete, print, export, import.

Permission internal seperti login, storage, dan resource tertentu yang tidak relevan untuk matrix dikecualikan melalui `ignoredResources`.

`PermissionMatrixService::build()` menghasilkan grouping resource/action tersebut. Namun, halaman Livewire `auth.permission.matrix` saat ini tidak memakai service ini; halaman tersebut memuat semua role dan permission lalu menampilkan nama permission yang ada secara langsung sebagai kolom. Jangan menganggap hasil service sebagai bentuk UI matrix saat ini.

Role list saat ini mendukung daftar dan penghapusan dengan guard (Super Admin tidak dapat dihapus dan role yang masih dipakai user ditolak). Link UI create/edit role masih tidak aktif/berkomentar. Sesuaikan checklist project baru: jangan mengasumsikan seluruh CRUD role sudah tersedia melalui UI.

Route `auth.permission.matrix` masih terdaftar di `routes/web.php` dalam group `auth` dan `permission`, walaupun route file memiliki komentar lama yang menyebut matrix sudah outdated. Verifikasi route source sebelum menghapus atau mengganti referensi dokumentasi ini.

---

## 15. Authorization

File utama: `app/Http/Middleware/PermissionMiddleware.php`

Alias didaftarkan di: `bootstrap/app.php`

Alias: `permission`

Penggunaan:
```php
Route::prefix('master')
    ->middleware(['auth', 'permission'])
    ->group(function () {
        ...
    });
```

Authorization membaca nama route:
```text
master.barang.list
        ↓
PermissionNameService
        ↓
master.barang.view
        ↓
auth()->user()->can(...)
```

Jika user tidak memiliki permission: **403 Forbidden**

Jika route tidak memiliki route name, `PermissionMiddleware` melewati **pemeriksaan permission**. Ini tidak berarti authentication otomatis dilewati: route group tetap harus menggunakan middleware `auth` secara terpisah. Route yang membutuhkan pemeriksaan permission harus mempunyai named route.

---

## 16. AuthorizesRoute Trait

File: `app/Support/AuthorizesRoute.php`

Trait menyediakan `authorizeRoute()` menggunakan route name dan `PermissionNameService` untuk memeriksa permission user. Trait ini digunakan ketika authorization perlu dilakukan dari dalam component/class, terpisah dari route middleware.

---

## 17. Route Middleware vs Additional Permission

Gunakan middleware untuk authorization terhadap Page:
```text
master.barang.list
master.barang.create
master.barang.edit
```

Gunakan `additionalPermissions` untuk action bisnis yang bukan Page:
```text
master.barang.delete
master.barang.export
transaksi.penjualan.cancel
transaksi.penjualan.posting
```

Jangan membuat mapping route → permission baru di component. Gunakan `PermissionNameService` sebagai pusat mapping.

`additionalPermissions` hanya membuat permission tersedia untuk Role/Permission Matrix. Proteksi action tetap harus dilakukan pada method yang menjalankannya; jangan menganggap scanner sebagai enforcement.

---

## 18. Membuat Page Baru

Contoh membuat halaman Barang:

```text
resources/views/pages/master/
└── ⚡barang-list/
    ├── barang-list.php
    └── barang-list.blade.php
```

Route:
```php
Route::livewire('/barang', 'pages::master.barang-list')
    ->name('master.barang.list');
```

Kemudian jalankan:
```bash
php artisan framework:route-sync --safe
php artisan framework:permission-sync --safe
```

Hasil:
```text
system_routes: master.barang.list
permission: master.barang.view
```

Setelah permission tersedia, menu dapat dibuat melalui menu management.

---

## 19. Membuat Action Bisnis

Misalnya ada tombol Export. Export bukan Page baru.

Deklarasikan:
```php
protected array $additionalPermissions = [
    'master.barang.export',
];
```

Kemudian:
```bash
php artisan framework:permission-sync --safe
```

Permission `master.barang.export` akan tersedia untuk Role Permission Matrix.

---

## 20. Testing

Testing core saat ini menggunakan PHPUnit/Pest Laravel.

Menjalankan seluruh test:
```bash
php artisan test
```

Current core test coverage:
- Application redirect
- Route → Permission mapping
- Multiple route segments
- Permission middleware allow
- Permission middleware deny

Current result: 10 passed, 11 assertions.

Angka di atas adalah hasil yang tercatat saat dokumentasi ini disusun, bukan klaim bahwa test sudah dijalankan pada setiap checkout atau environment. Jalankan kembali `php artisan test` sebelum memakai angka tersebut sebagai status terbaru. Coverage yang tercatat belum mencakup safe config import/export, route/permission sync, scanner edge cases, atau perilaku menu/sidebar.

Testing tidak dimaksudkan untuk mengejar jumlah test sebanyak mungkin. Tujuannya adalah menjaga bagian framework yang paling kritis agar perubahan berikutnya tidak merusaknya.

---

## 21. Deployment / Clone Checklist

Untuk project baru, prinsipnya:

```text
1. Install dependency
2. Configure .env
3. Configure database
4. Run migrations
5. Review dan seed hanya data awal yang memang diperlukan
6. Synchronize routes
7. Synchronize permissions
8. Configure initial roles/users
9. Configure menus
10. Run tests
```

Command utama:
```bash
php artisan migrate
php artisan framework:route-sync --safe
php artisan framework:permission-sync --safe
php artisan test
```

**Catatan penting:** automation lengkap untuk deployment belum menjadi bagian final saat ini. `DatabaseSeeder` memanggil `CabangSeeder`, `AkunSeeder`, `MenuSeeder`, `LauncherGroupSeeder`, `SuperAdminSeeder`, dan `UserSeeder`. `RoleSeeder` ada di repository tetapi tidak dipanggil oleh `DatabaseSeeder` saat ini; tinjau daftar tersebut sebelum menjalankan seeding pada project baru.

`DatabaseSeeder` memanggil `MenuSeeder`, dan `MenuSeeder` menghapus semua row menu sebelum membuat ulang menu contoh. Jalankan seeding hanya pada database baru/kosong setelah meninjau seluruh seeder yang dipanggil. Jangan memakai `php artisan db:seed` untuk memperbarui konfigurasi menu di database yang sudah berisi data; gunakan menu management atau config import.

---

## 22. Framework Configuration Sync

Framework menyediakan snapshot konfigurasi di `database/framework-data.json` untuk dipindahkan bersama source code. Export membaca database sumber; import di environment tujuan bersifat additive dan mempertahankan konfigurasi lokal yang sudah ada sejauh dapat dicocokkan.

### Isi File Export

`framework:config-export` menulis `version`, `generated_at`, serta empat bagian:
- `system_routes`: `route_name` dan `display_name`.
- `permissions`: nama permission dengan guard `web`.
- `menus`: route, title, root-only `sidebar_heading`, icon, urutan, status sidebar, launcher group, dan identitas parent berupa route/title.
- `launcher_groups`: key, label, icon, urutan, dan status aktif.

File hasil export perlu ikut dipindahkan/di-deploy bersama kode yang memakainya. Export tidak memasukkan data role, assignment permission ke role, atau akun pengguna.

### Sinkronisasi Aman

Sebelum export, tambahkan data route/permission baru secara aman lalu ekspor:
```bash
php artisan framework:route-sync --safe
php artisan framework:permission-sync --safe
php artisan framework:config-export
```

Mode safe route sync hanya menambahkan route baru tanpa mengubah `display_name` route yang sudah ada dan tanpa menghapus route stale. Mode safe permission sync hanya menambahkan permission baru tanpa menghapus permission yang sudah ada. Mode normal tanpa `--safe` tetap tersedia dan dapat memperbarui `display_name` atau melakukan cleanup; gunakan hanya jika perubahan tersebut memang diinginkan.

### Perilaku Import ke Target

Sebelum import pada database yang sudah digunakan, backup database dan periksa migration yang pending. Pastikan migration `sidebar_heading` sudah diterapkan sebelum menjalankan import atau membuka menu management.

Deploy kode dan `database/framework-data.json`, kemudian jalankan:
```bash
php artisan migrate:status
php artisan migrate --force
php artisan framework:config-import
```

`framework:config-import` otomatis menjalankan `framework:route-sync --safe` dan `framework:permission-sync --safe` pada environment target. Dua array `system_routes` dan `permissions` di JSON diekspor sebagai snapshot, tetapi tidak diimpor langsung; route target ditemukan dari route Laravel yang sedang terdaftar, dan permission dibentuk dari `system_routes` target serta hasil scanner. Display name dan permission yang sudah ada dipertahankan.

Launcher group baru dibuat menggunakan `firstOrCreate`. Jika key sudah ada, label, icon, urutan, dan status aktif yang tersimpan di target tidak ditimpa.

Menu diproses dua pass: root lebih dahulu, kemudian child. Menu existing dicocokkan berdasarkan route dan, sebagai fallback, title atau kombinasi parent/title. Properti menu target yang sudah ada umumnya dipertahankan: title, route, icon, urutan, status sidebar, dan launcher group. Pengecualian yang disengaja: `sidebar_heading` dari export hanya mengisi field root target yang masih kosong; heading target yang sudah terisi tidak ditimpa. Route conflict dipertahankan dan dilaporkan. Menu routed yang route-nya tidak tersedia pada target dilewati dengan peringatan.

Import dapat dijalankan ulang untuk data yang dapat dicocokkan tanpa menggandakan menu tersebut. Namun, jika administrator mengubah **title dan route sekaligus**, importer tidak memiliki stable ID lintas environment untuk memastikan menu itu sama; data yang tidak cocok dapat dianggap menu baru. Periksa jumlah menu dibuat/dilewati serta pesan conflict setelah import.

Batas validasi implementasi saat ini: importer memeriksa bahwa file ada dan JSON ter-decode menjadi array, tetapi tidak memvalidasi `version` atau seluruh struktur record di dalamnya. Section yang hilang dianggap kosong. Proses import belum dibungkus transaksi database dan return code dari dua command sync tidak diperiksa sebelum import berlanjut.

---

## 23. Production Checklist

```text
[ ] .env production sudah benar
[ ] APP_DEBUG=false
[ ] Database production benar
[ ] Backup dan status migration sudah dicek sebelum perubahan schema
[ ] Migration selesai setelah pemeriksaan di atas
[ ] Route sync aman selesai (`--safe`) atau perubahan normalnya sudah ditinjau
[ ] Permission sync aman selesai (`--safe`)
[ ] `framework-data.json` target sudah tersedia bila memakai config import
[ ] Role sudah dibuat
[ ] User sudah memiliki role
[ ] Menu sudah disusun
[ ] Permission middleware aktif
[ ] php artisan test berhasil
```

Jangan menggunakan password contoh dari development untuk production.
Jangan menjalankan `db:seed` di database berisi data sebelum seluruh seeder diperiksa; `MenuSeeder` menghapus seluruh menu yang ada.

---

## 24. Known Limitations

Framework saat ini belum mencakup:
```text
API authorization layer
Framework generator
CRUD generator
Automatic deployment wizard
Automatic seeder orchestration
GitHub release automation
Version management
```

API tidak merupakan requirement untuk framework web/Livewire saat ini. API dapat ditambahkan sebagai layer terpisah di masa depan tanpa mengubah konsep utama route/menu web framework.

---

## 25. Framework Decision Rules

Sebelum menambahkan fitur framework baru, pertimbangkan:
```text
Apakah fitur benar-benar dibutuhkan?
Apakah ada solusi lebih sederhana?
Apakah scalable?
Apakah mudah di-maintain?
Apakah perubahan 2 tahun lagi dapat dilakukan tanpa membongkar arsitektur?
```

Framework tidak dibuat untuk memiliki sebanyak mungkin file atau service. Setiap abstraction harus memiliki alasan.

---

## 26. Important Rules — Jangan Dilupakan

### Route
```text
Nama route adalah fondasi authorization.
```

### Permission
```text
Jangan membuat convertPermission() baru.
Gunakan PermissionNameService.
```

### Page
```text
Page action terakhir menentukan mapping permission.
```

### Business Action
```text
Gunakan additionalPermissions.
```

### Sidebar
```text
Menu berasal dari database.
```

### SystemRoute
```text
Jangan menghapus route yang masih digunakan Menu.
```

### Permission Cleanup
```text
Permission yang masih digunakan Role tidak dihapus oleh cleanup.
```

---

## 27. Quick Command Reference

```bash
# Framework
# Safe additive sync for existing environments
php artisan framework:route-sync --safe
php artisan framework:permission-sync --safe
php artisan framework:config-export
php artisan framework:config-import
php artisan test

# Livewire
php artisan make:livewire pages::master.nama-component --mfc

# Database
php artisan migrate
# Only after reviewing every called seeder and confirming an empty/new DB
php artisan db:seed
```

`framework:config-import` already invokes both sync commands with `--safe`. The normal commands without that option can update generated route display names and clean up stale/unassigned records. `db:seed` is not an upgrade command: `MenuSeeder` deletes existing menu rows, while `LauncherGroupSeeder` uses `updateOrCreate` and can reset customized launcher groups.

---

## 28. File Penting

```text
app/Console/Commands/RouteSyncCommand.php
app/Console/Commands/PermissionSyncCommand.php
app/Console/Commands/FrameworkConfigExportCommand.php
app/Console/Commands/FrameworkConfigImportCommand.php

app/Services/PermissionNameService.php
app/Services/PermissionMatrixService.php
app/Services/PermissionScannerService.php

app/Http/Middleware/PermissionMiddleware.php
app/Support/AuthorizesRoute.php

app/Models/SystemRoute.php
app/Models/Menu.php
app/Models/LauncherGroup.php
database/migrations/2026_09_26_000001_add_sidebar_heading_to_menus_table.php

bootstrap/app.php
routes/web.php
```

---

## 29. Arsitektur Framework (Visual)

```text
                  ROUTES (web.php)
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
      + LAUNCHER                 │
                                ▼
                              ROLES
                                │
                                ▼
                           AUTHORIZATION
                        ┌───────┴───────┐
                        ▼               ▼
                  Middleware       Livewire Trait
```

---

## 30. Implementasi Desktop Launcher (Step by Step)

Bagian ini adalah dokumentasi implementasi launcher yang sudah dilakukan. Gunakan sebagai template untuk project baru.

### M1 — Database & Model

#### Migration 1: add_launcher_group_to_menus_table

File: `src/database/migrations/2026_08_18_150000_add_launcher_group_to_menus_table.php`

```php
Schema::table('menus', function (Blueprint $table) {
    $table->string('launcher_group', 50)->nullable()->after('sort_order');
});
```

#### Migration 2: create_launcher_groups_table

File: `src/database/migrations/2026_08_18_160000_create_launcher_groups_table.php`

```php
Schema::create('launcher_groups', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->string('label');
    $table->string('icon')->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Migration 3: add_sidebar_heading_to_menus_table

File: `src/database/migrations/2026_09_26_000001_add_sidebar_heading_to_menus_table.php`

```php
Schema::table('menus', function (Blueprint $table) {
    $table->string('sidebar_heading', 100)->nullable()->after('title');
});
```

Field ini opsional. Nilai hanya dipakai pada menu root (`parent_id = null`) dan ditampilkan sebagai judul kecil di atas item menu sidebar. Menu root boleh berupa link langsung atau parent dropdown. Form mengosongkan serta menonaktifkan field ketika menu memiliki parent; server-side save juga memaksa submenu menyimpan `null`.

#### Model: Menu.php

File: `src/app/Models/Menu.php`

Tambah `launcher_group` ke `$fillable` dan relasi:

```php
protected $fillable = [
    'parent_id',
    'system_route_id',
    'title',
    'sidebar_heading',
    'icon',
    'sort_order',
    'is_sidebar',
    'launcher_group',
];

public function launcherGroup(): BelongsTo
{
    return $this->belongsTo(LauncherGroup::class, 'launcher_group', 'key');
}
```

#### Model: LauncherGroup.php

File: `src/app/Models/LauncherGroup.php`

```php
protected $fillable = [
    'key',
    'label',
    'icon',
    'sort_order',
    'is_active',
];

protected $casts = [
    'is_active' => 'boolean',
];

public function menus(): HasMany
{
    return $this->hasMany(Menu::class, 'launcher_group', 'key');
}
```

---

### M2 — Menu Management UI

#### Menu Create

File: `src/resources/views/pages/master/⚡menu-create/menu-create.php`

Tambah property dan rules:
```php
public ?string $launcher_group = null;
public ?string $sidebar_heading = null;

protected function rules(): array
{
    return [
        // ... rules lain
        'launcher_group' => ['nullable', 'string', 'max:50'],
        'sidebar_heading' => ['nullable', 'string', 'max:100'],
    ];
}
```

`sidebar_heading` hanya tersedia untuk menu root. Saat `parent_id` berubah dari kosong menjadi terisi, Livewire mengosongkan nilainya dan UI menonaktifkan input. Save juga memaksa nilai `null` bila parent terisi, sehingga aturan tidak hanya bergantung pada browser.

Pada Blade, `Parent Menu` memakai `wire:model.live="parent_id"` agar status input berubah segera. Input memakai `wire:model="sidebar_heading"` dan `@disabled(filled($parent_id))`; letakkan bersebelahan dengan parent selector untuk menghemat ruang, dan tampilkan helper text yang menjelaskan field hanya berlaku untuk root.

Di server-side create dan edit, validasi `nullable|string|max:100`, kosongkan nilai saat `parent_id` berubah menjadi terisi, lalu set `sidebar_heading` menjadi `null` lagi sebelum save jika parent masih terisi.

Form edit harus memuat nilai existing ke property `sidebar_heading`, dan menu list menampilkan kolom **Sidebar Heading** agar administrator dapat meninjau nilainya. Judul bebas seperti `Operasional` atau `Accounting Toko` disimpan apa adanya; tampilan sidebar yang mengubahnya menjadi uppercase secara visual.

File: `src/resources/views/pages/master/⚡menu-create/menu-create.blade.php`

Tambah dropdown:
```blade
<x-form.select label="Launcher Group" name="launcher_group" wire:model="launcher_group">
    <option value="">Tidak tampil di Launcher</option>
    @foreach($launcherGroups as $group)
        <option value="{{ $group->key }}">{{ $group->label }}</option>
    @endforeach
</x-form.select>
```

Update `render()` untuk pass `launcherGroups`:
```php
'launcherGroups' => \App\Models\LauncherGroup::where('is_active', true)->orderBy('sort_order')->get(),
```

#### Menu Edit

Sama seperti Menu Create, tapi tambah mount untuk load existing value:
```php
public function mount(Menu $menu): void
{
    // ... existing
    $this->launcher_group = $menu->launcher_group;
    $this->sidebar_heading = $menu->sidebar_heading;
}
```

#### Menu List

File: `src/resources/views/pages/master/⚡menu-list/menu-list.blade.php`

Tambah kolom:
```blade
<th class="px-4 py-3 text-center text-sm font-semibold">Launcher Group</th>
<th class="px-4 py-3 text-left text-sm font-semibold">Sidebar Heading</th>
```

Dan di row:
```blade
<td class="px-4 py-3 text-center">{{ $menu->launcherGroup?->label ?? '-' }}</td>
<td class="px-4 py-3">{{ $menu->sidebar_heading ?: '-' }}</td>
```

Update `menu-list.php` untuk eager load:
```php
->with(['parent', 'systemRoute', 'launcherGroup'])
```

#### MenuSeeder

File: `src/database/seeders/MenuSeeder.php`

Tambah parameter `launcherGroup` pada helper `createMenu()`:
```php
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
        // ...
        'launcher_group' => $launcherGroup,
    ]);
}
```

---

### M3 — Launcher Component (Core)

#### Launcher PHP

File: `src/resources/views/components/⚡launcher/launcher.php`

```php
new class extends Component
{
    public function render()
    {
        $activeGroups = LauncherGroup::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $menus = Menu::query()
            ->with(['systemRoute'])
            ->whereNotNull('launcher_group')
            ->whereIn('launcher_group', $activeGroups->pluck('key'))
            ->orderBy('sort_order')
            ->get();

        $filtered = $this->filterMenus($menus);
        $grouped = $filtered->groupBy('launcher_group');

        $ordered = collect();
        foreach ($activeGroups->pluck('key') as $group) {
            if ($grouped->has($group)) {
                $ordered->put($group, $grouped->get($group));
            }
        }

        return $this->view([
            'groupedMenus' => $ordered,
            'groupModels' => $activeGroups->keyBy('key'),
        ]);
    }

    public function isActive(Menu $menu): bool
    {
        return optional($menu->systemRoute)->route_name === request()->route()?->getName();
    }

    protected function filterMenus(Collection $menus): Collection
    {
        $permissionName = app(PermissionNameService::class);

        return $menus->map(function (Menu $menu) use ($permissionName) {
            if (! $menu->systemRoute) {
                return $menu;
            }

            $permission = $permissionName->fromRoute(
                $menu->systemRoute->route_name
            );

            return auth()->user()?->can($permission)
                ? $menu
                : null;
        })->filter()->values();
    }
};
```

Urutan group berasal dari `launcher_groups.sort_order`, bukan daftar `$groupOrder` hard-code. `groupModels` juga dikirim ke Blade agar view tidak melakukan query per group.

#### Launcher Blade

File: `src/resources/views/components/⚡launcher/launcher.blade.php`

```blade
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5">
    @foreach($groupedMenus as $group => $menus)
        @php
            $groupModel = $groupModels->get($group);
        @endphp
        <section class="relative flex h-full min-h-[212px] flex-col overflow-hidden rounded-2xl border bg-white p-4 sm:p-5">
            <div class="relative mb-4 flex shrink-0 items-center gap-3">
                @if($groupModel && $groupModel->icon)
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-blue-50 text-blue-600">
                        <i class="{{ $groupModel->icon }} text-xs"></i>
                    </span>
                @endif
                <h2 class="text-sm font-semibold text-gray-700">
                    {{ $groupModel?->label ?? ucfirst(str_replace('_', ' ', $group)) }}
                </h2>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                @foreach($menus as $menu)
                    <a
                        href="{{ $menu->systemRoute?->route_name ? route($menu->systemRoute->route_name) : '#' }}"
                        wire:navigate
                        class="group relative flex min-h-[92px] flex-col items-center justify-center gap-2 rounded-xl border border-slate-200/80 bg-white px-2 py-3 transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-sm"
                    >
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#122342]">
                            @if($menu->icon)
                                <i class="{{ $menu->icon }} text-[12px]"></i>
                            @else
                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            @endif
                        </div>
                        <span class="text-center text-[11px] font-semibold leading-snug text-slate-600 line-clamp-2">
                            {{ $menu->title }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
```

View launcher saat ini menggunakan card responsif, icon dan accent yang mengikuti group, serta tile menu. Pertahankan prinsip data-flow di atas: Blade menerima `groupModels` dari component; jangan menambahkan query `LauncherGroup` di dalam loop.

---

### M4 — Dashboard Integration

File: `src/resources/views/dashboard/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
    <div class="relative mx-auto max-w-[1600px]">
        <div class="mb-3 flex items-center gap-2 px-1">
            <span class="h-1.5 w-1.5 rounded-full bg-[#D4AF37]"></span>
            <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-slate-400">Menu Utama</span>
        </div>
        <div class="relative rounded-2xl border border-slate-200/90 bg-white/80 px-3 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-7">
            @livewire('components::launcher')
        </div>
    </div>
@endsection
```

Setelah login pengguna diarahkan ke dashboard, yang menampilkan Launcher.

---

### M5 — Launcher Group Manager

#### Route

File: `src/routes/web.php`

```php
Route::prefix('launcher-group')
    ->middleware(['auth', 'permission'])
    ->name('master.launcher-group.')
    ->group(function () {
        Route::livewire('/', 'pages::master.launcher-group-manager')
            ->name('list');
    });
```

#### Component PHP

File: `src/resources/views/pages/master/⚡launcher-group-manager/launcher-group-manager.php`

```php
new class extends Component
{
    public ?int $editingId = null;
    public string $key = '';
    public string $label = '';
    public ?string $icon = null;
    public int $sortOrder = 0;
    public bool $isActive = true;

    protected function rules(): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'isActive' => ['boolean'],
        ];

        if (! $this->editingId) {
            $rules['key'] = ['required', 'string', 'max:50', 'unique:launcher_groups,key'];
        }

        return $rules;
    }

    public function render()
    {
        return $this->view([
            'groups' => LauncherGroup::withCount('menus')->orderBy('sort_order')->get(),
        ])
        ->layout('layouts::app')
        ->title('Launcher Group Manager');
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = [
            'label' => $validated['label'],
            'icon' => $validated['icon'],
            'sort_order' => $validated['sortOrder'],
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId) {
            LauncherGroup::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Launcher group updated.');
        } else {
            $data['key'] = $validated['key'];
            LauncherGroup::create($data);
            session()->flash('success', 'Launcher group created.');
        }

        $this->resetForm();
    }

    public function edit(LauncherGroup $group): void
    {
        $this->editingId = $group->id;
        $this->label = $group->label;
        $this->icon = $group->icon;
        $this->sortOrder = $group->sort_order;
        $this->isActive = $group->is_active;
    }

    public function delete(LauncherGroup $group): void
    {
        if ($group->menus()->exists()) {
            session()->flash('error', 'Group tidak dapat dihapus karena masih digunakan oleh menu.');
            return;
        }

        $group->delete();
        session()->flash('success', 'Launcher group deleted.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->key = '';
        $this->label = '';
        $this->icon = null;
        $this->sortOrder = 0;
        $this->isActive = true;
    }
};
```

#### Component Blade

File: `src/resources/views/pages/master/⚡launcher-group-manager/launcher-group-manager.blade.php`

```blade
<x-form.card>
    <x-slot:title>
        <div class="flex flex-col">
            <span class="text-lg font-semibold">Launcher Group Manager</span>
            <span class="text-sm text-gray-500">Manage launcher categories for the dashboard</span>
        </div>
    </x-slot:title>

    @if (session('success'))
        <div class="mb-5 rounded-lg bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 rounded-lg bg-red-100 px-4 py-3 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-lg bg-red-100 px-4 py-3 text-red-700">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Form --}}
            <div class="md:col-span-1 bg-white p-4 rounded-lg shadow-sm">
                <div class="grid grid-cols-1 gap-4">
                    @if(!$editingId)
                    <x-form.input label="Key" name="key" wire:model="key" />
                    @error('key')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 -mt-2">Unique identifier, lowercase underscore.</p>
                    @endif

                    <x-form.input label="Label" name="label" wire:model="label" />
                    @error('label')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <x-form.input label="Icon (Tabler)" name="icon" wire:model="icon" />
                    @error('icon')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 -mt-2">Example: ti ti-receipt</p>

                    <x-form.input label="Sort Order" name="sortOrder" type="number" wire:model="sortOrder" />
                    @error('sortOrder')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center gap-2">
                        <input id="is_active" type="checkbox" wire:model="isActive" class="rounded border-gray-300">
                        <label for="is_active">Active</label>
                    </div>
                    @error('isActive')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <div class="flex gap-2">
                        <button type="button" wire:click="save" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                            {{ $editingId ? 'Update' : 'Create' }}
                        </button>
                        @if($editingId)
                            <button type="button" wire:click="cancelEdit" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                                Cancel
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- List --}}
            <div class="md:col-span-2">
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Key</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Label</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Icon</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Sort</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Active</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Menus</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($groups as $group)
                                <tr>
                                    <td class="px-4 py-3">{{ $group->key }}</td>
                                    <td class="px-4 py-3">{{ $group->label }}</td>
                                    <td class="px-4 py-3">{{ $group->icon ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $group->sort_order }}</td>
                                    <td class="px-4 py-3 text-center">{{ $group->is_active ? 'Yes' : 'No' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $group->menus_count }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-2">
                                            <button type="button" wire:click="edit({{ $group->id }})" class="rounded bg-yellow-500 px-3 py-1 text-sm text-white hover:bg-yellow-600">Edit</button>
                                            <button type="button" wire:click="delete({{ $group->id }})" wire:confirm="Yakin ingin menghapus group ini?" @if($group->menus_count > 0) disabled @endif class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 @if($group->menus_count > 0) opacity-50 cursor-not-allowed @endif">{{ $group->menus_count > 0 ? 'Used' : 'Delete' }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada launcher group.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-form.card>
```

#### Seeder

File: `src/database/seeders/LauncherGroupSeeder.php`

```php
LauncherGroup::updateOrCreate(['key' => 'transaksi'], [
    'label' => 'Transaksi', 'icon' => 'ti ti-receipt', 'sort_order' => 1, 'is_active' => true
]);
// ... master_data, laporan, sistem
```

File: `src/database/seeders/DatabaseSeeder.php`

```php
$this->call([
    MenuSeeder::class,
    LauncherGroupSeeder::class,
    SuperAdminSeeder::class,
]);
```

---

### M6 — Config Export/Import

#### Export

File: `src/app/Console/Commands/FrameworkConfigExportCommand.php`

Mapping menu export menyertakan heading hanya untuk root menu:

```php
$menus = Menu::query()
    ->with('systemRoute')
    ->orderBy('sort_order')
    ->get()
    ->map(function (Menu $menu) {
        $parent = $menu->parent()->with('systemRoute')->first();

        return [
            'route' => $menu->systemRoute?->route_name,
            'title' => $menu->title,
            'sidebar_heading' => $menu->parent_id === null
                ? $menu->sidebar_heading
                : null,
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

$launcherGroups = LauncherGroup::query()
    ->orderBy('sort_order')
    ->get()
    ->map(fn (LauncherGroup $group) => [
        'key' => $group->key,
        'label' => $group->label,
        'icon' => $group->icon,
        'sort_order' => $group->sort_order,
        'is_active' => $group->is_active,
    ])
    ->values()
    ->toArray();

$data = [
    // ...
    'launcher_groups' => $launcherGroups,
];
```

#### Import

File: `src/app/Console/Commands/FrameworkConfigImportCommand.php`

Tambah pass baru sebelum menu sync:

```php
$this->call('framework:route-sync', ['--safe' => true]);
$this->call('framework:permission-sync', ['--safe' => true]);

$launcherGroups = $data['launcher_groups'] ?? [];

foreach ($launcherGroups as $groupData) {
    LauncherGroup::firstOrCreate(
        ['key' => $groupData['key']],
        [
            'label' => $groupData['label'],
            'icon' => $groupData['icon'] ?? null,
            'sort_order' => $groupData['sort_order'] ?? 0,
            'is_active' => $groupData['is_active'] ?? true,
        ]
    );
}
```

Contoh di atas menunjukkan record menu yang diekspor; bagian launcher group berikutnya tetap menggunakan struktur field yang sama seperti source.

Import route/permission bersifat additive. Launcher group existing dibuat dengan `firstOrCreate`, bukan `updateOrCreate`, sehingga label/icon/order/status target tidak ditimpa. `sidebar_heading` hanya diekspor untuk root. Pada root yang sudah ada, importer hanya mengisi heading jika export berisi teks dan field target masih kosong; submenu tidak menerima heading.

Existing menu dicari berdasarkan route lalu title; child juga dicocokkan dengan parent/title agar menu tanpa route tidak terduplikasi. Import bukan full overwrite/sync untuk semua atribut.

---

### M7 — Sidebar Navigation and UX

File: `src/resources/views/components/⚡sidebar/sidebar.blade.php`

Akar masalah: `wire:current` dapat mencocokkan prefix URL, sehingga child route perlu dicocokkan menggunakan path yang tepat. Accordion juga tidak lagi memakai `wire:click`: perubahan expand/collapse bersifat lokal di Alpine agar tidak menunggu request server. Daftar menu dan permission tetap difilter di server.

```blade
<div x-data="{ currentPath: window.location.pathname, init() { document.addEventListener('livewire:navigated', () => { this.currentPath = window.location.pathname; }); } }">
    @foreach($menus as $menu)
        @if(filled($menu->sidebar_heading))
            <p>{{ $menu->sidebar_heading }}</p>
        @endif

        @if($menu->children->isNotEmpty())
            <div x-data="{ expanded: @js(in_array($menu->id, $openedMenus)) }"
                 x-on:sidebar-active-menus.window="if ($event.detail.openedMenus.includes({{ $menu->id }})) expanded = true">
                <button type="button"
                        aria-controls="sidebar-submenu-{{ $menu->id }}"
                        @click="expanded = !expanded"
                        :aria-expanded="expanded">
                    {{ $menu->title }}
                </button>
                <div id="sidebar-submenu-{{ $menu->id }}"
                     class="grid grid-rows-[0fr] -translate-y-1 opacity-0 transition-all duration-200"
                     :class="expanded ? 'grid-rows-[1fr] translate-y-0 opacity-100' : ''"
                     aria-hidden="true"
                     :aria-hidden="!expanded"
                     inert
                     :inert="!expanded">
                    <div class="min-h-0 overflow-hidden">
                        @foreach($menu->children as $child)
                            @if($child->systemRoute)
                                <a href="{{ route($child->systemRoute->route_name) }}"
                                   wire:navigate
                                   data-path="{{ parse_url(route($child->systemRoute->route_name), PHP_URL_PATH) }}"
                                   x-bind:class="'group ml-4 flex h-10 items-center rounded-lg border-l-2 px-3 text-sm transition-all duration-200 ' + ($el.dataset.path === currentPath ? 'bg-amber-500/20 text-amber-300 font-semibold border-amber-400' : 'border-transparent text-slate-300 hover:translate-x-0.5 hover:border-amber-400/60 hover:bg-white/[0.05] hover:text-white')"
                                   :aria-current="$el.dataset.path === currentPath ? 'page' : null">
                                    {{ $child->title }}
                                </a>
                            @else
                                <span>{{ $child->title }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
```

Submenu memakai transisi grid/opacity, tombol parent memiliki `aria-expanded`, dan child tanpa route dirender sebagai teks non-link. Posisi scroll pada `layouts/sidebar.blade.php` disimpan di `sessionStorage` dan dipulihkan saat sidebar dibuka, setelah Livewire navigation, serta setelah state parent aktif diperbarui.

---

## 31. Troubleshooting Launcher

### Field Key tidak muncul saat Edit

**Penyebab:** Field Key disembunyikan sepenuhnya saat edit untuk mencegah perubahan key yang bisa memutus relasi.

**Solusi:** Ini adalah expected behavior. Saat edit, key tidak perlu diubah.

### Sort Order selalu 0

**Penyebab:** Mismatch antara camelCase property (`sortOrder`) dan snake_case column (`sort_order`).

**Solusi:** Di `save()`, mapping manual:
```php
$data = ['sort_order' => $validated['sortOrder']];
```

### Sidebar highlight salah saat klik child route

**Penyebab:** `wire:current` match by URL prefix.

**Solusi:** Gunakan Alpine.js exact path matching seperti di bagian 30.

### Sidebar kembali scroll ke atas setelah memilih menu

**Penyebab:** Livewire navigation memperbarui component/sidebar saat halaman berganti.

**Solusi:** Sidebar menyimpan scrollTop menu di `sessionStorage` dan mengembalikannya saat dibuka, setelah event `livewire:navigated`, serta setelah event update parent aktif. Jika container sidebar diganti/diubah, pastikan `x-ref="menuScroll"`, event `scroll`, dan key session storage `pops-sidebar-menu-scroll-top` tetap konsisten di `layouts/sidebar.blade.php`.

### Menu duplikat atau pesan route conflict saat config import

**Penyebab:** Menu di target mempunyai title dan route berbeda sekaligus, sehingga tidak ada stable ID lintas environment untuk mencocokkannya.

**Solusi:** Periksa pesan conflict dan mapping menu target sebelum import. Import mempertahankan record yang dapat dicocokkan; konfigurasi yang tidak cocok dapat dianggap sebagai menu baru. Hindari mengganti title dan route sekaligus tanpa rencana pemetaan.

### Delete group gagal dengan error "masih digunakan"

**Penyebab:** Group masih memiliki menu yang menggunakannya.

**Solusi:** Hapus/ubah menu tersebut terlebih dahulu sebelum menghapus group.

---

## 32. Checklist Deploy Project Baru

1. Copy seluruh struktur folder
2. Setup `.env`
3. Setup database
4. Jalankan `php artisan migrate`
5. Review seluruh seeder; jalankan `db:seed` hanya pada database baru/kosong
6. Jalankan `php artisan framework:route-sync --safe`
7. Jalankan `php artisan framework:permission-sync --safe`
8. Setup role & permission awal
9. Setup menu di `master.menu.list`
10. Setup launcher group di `master.launcher-group.list`
11. Assign menu ke launcher group
12. Test seluruh halaman

Untuk upgrade pada server yang sudah berisi data, backup database, periksa `php artisan migrate:status`, lalu jalankan hanya migration yang pending. Gunakan `framework:config-import` untuk konfigurasi; jangan jalankan `db:seed` sebagai pengganti import.

---

## 33. Catatan Penting untuk Project Baru

1. **Jangan hardcode menu** — selalu lewat database + route sync
2. **Jangan buat permission baru sembarang** — ikuti `module.resource.action`
3. **Gunakan `PermissionNameService`** sebagai pusat mapping
4. **Launcher adalah tambahan** — tidak mengubah sistem permission yang ada
5. **`framework-data.json` adalah snapshot transfer konfigurasi**; import bersifat additive dan mempertahankan customisasi target yang sudah ada
6. **Key launcher group tidak boleh diubah** setelah create — disembunyikan di form edit untuk keamanan
7. **Semua component pakai Livewire MFC pattern** (`new class extends Component`)
8. **Naming convention:**
   - Class: PascalCase
   - Method/property: camelCase
   - Database: snake_case
   - Route: dot.notation

---

## 34. Quick Reference

```bash
# Framework
# Safe additive sync; import juga menjalankan keduanya dalam mode ini
php artisan framework:route-sync --safe
php artisan framework:permission-sync --safe
php artisan framework:config-export
php artisan framework:config-import
php artisan test

# Livewire
php artisan make:livewire pages::master.nama-component --mfc

# Database
php artisan migrate
# Hanya setelah meninjau seluruh seeder dan memastikan database baru/kosong
php artisan db:seed
```

Jangan jalankan `migrate:fresh`, `migrate:refresh`, atau `db:seed` pada database berisi data tanpa backup dan persetujuan/peninjauan yang sesuai. `MenuSeeder` menghapus seluruh menu dan `LauncherGroupSeeder` dapat menimpa group yang telah dikustomisasi.

---

*Dokumentasi ini adalah panduan implementasi yang rinci untuk dipelajari dan digunakan kembali. Source code tetap menjadi acuan perilaku runtime; perubahan kode framework perlu diikuti pembaruan dokumentasi ini.*
