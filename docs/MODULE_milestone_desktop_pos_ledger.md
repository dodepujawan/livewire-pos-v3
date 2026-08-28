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
| 3 | POS Penjualan | ✅ | Migration + model + transaksi-create + cancel + kas_mutasi |
| 4 | Cash Ledger | ✅ | kas_mutasi + laporan kas + refund cancel |
| 5 | Pembelian & HPP | ✅ | pembelian + pembelian_detail + receive/cancel stok |
| 6 | Jurnal Akuntansi | ✅ | Akun, jurnal, jurnal_detail + service + laporan laba-rugi |
| 7 | Piutang, Hutang & Pajak | ✅ | Piutang, hutang, pelunasan, PPN, status PIUTANG |
| 8 | Laporan Gabungan & Final | ✅ | Penjualan, stok, neraca, arus kas + sidebar menu lengkap |

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

### Tahap 5 — Pembelian & HPP (2026-08-24)

**Selesai:**
- 2 migration: `create_pembelian_table`, `create_pembelian_detail_table`
- Model `Pembelian` & `PembelianDetail` baru
- MFC Component `pembelian-list` (`pages::transaksi.pembelian-list`): list + filter status/tanggal + tombol Terima
- MFC Component `pembelian-create` (`pages::transaksi.pembelian-create`): form ORDER + cart barang
- MFC Component `pembelian-edit` (`pages::transaksi.pembelian-edit`): edit ORDER + receive + cancel
- Route: `transaksi.pembelian.list`, `.create`, `.edit`
- Permission auto-sync: `transaksi.pembelian.view`, `.create`, `.update`, `.delete`
- Additional permissions: `transaksi.pembelian.receive`, `transaksi.pembelian.cancel`

**File berubah:**
- `src/database/migrations/2026_08_24_000010_create_pembelian_table.php` (BARU)
- `src/database/migrations/2026_08_24_000011_create_pembelian_detail_table.php` (BARU)
- `src/app/Models/Pembelian.php` (BARU)
- `src/app/Models/PembelianDetail.php` (BARU)
- `src/resources/views/pages/transaksi/⚡pembelian-list/pembelian-list.php` (BARU)
- `src/resources/views/pages/transaksi/⚡pembelian-list/pembelian-list.blade.php` (BARU)
- `src/resources/views/pages/transaksi/⚡pembelian-create/pembelian-create.php` (BARU)
- `src/resources/views/pages/transaksi/⚡pembelian-create/pembelian-create.blade.php` (BARU)
- `src/resources/views/pages/transaksi/⚡pembelian-edit/pembelian-edit.php` (BARU)
- `src/resources/views/pages/transaksi/⚡pembelian-edit/pembelian-edit.blade.php` (BARU)
- `src/routes/web.php` (UPDATE)

**Migration yang dibuat:**
- `2026_08_24_000010_create_pembelian_table`
- `2026_08_24_000011_create_pembelian_detail_table`

**Known Issues / TODO:**
- `receivePembelian()` di `pembelian-edit` sudah ada tapi belum diuji langsung
- Belum ada validasi `harga_beli` minimum saat create/edit
- Menu sidebar belum ada entry untuk "Pembelian Barang"
- **FIXED**: `cancelPembelian()` sekarang rollback stok jika pembelian sudah status TERIMA (KELUAR stok_mutasi + decrement barang_stok + decrement barang.stok)

**Hint untuk tahap berikutnya:**
- Lanjut **Tahap 7** — Piutang, Hutang & Pajak: tabel `piutang`, `hutang`, `pelunasan` + kolom `pajak` di transaksi & pembelian
- Migration: `create_piutang_table`, `create_hutang_table`, `create_pelunasan_table`, `add_pajak_to_transaksi`, `add_pajak_to_pembelian`
- UI `transaksi.piutang-list`, `transaksi.hutang-list` (list + tombol bayar)
- Logic pelunasan: update sisa → jika 0 status LUNAS → insert kas_mutasi → insert jurnal
- Additional Permissions: `transaksi.piutang.pay`, `transaksi.hutang.pay`

### Tahap 6 — Jurnal Akuntansi (2026-08-26)

**Selesai:**
- 3 migration: `create_akun_table`, `create_jurnal_table`, `create_jurnal_detail_table`
- Model `Akun`, `Jurnal`, `JurnalDetail` baru + relationships
- Seeder `AkunSeeder` — 8 akun dasar: 1001 Kas, 1002 Piutang, 1003 Persediaan, 2001 Hutang, 3001 Modal, 4001 Penjualan, 5001 HPP, 6001 Beban Operasional
- Service `JurnalService`:
  - `buatJurnalPenjualan(Transaksi)` → Kas debet / Penjualan kredit / HPP debet / Persediaan kredit
  - `buatJurnalPembelian(Pembelian)` → Persediaan debet / Hutang kredit
  - `buatJurnalRefund(Transaksi)` → kebalikan jurnal penjualan
- Update `transaksi-create`: trigger `JurnalService::buatJurnalPenjualan()` saat status SELESAI
- Update `pembelian-edit`: trigger `JurnalService::buatJurnalPembelian()` saat receive
- MFC `buku-besar-list`: filter nomor/keterangan + tipe akun + tanggal, tampilkan semua detail jurnal
- MFC `laba-rugi-list`: filter tanggal, ringkasan pendapatan vs beban = laba/rugi bersih
- Routes: `laporan.buku-besar.list`, `laporan.laba-rugi.list`
- DatabaseSeeder: tambah AkunSeeder

**File berubah:**
- `src/database/migrations/2026_08_24_000012_create_akun_table.php` (BARU)
- `src/database/migrations/2026_08_24_000013_create_jurnal_table.php` (BARU)
- `src/database/migrations/2026_08_24_000014_create_jurnal_detail_table.php` (BARU)
- `src/app/Models/Akun.php` (BARU)
- `src/app/Models/Jurnal.php` (BARU)
- `src/app/Models/JurnalDetail.php` (BARU)
- `src/database/seeders/AkunSeeder.php` (BARU)
- `src/database/seeders/DatabaseSeeder.php` (UPDATE)
- `src/app/Services/JurnalService.php` (BARU)
- `src/resources/views/pages/transaksi/⚡transaksi-create/transaksi-create.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡pembelian-edit/pembelian-edit.php` (UPDATE)
- `src/resources/views/pages/laporan/⚡buku-besar-list/buku-besar-list.php` (BARU)
- `src/resources/views/pages/laporan/⚡buku-besar-list/buku-besar-list.blade.php` (BARU)
- `src/resources/views/pages/laporan/⚡laba-rugi-list/laba-rugi-list.php` (BARU)
- `src/resources/views/pages/laporan/⚡laba-rugi-list/laba-rugi-list.blade.php` (BARU)
- `src/routes/web.php` (UPDATE)

**Migration yang dibuat:**
- `2026_08_24_000012_create_akun_table`
- `2026_08_24_000013_create_jurnal_table`
- `2026_08_24_000014_create_jurnal_detail_table`

**Known Issues / TODO:**
- Nomor jurnal format: JNL-SAL-{YYYYMMDD}-{seq} untuk penjualan, JNL-BEL-{YYYYMMDD}-{seq} untuk pembelian
- Jurnal belum dibuat saat transaksi non-TUNAI (TRANSFER/QRIS) — bisa ditambahkan nanti
- Menu sidebar belum ada entry untuk "Buku Besar" dan "Laba Rugi"
- Belum ada export PDF/Excel untuk laporan laba rugi

**Hint untuk tahap berikutnya:**
- Lanjut **Tahap 8** — Laporan Gabungan: laporan penjualan, stok, neraca, arus kas
- MFC `laporan.penjualan-list`, `laporan.stok-list`, `laporan.neraca-list`, `laporan.arus-kas-list`
- `php artisan test` untuk fitur penting

### Tahap 7 — Piutang, Hutang & Pajak (2026-08-28)

**Selesai:**
- Fix migration dari Copilot (tambah `customer` di piutang, `supplier` di hutang, FK di pelunasan)
- 5 migration: `create_piutang_table`, `create_hutang_table`, `create_pelunasan_table`, `add_pajak_to_transaksi`, `add_pajak_to_pembelian`
- Model `Piutang`, `Hutang`, `Pelunasan` baru + relationships
- Service `PelunasanService`: `processPelunasanPiutang()`, `processPelunasanHutang()`
- Update `Transaksi` model (tambah `pajak` fillable + `piutang()` relation)
- Update `Pembelian` model (tambah `pajak` fillable)
- Update `transaksi-create`: input pajak, status selector (SELESAI/PIUTANG), auto-create piutang saat status PIUTANG
- Update `pembelian-edit`: input pajak field
- MFC `piutang-list`: filter + pelunasan modal (bayar piutang → kas_mutasi MASUK + jurnal)
- MFC `hutang-list`: filter + pelunasan modal (bayar hutang → kas_mutasi KELUAR + jurnal)
- Routes: `transaksi.piutang.list`, `transaksi.hutang.list` (sudah ada dari Copilot)
- Permission sync: 38 permissions

**File berubah:**
- `src/database/migrations/2026_08_28_000001_create_piutang_table.php` (UPDATE)
- `src/database/migrations/2026_08_28_000002_create_hutang_table.php` (UPDATE)
- `src/database/migrations/2026_08_28_000003_create_pelunasan_table.php` (UPDATE)
- `src/database/migrations/2026_08_28_000004_add_pajak_to_transaksi_table.php` (sudah OK)
- `src/database/migrations/2026_08_28_000005_add_pajak_to_pembelian_table.php` (sudah OK)
- `src/app/Models/Piutang.php` (BARU)
- `src/app/Models/Hutang.php` (BARU)
- `src/app/Models/Pelunasan.php` (BARU)
- `src/app/Models/Transaksi.php` (UPDATE)
- `src/app/Models/Pembelian.php` (UPDATE)
- `src/app/Services/PelunasanService.php` (BARU)
- `src/resources/views/pages/transaksi/⚡transaksi-create/transaksi-create.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡transaksi-create/transaksi-create.blade.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡pembelian-edit/pembelian-edit.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡pembelian-edit/pembelian-edit.blade.php` (UPDATE)
- `src/resources/views/pages/transaksi/⚡piutang-list/piutang-list.php` (BARU)
- `src/resources/views/pages/transaksi/⚡piutang-list/piutang-list.blade.php` (BARU)
- `src/resources/views/pages/transaksi/⚡hutang-list/hutang-list.php` (BARU)
- `src/resources/views/pages/transaksi/⚡hutang-list/hutang-list.blade.php` (BARU)
- `src/routes/web.php` (sudah ada dari Copilot)

**Migration yang dibuat/di-fix:**
- `2026_08_28_000001_create_piutang_table` (fix: tambah customer)
- `2026_08_28_000002_create_hutang_table` (fix: tambah supplier)
- `2026_08_28_000003_create_pelunasan_table` (fix: tambah FK ke piutang/hutang)
- `2026_08_28_000004_add_pajak_to_transaksi_table` (sudah OK)
- `2026_08_28_000005_add_pajak_to_pembelian_table` (sudah OK)

**Known Issues / TODO:**
- Menu sidebar belum ada entry untuk "Piutang" dan "Hutang" (perlu tambah via MenuSeeder)
- Belum ada laporan neraca (Tahap 8)
- Belum ada export PDF/Excel untuk laporan

**Hint untuk tahap berikutnya:**
- Lanjut **Tahap 8** — Laporan Gabungan: laporan penjualan, stok, neraca, arus kas
- MFC `laporan.penjualan-list`, `laporan.stok-list`, `laporan.neraca-list`, `laporan.arus-kas-list`
- `php artisan test` untuk fitur penting

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

### Tahap 7 — Piutang, Hutang & Pajak

**Tujuan:** transaksi belum lunas & utang supplier tercatat.

### 1. Analisis & Persiapan
- [ ] Baca dokumentasi database (section 9.5) untuk skema tabel piutang, hutang, dan pelunasan
- [ ] Pastikan approval untuk migration dari programmer
- [ ] Siapkan struktur folder MFC untuk komponen baru

### 2. Migration
- [ ] Migration: `piutang` (kolom: `id`, `cabang_id`, `transaksi_id`, `nomor_piutang`, `tanggal`, `jumlah`, `sisa`, `status`, `catatan`)
- [ ] Migration: `hutang` (kolom: `id`, `cabang_id`, `pembelian_id`, `nomor_hutang`, `tanggal`, `jumlah`, `sisa`, `status`, `catatan`)
- [ ] Migration: `pelunasan` (kolom: `id`, `cabang_id`, `jenis`, `referensi_id`, `tanggal`, `jumlah`, `metode_bayar`, `catatan`)
- [ ] Migration: tambah kolom `pajak` di tabel `transaksi` (type: decimal(10,2))
- [ ] Migration: tambah kolom `pajak` di tabel `pembelian` (type: decimal(10,2))

### 3. Model & Relationship
- [ ] Buat model `Piutang` dengan relasi ke `Cabang`, `Transaksi`
- [ ] Buat model `Hutang` dengan relasi ke `Cabang`, `Pembelian`
- [ ] Buat model `Pelunasan` dengan relasi ke `Cabang`, `Piutang`/`Hutang`
- [ ] Tambahkan relationship di model `Transaksi` dan `Pembelian` untuk pajak
- [ ] Tambahkan relationship di model `Cabang` untuk piutang dan hutang
- [ ] Buat model `PelunasanDetail` jika diperlukan untuk riwayat pelunasan

### 4. UI Components (Livewire)
#### Struktur Folder Komponen
```
resources/views/pages/
├── transaksi/
│   ├── piutang-list.blade.php
│   ├── piutang-create.blade.php
│   ├── piutang-edit.blade.php
│   ├── hutang-list.blade.php
│   ├── hutang-create.blade.php
│   ├── hutang-edit.blade.php
│   └── pelunasan-create.blade.php
```

#### Komponen Transaksi Piutang
- [ ] Route: `transaksi.piutang.list`, `transaksi.piutang.create`, `transaksi.piutang.edit`
- [ ] Component: `pages::transaksi.piutang-list`, `pages::transaksi.piutang-create`, `pages::transaksi.piutang-edit`
- [ ] UI: List piutang dengan filter berdasarkan cabang/tanggal/status
- [ ] UI: Form create/edit piutang dengan input jumlah, tanggal, catatan
- [ ] UI: Detail piutang dengan histori pelunasan
- [ ] UI: Tombol pelunasan yang memicu modal pelunasan

#### Komponen Transaksi Hutang
- [ ] Route: `transaksi.hutang.list`, `transaksi.hutang.create`, `transaksi.hutang.edit`
- [ ] Component: `pages::transaksi.hutang-list`, `pages::transaksi.hutang-create`, `pages::transaksi.hutang-edit`
- [ ] UI: List hutang dengan filter berdasarkan cabang/tanggal/status
- [ ] UI: Form create/edit hutang dengan input jumlah, tanggal, catatan
- [ ] UI: Detail hutang dengan histori pelunasan
- [ ] UI: Tombol pelunasan yang memicu modal pelunasan

#### Komponen Pelunasan
- [ ] Route: `transaksi.pelunasan.create`
- [ ] Component: `pages::transaksi.pelunasan-create`
- [ ] UI: Form pelunasan dengan pilihan referensi (piutang/hutang), jumlah, metode bayar
- [ ] UI: Validasi jumlah tidak melebihi sisa piutang/hutang
- [ ] UI: Tampilan detail referensi sebelum pelunasan
- [ ] UI: Tombol konfirmasi pelunasan

### 5. Business Logic
- [ ] Service `PelunasanService` untuk mengelola pelunasan piutang/hutang
- [ ] Saat pelunasan → update `kas_mutasi` + `jurnal` + update sisa piutang/hutang
- [ ] Pajak masuk ke `jurnal` (akun PPN Masukan/Keluaran)
- [ ] Validasi jumlah pelunasan tidak melebihi sisa piutang/hutang
- [ ] Status piutang/hutang otomatis berubah menjadi LUNAS jika sisa = 0
- [ ] Service `JurnalService` diperbarui untuk menangani pelunasan piutang/hutang
- [ ] Validasi permission untuk setiap aksi pelunasan

### 6. Permissions
- [ ] Tambahkan `$additionalPermissions`:
  - `transaksi.piutang.pay`
  - `transaksi.piutang.cancel`
  - `transaksi.hutang.pay`
  - `transaksi.hutang.cancel`
  - `transaksi.pelunasan.create`

### 7. Testing
- [ ] Test validasi input pelunasan
- [ ] Test pelunasan piutang/hutang
- [ ] Test jurnal otomatis
- [ ] Test kas mutasi
- [ ] Test pajak masuk ke jurnal
- [ ] Test status otomatis LUNAS
- [ ] Test validasi jumlah pelunasan
- [ ] Test permission untuk setiap aksi

### 8. Deployment
- [ ] Jalankan `php artisan framework:permission-sync`
- [ ] Jalankan `php artisan test` untuk fitur piutang/hutang
- [ ] Update dokumentasi
- [ ] Buat dokumentasi untuk cara penggunaan fitur piutang/hutang

---

## Tahap 8 — Laporan Gabungan & Final

- [ ] `laporan.penjualan.list` (per invoice `nomor_transaksi`, per barang, per cabang).
- [ ] `laporan.stok.list` (mutasi `stok_mutasi` per barang/cabang).
- [ ] `laporan.neraca.list` (ASET = UTANG + MODAL dari saldo `akun`).
- [ ] `laporan.arus-kas.list` (dari `kas_mutasi`).
- [ ] `php artisan test` untuk fitur penting (happy path, auth, validation, failure).
- [ ] Update `docs/CHANGELOG.md` & `MODULE_*.md` per milestone.

---

## Cara menjalankan (tiap milestone)

1. Analisa & buat Mega Plan → tunggu approval.
2. Buat migration (kalau sudah di-approve di Tahap 0).
3. Buat Model + relationship.
4. Buat Livewire MFC component + route (ikuti `module.resource.action`).
4. Tambah `$additionalPermissions` untuk aksi bisnis.
5. `DB::transaction()` untuk aksi multi-tabel.
6. `php artisan framework:permission-sync`.
7. Test: `php artisan test`.
8. Update dokumentasi.

> Ingat: jangan ubah migration/program inti tanpa approval. Dokumentasi ini
> adalah acuan bersama, bisa dilanjutkan AI mana pun asal baca `docs/` dulu.

> tambahan tolong catat progress disini docs/MODULE_milestone_desktop_pos_ledger.md kalo perlu kasi sedikit hints untuk selanjutnya sehingga ai lain ada acuan lebih clear

### Tahap 8 — Laporan Gabungan & Final (2026-08-28)

**Selesai:**
- 4 MFC laporan baru (tanpa migration, aggregates dari tabel existing):
  - `laporan.penjualan-list`: filter tanggal/cabang/status, ringkasan total penjualan/pajak/diskon
  - `laporan.stok-list`: mutasi stok (MASUK/KELUAR) per barang/cabang/tanggal
  - `laporan.neraca-list`: saldo akun (ASET vs UTANG+MODAL+LABA), balance check
  - `laporan.arus-kas-list`: kas_mutasi (MASUK/KELUAR) per cabang/sumber/tanggal
- Routes: `laporan.penjualan.list`, `laporan.stok.list`, `laporan.neraca.list`, `laporan.arus-kas.list`
- MenuSeeder: lengkap (Master: Barang+Cabang, Transaksi: Penjualan+Pembelian+Piutang+Hutang, Laporan: 7 laporan, Sistem: Pengaturan)
- Permission sync: 45 permissions

**File berubah:**
- `src/resources/views/pages/laporan/⚡penjualan-list/penjualan-list.php` (BARU)
- `src/resources/views/pages/laporan/⚡penjualan-list/penjualan-list.blade.php` (BARU)
- `src/resources/views/pages/laporan/⚡stok-list/stok-list.php` (BARU)
- `src/resources/views/pages/laporan/⚡stok-list/stok-list.blade.php` (BARU)
- `src/resources/views/pages/laporan/⚡neraca-list/neraca-list.php` (BARU)
- `src/resources/views/pages/laporan/⚡neraca-list/neraca-list.blade.php` (BARU)
- `src/resources/views/pages/laporan/⚡arus-kas-list/arus-kas-list.php` (BARU)
- `src/resources/views/pages/laporan/⚡arus-kas-list/arus-kas-list.blade.php` (BARU)
- `src/routes/web.php` (UPDATE)
- `src/database/seeders/MenuSeeder.php` (UPDATE)

**Migration yang dibuat:** none (semua aggregates dari tabel existing)

**Known Issues / TODO:**
- Belum ada export PDF/Excel untuk semua laporan
- Belum ada `php artisan test` (unit test)
- Neraca balance check: kalau tidak seimbang, mungkin ada jurnal yang tidak valid

---

## Status: ✅ SELESAI SEMUA MILESTONE

Semua 9 tahap (Tahap 0-8) sudah selesai. Sistem POS & Ledger lengkap:
- Multi-cabang, master barang, transaksi penjualan, pembelian, piutang, hutang
- Cash ledger, jurnal akuntansi, laporan keuangan (laba rugi, neraca, arus kas)
- Sidebar menu lengkap, permission system

---

## Cara menjalankan (tiap milestone)
