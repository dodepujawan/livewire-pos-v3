# DESKTOP LAUNCHER — PROGRESS

## Selesai
- Migration `2026_08_18_150000_add_launcher_group_to_menus_table` dibuat dan **berhasil dijalankan**.
- Model `Menu.php` diperbarui: `launcher_group` ditambahkan ke `$fillable`.
- Menu Create/Edit/List: field `launcher_group` ditambahkan.
- `MenuSeeder` diperbarui untuk mendukung `launcher_group`.
- Livewire component `⚡launcher` (`launcher.php` + `launcher.blade.php`) dibuat.
- Dashboard (`dashboard/index.blade.php`) sekarang menampilkan launcher.
- Login redirect ke dashboard (`LoginController::login` sudah ada).
- Sidebar tidak diubah.
- Navbar tidak perlu toggle mode; launcher adalah isi dashboard.
- Config export/import: `launcher_group` sudah ditambahkan.
- Migration `2026_08_18_160000_create_launcher_groups_table` dibuat dan **berhasil dijalankan**.
- Model `LauncherGroup.php` dibuat.
- `LauncherGroupSeeder` dibuat dengan 4 group default.
- Launcher styling diperbarui: card lebih kontras, hover effect lebih jelas, group header dengan icon.
- Launcher group jadi dinamis: bisa add/edit/hapus dari UI.
- Route `master.launcher-group.list` ditambahkan mengikuti pattern `module.resource.action`.
- Menu List: tombol "Launcher Groups" ditambahkan untuk navigate ke manager.
- **Export/import `launcher_groups` sudah diimplementasikan dan di-test:**
  - Export menambahkan section `launcher_groups` ke JSON
  - Import menambahkan pass baru sebelum menu sync, menggunakan `updateOrCreate` berdasarkan `key`
  - Import aman: tidak menghapus group yang tidak ada di JSON, hanya update/create
  - Tested: export → import berhasil, 4 groups synced, 0 menus created (all skipped as expected)

## Catatan pendekatan
- Sidebar tetap dipakai sebagai navigasi utama.
- Launcher menggantikan konten dashboard (bukan menggantikan sidebar).
- Setelah login pengguna diarahkan ke dashboard, yang menampilkan Launcher.
- Jika pengguna klik menu Dashboard dari sidebar, halaman dashboard menampilkan Launcher.
- Tidak ada mode toggle Sidebar/Launcher.
- Launcher groups dikelola via `LauncherGroup` model + manager UI.
- Nama route mengikuti pattern `module.resource.action`: `master.launcher-group.list`.
- Component path: `resources/views/pages/master/⚡launcher-group-manager/`.
- Property naming: camelCase (`sortOrder`, `isActive`).

## Perbaikan terakhir
- Component `launcher-group-manager` dipindah ke path yang benar: `resources/views/pages/master/⚡launcher-group-manager/`
- Route diubah dari `system.launcher.groups` ke `master.launcher-group.list` mengikuti pattern `module.resource.action`
- Property naming diikuti camelCase (`sortOrder`, `isActive`) sesuai project rules
- Link di Menu List diupdate ke route yang benar
- **Menu List UX improved**: kolom "Launcher Group" sekarang menampilkan label group (misal "Transaksi") instead of raw key ("transaksi")
  - Tambah relasi `launcherGroup()` di `Menu.php`
  - Tambah eager load `launcherGroup` di `menu-list.php`
  - Update blade untuk menampilkan label
- **Sidebar child active state fix**: replaced `wire:current` with Alpine.js exact-path matching to prevent prefix-matching bug where `/system/roles` incorrectly highlighted `/system` parent
- **Launcher Group Manager UX fix**: Key field now only shows in create mode, hidden in edit mode to prevent accidental key changes that could break menu relations
  - Validation rules updated to only require key when creating, not when editing
  - Delete protection added: groups with associated menus cannot be deleted
- **Launcher visual redesign**:
  - Removed 'Dashboard' and 'Launcher' headings for cleaner look
  - Changed grid from 5 columns to 6 columns (lg:grid-cols-6) for more compact layout
  - Smaller icons: h-9 w-9 with text-base instead of h-10 w-10 with text-lg
  - Smaller text: text-[11px] instead of text-xs
  - Tighter spacing: gap-3 instead of gap-4, space-y-8 between groups
  - Uniform card design: p-3 padding, rounded-xl, consistent hover effects
  - Added `x-data` with `currentPath` tracking
  - Child items use `x-bind:class` with exact `window.location.pathname` comparison
  - Parent items remain using `isActive()` and `hasActiveChild()`

## Sedang dikerjakan
- M6 — Testing & polish.
