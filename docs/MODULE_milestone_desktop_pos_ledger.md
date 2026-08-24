# MODULE — Log Pengerjaan Desktop POS & Ledger

> Log progress per tahap + hints untuk AI selanjutnya.
> Referensi utama: `docs/MEGA_PLAN_pos_ledger.md`

---

## Status: 🔄 DALAM PENGERJAAN

| Tahap | Nama | Status | Catatan |
|-------|------|--------|---------|
| 0 | Persiapan | ✅ | Mega Plan & dokumentasi selesai |
| 1 | Multi-Cabang | ✅ | Fondasi: tabel cabang, barang_stok, model, MFC, routes |
| 2 | Master Barang & Satuan | ✅ | Tambah harga_beli, is_default |
| 3 | POS Penjualan | 🔄 | Migration + model + transaksi-create + cancel |
| 4 | Cash Ledger | 🔄 | kas_mutasi + laporan kas + refund cancel |
| 5 | Pembelian & HPP | ⏳ | Modul pembelian barang |
| 6 | Jurnal Akuntansi | ⏳ | Akun, jurnal, jurnal_detail |
| 7 | Piutang, Hutang & Pajak | ⏳ | Piutang, hutang, pelunasan, PPN |
| 8 | Laporan Gabungan & Final | ⏳ | Semua laporan + test |

---

## Hints untuk AI Selanjutnya

1. **WAJIB baca** `docs/MEGA_PLAN_pos_ledger.md` sebelum mulai coding
2. **WAJIB baca** `docs/PROJECT_RULES_v2.md` untuk aturan project
3. **WAJIB baca** `docs/database_pos_ledger.md` untuk skema database
4. Migration **tidak boleh dibuat tanpa approval** programmer
5. Setelah selesai 1 tahap, update tabel status di atas + tulis catatan di bawah

---

## Catatan Pengerjaan

### Tahap 1 — Multi-Cabang (2026-08-24)

**Selesai:**
- 3 migration: `create_cabang_table`, `create barang_stok_table`, `add_cabang_id_to_users_table`
- Model `Cabang` & `BarangStok` baru
- Update Model `Barang` (tambah `stokPerCabang()`, `stokDiCabang()`)
- Update Model `User` (tambah `cabang()` relation, `cabang_id` fillable)
- Seeder `CabangSeeder` — buat cabang default "PUSAT" + migrasi stok existing ke `barang_stok`
- 3 MFC Components: `cabang-list`, `cabang-create`, `cabang-edit`
- 3 routes: `master.cabang.list`, `.create`, `.edit`
- Permission auto-sync: `master.cabang.view`, `.create`, `.update`, `.delete`
- Additional permission: `master.cabang.delete`

**File berubah:**
- `src/database/migrations/2026_08_24_000001_create_cabang_table.php` (BARU)
- `src/database/migrations/2026_08_24_000002_create barang_stok_table.php` (BARU)
- `src/database/migrations/2026_08_24_000003_add_cabang_id_to_users_table.php` (BARU)
- `src/app/Models/Cabang.php` (BARU)
- `src/app/Models/BarangStok.php` (BARU)
- `src/app/Models/Barang.php` (UPDATE)
- `src/app/Models/User.php` (UPDATE)
- `src/database/seeders/CabangSeeder.php` (BARU)
- `src/database/seeders/DatabaseSeeder.php` (UPDATE)
- `src/resources/views/pages/master/⚡cabang-list/cabang-list.php` (BARU)
- `src/resources/views/pages/master/⚡cabang-list/cabang-list.blade.php` (BARU)
- `src/resources/views/pages/master/⚡cabang-create/cabang-create.php` (BARU)
- `src/resources/views/pages/master/⚡cabang-create/cabang-create.blade.php` (BARU)
- `src/resources/views/pages/master/⚡cabang-edit/cabang-edit.php` (BARU)
- `src/resources/views/pages/master/⚡cabang-edit/cabang-edit.blade.php` (BARU)
- `src/routes/web.php` (UPDATE)

**Migration yang dibuat:**
- `2026_08_24_000001_create_cabang_table`
- `2026_08_24_000002_create barang_stok_table`
- `2026_08_24_000003_add_cabang_id_to_users_table`

**Known Issues / TODO:**
- Menu sidebar belum ada entry untuk "Master Cabang" (perlu tambah manual via MenuSeeder atau UI)
- `barang.stok` existing masih ada (backward compat), nanti di Tahap 3 stok utama pindah ke `barang_stok`

**Hint untuk tahap berikutnya:**
- Lanjut **Tahap 2** — tambah `harga_beli` di `barang` & `barang_satuan`, plus `is_default` di `barang_satuan`
- Update UI `barang-create` dan `barang-edit` untuk input harga beli
- Migration: `add_harga_beli_to barang`, `add_harga_beli_is_default_to barang_satuan`

### Tahap 3 — POS Penjualan (2026-08-24)

**Selesai:**
- 3 migration: `add_fields_to_transaksi_table`, `add_snapshot_to_transaksi_detail_table`, `add_fields_to_stok_mutasi_table`
- Update Model `Transaksi` (tambah cabang_id, user_id, status, metode_bayar, bayar, kembali, diskon_total, catatan + relasi)
- Update Model `TransaksiDetail` (tambah harga_beli, nama_barang, nama_satuan fillable)
- Update Model `StokMutasi` (tambah cabang_id, transaksi_id, barang_satuan_id, qty_satuan + relasi)
- Update UI `transaksi-create` (pilih cabang, metode bayar, bayar/kembali otomatis, diskon total, catatan)
- Update `saveTransaksi()` pakai `DB::transaction()` + simpan snapshot + update `barang_stok` per cabang
- Tambah cancel/void di `transaksi-edit` (`$additionalPermissions = ['transaksi.penjualan.cancel']`)

**File berubah:**
- `src/database/migrations/2026_08_24_000006_add_fields_to_transaksi_table.php` (BARU)
- `src/database/migrations/2026_08_24_000007_add_snapshot_to_transaksi_detail_table.php` (BARU)
- `src/database/migrations/2026_08_24_000008_add_fields_to_stok_mutasi_table.php` (BARU)
- `src/app/Models/Transaksi.php` (UPDATE)
- `src/app/Models/TransaksiDetail.php` (UPDATE)
- `src/app/Models/StokMutasi.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡transaksi-create/transaksi-create.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡transaksi-create/transaksi-create.blade.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡transaksi-edit/transaksi-edit.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡transaksi-edit/transaksi-edit.blade.php` (UPDATE)

**Migration yang dibuat:**
- `2026_08_24_000006_add_fields_to_transaksi_table`
- `2026_08_24_000007_add_snapshot_to_transaksi_detail_table`
- `2026_08_24_000008_add_fields_to_stok_mutasi_table`

**Known Issues / TODO:**
- Migration belum dijalankan (butuh PHP >= 8.4.1, env saat ini 8.3.17)
- `transaksi-edit` belum fully updated untuk field-field baru (cabang, metode bayar, dll)
- `transaksi-list` belum ada tombol Edit/Cancel
- Menu sidebar belum ada entry untuk "Transaksi Penjualan"

**Hint untuk tahap berikutnya:**
- Lanjut **Tahap 4** — Cash Ledger: tabel `kas_mutasi`, insert saat transaksi LUNAS tunai
- Migration: `create_kas_mutasi_table`
- UI Laporan Kas (`laporan.kas.list`): filter cabang, per tanggal, saldo akhir
- Saat transaksi LUNAS tunai: insert `kas_mutasi` MASUK (`bayar`) + KELUAR (`kembali`)

### Tahap 4 — Cash Ledger (2026-08-24)

**Selesai:**
- 1 migration: `create_kas_mutasi_table`
- Model `KasMutasi` baru
- Update `transaksi-create` (insert `kas_mutasi` MASUK/KELUAR saat TUNAI SELESAI)
- Update `transaksi-edit` cancel (insert `kas_mutasi` refund: KELUAR `bayar` + MASUK `kembali`)
- MFC Component `kas-list` (`pages::laporan.kas-list`): filter cabang + tanggal, tampil MASUK/KELUAR/saldo_akhir
- Route: `laporan.kas.list`
- Permission auto-sync: `laporan.kas.view`, `laporan.kas.export`

**File berubah:**
- `src/database/migrations/2026_08_24_000009_create_kas_mutasi_table.php` (BARU)
- `src/app/Models/KasMutasi.php` (BARU)
- `src/resources/views/pages/transaksi/⚡transaksi-create/transaksi-create.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡transaksi-edit/transaksi-edit.php` (UPDATE)
- `src/resources/views/pages/laporan/⚡kas-list/kas-list.php` (BARU)
- `src/resources/views/pages/laporan/⚡kas-list/kas-list.blade.php` (BARU)
- `src/routes/web.php` (UPDATE)

**Migration yang dibuat:**
- `2026_08_24_000009_create_kas_mutasi_table`

**Known Issues / TODO:**
- Saldo awal cabang belum ada (butuh seeding awal atau input manual)
- `saldo_akhir` di `kas_mutasi` masih nullable (belum dihitung kumulatif)
- Menu sidebar belum ada entry untuk "Laporan Kas"

**Hint untuk tahap berikutnya:**
- Lanjut **Tahap 5** — Pembelian & HPP: tabel `pembelian` + `pembelian_detail`
- Migration: `create_pembelian_table`, `create_pembelian_detail_table`
- UI `pembelian.list / .create / .edit`
- Status TERIMA → `DB::transaction()`: `stok_mutasi` MASUK + update `barang.harga_beli` & `barang_stok`

### Tahap 2 — Master Barang & Satuan (2026-08-24)

**Selesai:**
- 2 migration: `add_harga_beli_to barang_table`, `add_columns_to barang_satuan_table`
- Update Model `Barang` (tambah `harga_beli` fillable)
- Update Model `BarangSatuan` (tambah `harga_beli`, `is_default` fillable)
- Update UI `barang-create` (input harga_beli barang + harga_beli & is_default per satuan)
- Update UI `barang-edit` (sama)
- Validasi: auto-set first satuan as default jika tidak ada yang dipilih

**File berubah:**
- `src/database/migrations/2026_08_24_000004_add_harga_beli_to barang_table.php` (BARU)
- `src/database/migrations/2026_08_24_000005_add_columns_to barang_satuan_table.php` (BARU)
- `src/app/Models/Barang.php` (UPDATE)
- `src/app/Models/BarangSatuan.php` (UPDATE)
- `src/resources/views/pages/master/⚡barang-create/barang-create.php` (UPDATE)
- `src/resources/views/pages/master/⚡barang-create/barang-create.blade.php` (UPDATE)
- `src/resources/views/pages/master/⚡barang-edit/barang-edit.php` (UPDATE)
- `src/resources/views/pages/master/⚡barang-edit/barang-edit.blade.php` (UPDATE)

**Migration yang dibuat:**
- `2026_08_24_000004_add_harga_beli_to barang_table`
- `2026_08_24_000005_add_columns_to barang_satuan_table`

**Known Issues / TODO:**
- Menu sidebar belum ada entry untuk "Master Cabang"

**Hint untuk tahap berikutnya:**
- Lanjut **Tahap 4** — Cash Ledger: tabel `kas_mutasi`, insert saat transaksi LUNAS tunai
- Migration: `create_kas_mutasi_table`
- UI Laporan Kas (`laporan.kas.list`): filter cabang, per tanggal, saldo akhir
- Saat transaksi LUNAS tunai: insert `kas_mutasi` MASUK (`bayar`) + KELUAR (`kembali`)
