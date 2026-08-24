# FUTURE TASK — DESKTOP LAUNCHER

Saya ingin menambahkan fitur "Desktop Launcher" ke framework
navigasi yang sudah ada.

JANGAN langsung coding.

Tujuan:
Aplikasi nantinya memiliki dua mode navigasi:
1. Sidebar Mode — struktur hierarchical berdasarkan parent menu.
2. Desktop Launcher Mode — grid/group icon untuk akses cepat.

Desktop Launcher TIDAK boleh membuat sistem permission atau
route baru. Launcher hanya merupakan renderer/navigation layer
lain dari menu yang sudah ada.

PENTING:
Parent menu sidebar TIDAK otomatis dianggap sebagai kategori
Desktop Launcher.

Contoh:
Sidebar:
Transaksi
└── Penjualan
    ├── Invoice
    ├── Invoice Unggulan
    └── Retur

Launcher dapat mengelompokkan:
TRANSAKSI
├── Invoice
├── Invoice Unggulan
└── Retur

Jadi struktur sidebar dan grouping launcher dapat berbeda.

==================================================
SEBELUM CODING — ANALISA PROJECT
==================================================

Periksa dan jelaskan file yang berkaitan dengan:

1. Menu Model
   - src/app/Models/Menu.php

2. System Route Model
   - src/app/Models/SystemRoute.php

3. Menu migration
   - database/migrations/*create_menus_table.php

4. System route migration
   - database/migrations/*create_system_routes_table.php

5. Menu management Livewire components
   - resources/views/pages/master/⚡menu-list/
   - resources/views/pages/master/⚡menu-create/
   - resources/views/pages/master/⚡menu-edit/

6. Sidebar component
   - resources/views/components/⚡sidebar/
   - cek seluruh file di dalam component tersebut

7. Layout utama
   - resources/views/layouts/

8. Route definition
   - routes/web.php

9. Permission system
   - app/Services/PermissionNameService.php
   - app/Services/PermissionMatrixService.php
   - app/Http/Middleware/PermissionMiddleware.php
   - app/Support/AuthorizesRoute.php
   - app/Console/Commands/PermissionSyncCommand.php
   - app/Console/Commands/RouteSyncCommand.php

10. Framework configuration sync
   - app/Console/Commands/FrameworkConfigExportCommand.php
   - app/Console/Commands/FrameworkConfigImportCommand.php
   - database/framework-data.json

11. Menu seeder
   - database/seeders/MenuSeeder.php

12. Project documentation
   - PROJECT_RULES.md / PROJECT_RULES_v2.md
   - FRAMEWORK_PERMISSION_DOCUMENTATION.md
   - docs/CHANGELOG.md
   - MODULE_*.md yang berkaitan dengan menu/navigation

==================================================
ANALISA DATABASE
==================================================

Periksa struktur tabel menus dan system_routes.

Jelaskan apakah struktur database saat ini sudah cukup untuk
Desktop Launcher.

Jangan membuat migration.

Jika diperlukan perubahan database, jelaskan:

- kolom yang diperlukan
- alasan
- dampak terhadap sidebar
- dampak terhadap Menu Management
- dampak terhadap Seeder
- dampak terhadap framework-config export/import
- dampak terhadap existing data

Tunggu approval sebelum membuat migration.

==================================================
ANALISA ARSITEKTUR
==================================================

Tentukan apakah Desktop Launcher dapat dibuat hanya dengan
menggunakan struktur menu yang sekarang.

Jika tidak, cari perubahan MINIMAL yang diperlukan.

PRIORITAS:
1. Jangan merombak Permission Engine.
2. Jangan merombak Route Sync.
3. Jangan merombak Permission Sync.
4. Jangan membuat sistem menu kedua.
5. Jangan membuat permission khusus launcher.
6. Jangan membuat route baru hanya untuk launcher.
7. Gunakan data menu yang sama.
8. Sidebar harus tetap bekerja seperti sekarang.
9. Permission existing harus tetap berlaku.
10. Framework core jangan diubah jika kebutuhan dapat diselesaikan
    di level navigation/menu.

==================================================
DESAIN YANG DIHARAPKAN
==================================================

Secara konsep:

                    MENU DATABASE
                          │
             ┌────────────┴────────────┐
             ↓                         ↓
       SIDEBAR RENDERER          LAUNCHER RENDERER
             ↓                         ↓
       Sidebar UI                Desktop Launcher

Keduanya menggunakan route dan permission yang sama.

Launcher harus mendukung:
- group/category
- icon
- title
- sort order
- permission filtering
- route navigation
- responsive desktop layout

Gunakan:
- Livewire 4
- Livewire MFC
- Volt
- TailwindCSS
- Alpine.js
- existing UI components

Maksimalkan:
resources/views/components/

Jangan membuat duplicate UI component jika component existing
dapat digunakan.

==================================================
OUTPUT YANG SAYA MAU
==================================================

Sebelum coding, berikan:

1. File yang perlu diubah.
2. File yang hanya perlu dibaca.
3. Database table yang terdampak.
4. Apakah migration diperlukan.
5. Perubahan minimal yang disarankan.
6. Dampak terhadap sidebar existing.
7. Dampak terhadap permission system.
8. Dampak terhadap config export/import.
9. Mega Plan implementasi.
10. Risiko/regression yang mungkin terjadi.

JANGAN coding sebelum saya memberikan approval.