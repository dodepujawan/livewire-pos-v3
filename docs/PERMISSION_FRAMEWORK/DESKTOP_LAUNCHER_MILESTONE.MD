# DESKTOP LAUNCHER — MILESTONE & IMPLEMENTASI

## 1. ANALISA singkat

Database `menus` saat ini **cukup** untuk mendukung Desktop Launcher dengan penambahan **1 kolom**:

| Kolom | Tipe | Default | Fungsi |
|-------|------|---------|--------|
| `launcher_group` | string(50) | NULL | Kategori launcher: `transaksi`, `master_data`, `laporan`, `sistem` |

Kolom ini mengontrol grouping Launcher. Setiap menu dengan `launcher_group != NULL` otomatis muncul di Launcher.

### 4 Kategori Launcher (berdasarkan referensi UI)

| Nilai `launcher_group` | Label | Contoh Menu |
|------------------------|-------|-------------|
| `transaksi` | Transaksi | POS, Penjualan, Pembelian, Retur |
| `master_data` | Master Data | Barang, Supplier, Customer, Kategori |
| `laporan` | Laporan | Laba Rugi, Stok, Penjualan, Neraca |
| `sistem` | Sistem | Pengaturan, User, Backup, Log |

### Perubahan pendekatan (disetujui)
- **Sidebar tetap dipakai** sebagai navigasi utama.
- **Launcher menggantikan konten dashboard**, bukan menggantikan sidebar.
- Setelah login pengguna diarahkan ke dashboard, yang menampilkan Launcher.
- Jika pengguna klik menu Dashboard dari sidebar, halaman dashboard menampilkan Launcher.
- Tidak ada mode toggle Sidebar/Launcher.

### Peraturan penting
- Sidebar dan Launcher adalah **dua renderer** dari data menu yang sama.
- `launcher_group` **hanya** mengontrol grouping Launcher, tidak mempengaruhi Sidebar.
- Permission checking menggunakan logic yang **sama persis** dengan Sidebar (`PermissionNameService::fromRoute` + `auth()->user()->can()`).
- Tidak ada route baru, tidak ada permission baru, tidak ada sistem menu kedua.
- Urutan item di dalam group diambil dari `sort_order` (kolom existing).

---

## 2. FILES TERDAMPAK

### A. File yang perlu DIUBAH

| # | File | Perubahan |
|---|------|-----------|
| 1 | `src/database/migrations/YYYY_MM_DD_HHMMSS_add_launcher_group_to_menus_table.php` | **BARU** — migrasi tambah kolom `launcher_group` |
| 2 | `src/app/Models/Menu.php` | Tambah `launcher_group` ke fillable & cast |
| 3 | `src/resources/views/pages/master/⚡menu-create/menu-create.php` | Tambah field `launcher_group` (select dropdown) |
| 4 | `src/resources/views/pages/master/⚡menu-create/menu-create.blade.php` | Tambah UI dropdown 4 kategori |
| 5 | `src/resources/views/pages/master/⚡menu-edit/menu-edit.php` | Tambah field `launcher_group` |
| 6 | `src/resources/views/pages/master/⚡menu-edit/menu-edit.blade.php` | Tambah UI dropdown 4 kategori |
| 7 | `src/resources/views/pages/master/⚡menu-list/menu-list.blade.php` | Tambah kolom "Group Launcher" di tabel |
| 8 | `src/resources/views/components/⚡launcher/launcher.php` | **BARU** — Livewire MFC component |
| 9 | `src/resources/views/components/⚡launcher/launcher.blade.php` | **BARU** — grid layout by group |
| 10 | `src/resources/views/dashboard/index.blade.php` | Tambah `@livewire('components::launcher')` |
| 11 | `src/database/seeders/MenuSeeder.php` | Tambah parameter `launcherGroup` pada helper `createMenu()` |
| 12 | `src/app/Console/Commands/FrameworkConfigExportCommand.php` | Export `launcher_group` |
| 13 | `src/app/Console/Commands/FrameworkConfigImportCommand.php` | Import `launcher_group` |
| 14 | `src/database/framework-data.json` | Akan ter-update otomatis saat export |

### B. File yang hanya perlu DIBACA (tidak diubah)

| # | File | Alasan |
|---|------|--------|
| 1 | `src/app/Services/PermissionNameService.php` | Reference untuk permission mapping |
| 2 | `src/app/Http/Middleware/PermissionMiddleware.php` | Confirm tidak ada perubahan yang dibutuhkan |
| 3 | `src/app/Support/AuthorizesRoute.php` | Reference untuk in-component authorization |
| 4 | `src/app/Console/Commands/RouteSyncCommand.php` | Confirm tidak ada perubahan yang dibutuhkan |
| 5 | `src/app/Console/Commands/PermissionSyncCommand.php` | Confirm tidak ada perubahan yang dibutuhkan |
| 6 | `src/app/Models/SystemRoute.php` | Reference model |
| 7 | `src/resources/views/components/⚡sidebar/sidebar.php` | Reference untuk filterMenus() logic yang akan di-reuse |
| 8 | `src/resources/views/components/⚡sidebar/sidebar.blade.php` | Reference untuk styling pattern |
| 9 | `src/resources/views/layouts/app.blade.php` | Confirm tidak ada perubahan struktur yang dibutuhkan |
| 10 | `src/app/Http/Controllers/Auth/LoginController.php` | Sudah redirect ke dashboard setelah login |
| 11 | `src/resources/views/layouts/navbar.blade.php` | Sudah ada link dashboard (icon home) |
| 12 | `src/resources/views/layouts/sidebar.blade.php` | Tidak diubah |

---

## 3. DATABASE TABLE TERDAMPAK

### `menus` — 1 kolom baru

```sql
ALTER TABLE menus ADD COLUMN launcher_group VARCHAR(50) NULL AFTER sort_order;
```

**Dampak:**
- Semua menu existing: `launcher_group = NULL` — tidak muncul di launcher sampai diisi.
- Kolom nullable: menu yang tidak untuk launcher biarkan NULL.
- Tidak mempengaruhi relasi `parent_id`, `system_route_id`, `is_sidebar`.
- Tidak mempengaruhi Sidebar.

---

## 4. APAKAH MIGRASI DIPERLUKAN?

**YA.** 1 migration baru untuk menambahkan kolom `launcher_group` pada tabel `menus`.

---

## 5. PERUBAHAN MINIMAL YANG DISARANKAN

1. Tambah kolom `launcher_group VARCHAR(50) NULL` pada `menus`.
2. Tambah dropdown 4 kategori pada Menu Create/Edit.
3. Buat component Livewire baru `⚡launcher` yang query `whereNotNull('launcher_group')`, group by `launcher_group`, filter permission sama seperti sidebar.
4. Tambah `@livewire('components::launcher')` ke `dashboard/index.blade.php`.
5. Update Export/Import untuk kolom baru.

TIDAK ada perubahan pada:
- Permission engine
- Route sync
- Permission sync
- Sidebar component
- Layout structure (app.blade.php)
- Navbar (tidak perlu toggle mode)
- LoginController (sudah redirect ke dashboard)

---

## 6. DAMPAK TERHADAP SIDEBAR EXISTING

**NOL.** Sidebar terus bekerja persis seperti sekarang.

---

## 7. DAMPAK TERHADAP PERMISSION SYSTEM

**NOL.** Launcher menggunakan logic permission yang sama:
- Sama `PermissionNameService::fromRoute()`
- Sama `auth()->user()?->can($permission)`
- Item tanpa permission tetap tersembunyi di Launcher.

Tidak ada permission baru yang dibuat.

---

## 8. DAMPAK TERHADAP CONFIG EXPORT/IMPORT

**MINOR.** Kedua command perlu menambahkan 1 field baru pada array menu:
- Export: tambah `launcher_group`
- Import: baca dan set `launcher_group`

Backward compatible: file JSON lama tanpa field ini akan fallback ke `NULL` (tidak muncul di launcher).

---

## 9. MEGA PLAN IMPLEMENTASI

### M1 — Database & Model
- [x] Buat migration: `add_launcher_group_to_menus_table`
- [x] Update `Menu.php`: tambah `launcher_group` ke fillable + cast
- [x] Jalankan migration

### M2 — Menu Management UI
- [x] Update `menu-create.php` + blade: tambah dropdown `launcher_group`
- [x] Update `menu-edit.php` + blade: sama
- [x] Update `menu-list.blade.php`: tambah kolom "Group Launcher"
- [x] Update `MenuSeeder`: tambah parameter `launcherGroup` pada helper `createMenu()`

### M3 — Launcher Component (Core)
- [x] Buat `resources/views/components/⚡launcher/launcher.php`
  - Query: `Menu::whereNotNull('launcher_group')->with(['systemRoute', 'parent'])->orderBy('sort_order')->get()`
  - Group hasil query by `launcher_group`
  - `isActive()` + `filterMenus()` sama seperti sidebar
- [x] Buat `launcher.blade.php`
  - Iterate 4 group dalam urutan tetap: Transaksi → Master Data → Laporan → Sistem
  - Setiap group: header label + icon + grid icon-card (3-4 kolom desktop, 2 tablet, 1 mobile)
  - Setiap card: icon + title + hover effect
  - `wire:navigate` untuk navigasi
  - Group kosong: tampilkan empty state atau hide entirely

### M4 — Dashboard Integration
- [x] Update `dashboard/index.blade.php`: tambah `@livewire('components::launcher')`
- [x] Confirm `LoginController::login()` redirect ke dashboard (sudah ada)
- [x] Confirm navbar sudah ada link ke dashboard (icon home)
- [x] Sidebar tidak diubah

### M5 — Config Sync Update
- [ ] Update `FrameworkConfigExportCommand`: tambah field `launcher_group`
- [ ] Update `FrameworkConfigImportCommand`: baca dan set `launcher_group`
- [ ] Test export → import cycle

### M6 — Testing & Polish
- [ ] Test permission filter di Launcher
- [ ] Test responsive grid (desktop/tablet/mobile)
- [ ] Test dengan user yang punya partial permission
- [ ] Verify sidebar masih works 100%
- [ ] Verify config export/import aman
- [ ] Verify setelah login pengguna melihat launcher di dashboard

---

## 10. RISIKO / REGRESI

| Risiko | Level | Mitigasi |
|--------|-------|----------|
| User kebingungan antara Sidebar dan Launcher | Rendah | Sidebar tetap sebagai navigasi, Launcher sebagai konten dashboard |
| Menu muncul di Launcher tanpa permission filter | Rendah | Reuse logic yang sama dengan sidebar |
| Group launcher kosong tampil aneh | Rendah | Hide group jika tidak ada item yang visible |
| Existing menu tanpa `launcher_group` | Tidak ada | NULL = tidak muncul di launcher, aman |
| Config import menimpa `launcher_group` yang sudah di-set manual | Sedang | Import menggunakan `updateOrCreate` logic, field NULL dipertahankan jika tidak ada di JSON |

---

## 11. SKEMA GRID LAYOUT (Visual Reference — 4 Group)

```
+--------------------------------------------------+
|  [☰] POPS Pro    [🏠] [Master] [Transaksi] | 👤 |
+--------------------------------------------------+
|                                                    |
|  DASHBOARD                                         |
|                                                    |
|  TRANSAKSI                                         |
|  +------+  +------+  +------+  +------+           |
|  | 🛒    |  | 📋    |  | ↩️    |  | 💰    |           |
|  |POS    |  |Pen-   |  |Retur |  |Kasir  |           |
|  |      |  |jualan |  |      |  |      |           |
|  +------+  +------+  +------+  +------+           |
|                                                    |
|  MASTER DATA                                       |
|  +------+  +------+  +------+  +------+           |
|  | 📦    |  | 👥    |  | 🏷️    |  | 🏪    |           |
|  |Barang |  |Customer|  |Kategori|  |Supplier|        |
|  |      |  |      |  |      |  |      |           |
|  +------+  +------+  +------+  +------+           |
|                                                    |
|  LAPORAN                                           |
|  +------+  +------+  +------+  +------+           |
|  | 📊    |  | 💹    |  | 📋    |  | 🏦    |           |
|  |Laba   |  |Stok   |  |Neraca |  |Bank   |           |
|  |Rugi   |  |      |  |      |  |      |           |
|  +------+  +------+  +------+  +------+           |
|                                                    |
|  SISTEM                                            |
|  +------+  +------+  +------+                     |
|  | ⚙️    |  | 👤    |  | 💾    |                     |
|  |Peng-  |  |User   |  |Backup |                     |
|  |aturan |  |      |  |      |                     |
|  +------+  +------+  +------+                     |
|                                                    |
+--------------------------------------------------+
```

Setiap card:
- Icon besar (Tabler icon, 32-40px)
- Title di bawah icon
- Hover: scale + glow
- Active: border accent / background highlight
- Disabled (no permission): opacity-50 + pointer-events-none
- Group header: icon + label, subtle divider

---

## 12. TOLAK UKUR SUKSES

1. Admin bisa pilih "Group Launcher" (4 kategori) pada Menu Create/Edit, atau NULL untuk tidak muncul.
2. Setelah login pengguna langsung melihat Launcher di Dashboard.
3. Ketika pengguna klik Dashboard dari Sidebar, halaman dashboard menampilkan Launcher.
4. Permission filter di Launcher sama persis dengan Sidebar.
5. Tidak ada route baru yang dibuat untuk Launcher.
6. Config export/import menyertakan kolom `launcher_group`.
7. Zero regression pada Sidebar.
8. Launcher menampilkan 4 group yang bisa dikustomisasi via menu management.

---

*Document updated: 2026-08-18 — pendekatan diubah: Launcher menjadi konten Dashboard, Sidebar tetap sebagai navigasi utama*
