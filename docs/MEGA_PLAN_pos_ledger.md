# MEGA PLAN — Desktop POS & Ledger

> Rencana teknis lengkap milestone `docs/milestone_desktop_pos_ledger.md`.
> AI selanjutnya **WAJIB** baca file ini + `docs/MODULE_milestone_desktop_pos_ledger.md` sebelum lanjut.

---

## Ringkasan

| Item | Jumlah |
|------|--------|
| Migration baru | 19 |
| Model baru | 8 |
| MFC Component baru | ~15 |
| Routes baru | ~20 |
| Service baru | 1 (JurnalService) |
| Seeder baru | 2 (Cabang, Akun) |

---

## Urutan Pengerjaan (WAJIK IKUTI)

### Tahap 1 — Multi-Cabang (Fondasi dulu, sisanya bergantung di ini)

**Migration:**
1. `2026_08_24_000001_create_cabang_table` — id, kode_cabang (unique), nama_cabang, alamat (nullable), is_aktif (default true), timestamps
2. `2026_08_24_000002_create barang_stok_table` — id, barang_id (FK cascade), cabang_id (FK cascade), stok (integer default 0), timestamps, unique(barang_id, cabang_id)
3. `2026_08_24_000003_add_cabang_id_to_users` — +cabang_id (FK nullable)

**Model:**
- `Cabang` — fillable: kode_cabang, nama_cabang, alamat, is_aktif. Relations: hasMany BarangStok, hasMany User
- `BarangStok` — fillable: barang_id, cabang_id, stok. Relations: belongsTo Barang, belongsTo Cabang
- Update `Barang` — add hasMany(BarangStok), helper stokPerCabang(cabangId)
- Update `User` — add belongsTo(Cabang)

**Seed:**
- Cabang default: kode `PUSAT`, nama `Cabang Pusat`
- Migrasi data: pindahkan existing `barang.stok` → `barang_stok` (cabang PUSAT)

**MFC Components:**
- `pages::master.cabang-list` — list + search + pagination
- `pages::master.cabang-create` — form (kode, nama, alamat, is_aktif)
- `pages::master.cabang-edit` — form edit

**Routes** (prefix `master`, middleware auth+permission):
```
master.cabang.list   → GET /master/cabang
master.cabang.create → GET /master/cabang/create
master.cabang.edit   → GET /master/cabang/{id}/edit
```

**Permissions (auto dari route):** `master.cabang.view`, `.create`, `.update`
**Additional Permissions:** `master.cabang.delete`

**Hint:** Setelah tahap ini, semua transaksi/stok nanti pakai `cabang_id`. UI POS akan pilih cabang default = cabang kasir.

---

### Tahap 2 — Perkuat Master Barang & Satuan

**Migration:**
4. `2026_08_24_000004_add_harga_beli_to barang` — +harga_beli decimal(18,2) default 0 (after stok)
5. `2026_08_24_000005_add_columns_to barang_satuan` — +harga_beli decimal(18,2) default 0, +is_default boolean default false

**Update Model:**
- `Barang` — add `harga_beli` ke fillable
- `BarangSatuan` — add `harga_beli`, `is_default` ke fillable

**Update Component:**
- `⚡barang-create` — tambah input harga_beli (barang), harga_beli per satuan, checkbox is_default
- `⚡barang-edit` — sama
- Validasi: hanya 1 `is_default=true` per barang (custom rule)

**Hint:** Harga beli per satuan otomatis = `barang.harga_beli * konversi` saat input, tapi bisa override manual.

---

### Tahap 3 — POS Penjualan (Upgrade Transaksi)

**Migration:**
6. `2026_08_24_000006_add_fields_to transaksi` — +cabang_id (FK), +user_id (FK users), +status (enum: SELESAI/BATAL/PIUTANG default SELESAI), +metode_bayar (enum: TUNAI/TRANSFER/QRIS default TUNAI), +bayar decimal(15,2), +kembali decimal(15,2), +diskon_total decimal(15,2) default 0, +catatan text nullable
7. `2026_08_24_000007_add_snapshot_to transaksi_detail` — +harga_beli decimal(15,2), +nama_snapshot string, +nama_satuan_snapshot string
8. `2026_08_24_000008_add_fields_to stok_mutasi` — +cabang_id (FK), +transaksi_id (FK nullable), +barang_satuan_id (FK nullable), +qty_satuan decimal(15,2)

**Update Model:**
- `Transaksi` — add fillable baru, belongsTo(Cabang), belongsTo(User)
- `TransaksiDetail` — add `harga_beli`, `nama_snapshot`, `nama_satuan_snapshot` ke fillable
- `StokMutasi` — add `cabang_id`, `transaksi_id`, `barang_satuan_id`, `qty_satuan` ke fillable, belongsTo(Cabang), belongsTo(Transaksi), belongsTo(BarangSatuan)

**Update Component `⚡transaksi-create`:**
- Pilih cabang (default = cabang kasir dari users.cabang_id)
- Pilih metode bayar (TUNAI/TRANSFER/QRIS)
- Input bayar → auto-hitung kembali = bayar - grand_total
- Simpan snapshot: harga_beli, nama_snapshot, nama_satuan_snapshot
- user_id = auth()->id()
- Saat simpan: `DB::transaction()` → insert transaksi + details + stok_mutasi KELUAR per cabang + kurangi `barang_stok.stok`

**Aksi Baru:**
- `transaksi.penjualan.cancel` — void transaksi → balikkan stok, status BATAL

**Additional Permissions:** `transaksi.penjualan.cancel`

**Hint:** Transaksi status PIUTANG dibuat di Tahap 7 (saat ada modul piutang).

---

### Tahap 4 — Cash Ledger (Buku Kas)

**Migration:**
9. `2026_08_24_000009_create_kas_mutasi_table` — id, cabang_id (FK), tanggal date, tipe (enum: MASUK/KELUAR), sumber (enum: PENJUALAN/SETOR/TARIK/REFUND/LAIN), transaksi_id (FK nullable), jumlah decimal(15,2), saldo_akhir decimal(15,2), keterangan nullable, timestamps

**Logic (pas simpan transaksi TUNAI LUNAS):**
- Insert kas_mutasi MASUK: jumlah = bayar, sumber = PENJUALAN
- Insert kas_mutasi KELUAR: jumlah = kembali (jika > 0), sumber = PENJUALAN
- saldo_akhir dihitung: saldo terakhir per cabang + jumlah (MASUK) - jumlah (KELUAR)

**MFC Components:**
- `pages::laporan.kas-list` — filter cabang + tanggal, tampilkan: tanggal, sumber, MASUK, KELUAR, saldo_akhir

**Routes** (prefix `laporan`, middleware auth+permission):
```
laporan.kas.list → GET /laporan/kas
```

**Additional Permissions:** `laporan.kas.export`

**Hint:** Saat transaksi BATAL (cancel), insert kas_mutasi pengembalian tergantung metode bayar.

---

### Tahap 5 — Pembelian & HPP

**Migration:**
10. `2026_08_24_000010_create_pembelian_table` — id, nomor_beli (unique), cabang_id (FK), supplier string, tanggal date, total decimal(15,2), status (enum: ORDER/TERIMA/BATAL default ORDER), timestamps
11. `2026_08_24_000011_create_pembelian_detail_table` — id, pembelian_id (FK cascade), barang_id (FK), barang_satuan_id (FK), qty decimal(15,2), harga_beli decimal(15,2), subtotal decimal(15,2), timestamps

**MFC Components:**
- `pages::transaksi.pembelian-list` — list + filter status + tanggal
- `pages::transaksi.pembelian-create` — form (supplier, tanggal, barang, qty, harga_beli)
- `pages::transaksi.pembelian-edit` — form edit (hanya status ORDER)

**Routes** (prefix `transaksi`):
```
transaksi.pembelian.list   → GET /transaksi/pembelian
transaksi.pembelian.create → GET /transaksi/pembelian/create
transaksi.pembelian.edit   → GET /transaksi/pembelian/{id}/edit
```

**Logic (status TERIMA):**
- `DB::transaction()`:
  - Insert stok_mutasi MASUK per barang per cabang
  - Update/tambah `barang_stok.stok` per cabang
  - Update `barang.harga_beli` (harga beli terakhir)

**Additional Permissions:** `transaksi.pembelian.receive`, `transaksi.pembelian.cancel`

**Hint:** HPP (Harga Pokok Penjualan) dihitung dari `barang.harga_beli` * qty_pcs saat penjualan.

---

### Tahap 6 — Jurnal Akuntansi

**Migration:**
12. `2026_08_24_000012_create_akun_table` — id, kode_akun string (unique), nama_akun string, tipe (enum: ASET/UTANG/MODAL/PENDAPATAN/BEBAN), cabang_id (FK nullable), timestamps
13. `2026_08_24_000013_create_jurnal_table` — id, tanggal date, nomor_jurnal (unique), keterangan string, transaksi_id (FK nullable), cabang_id (FK), timestamps
14. `2026_08_24_000014_create_jurnal_detail_table` — id, jurnal_id (FK cascade), akun_id (FK), debit decimal(15,2) default 0, kredit decimal(15,2) default 0, timestamps

**Seeder Akun Dasar:**
```
1001  Kas         ASET
1002  Piutang     ASET
1003  Persediaan  ASET
2001  Hutang      UTANG
3001  Modal       MODAL
4001  Penjualan   PENDAPATAN
5001  HPP         BEBAN
6001  Beban Operasional BEBAN
```

**Service `app/Services/JurnalService.php`:**
- `buatJurnalPenjualan(Transaksi $transaksi)`:
  ```
  KAS (1001)         DEBIT  grand_total
  PENJUALAN (4001)   KREDIT grand_total
  HPP (5001)         DEBIT  total_harga_beli
  PERSEDIAAN (1003)  KREDIT total_harga_beli
  ```
- `buatJurnalPembelian(Pembelian $pembelian)`:
  ```
  PERSEDIAAN (1003)  DEBIT  total
  HUTANG (2001)      KREDIT total
  ```

**MFC Components:**
- `pages::laporan.buku-besar-list` — filter akun + periode, tampilkan semua jurnal_detail per akun + saldo
- `pages::laporan.laba-rugi-list` — PENDAPATAN - BEBAN - HPP (dari jurnal_detail)

**Routes:**
```
laporan.buku-besar.list → GET /laporan/buku-besar
laporan.laba-rugi.list  → GET /laporan/laba-rugi
```

**Additional Permissions:** `laporan.laba-rugi.export`

---

### Tahap 7 — Piutang, Hutang & Pajak

**Migration:**
15. `2026_08_24_000015_create_piutang_table` — id, transaksi_id (FK), customer string, sisa decimal(15,2), status (enum: BELUM/LUNAS default BELUM), timestamps
16. `2026_08_24_000016_create_hutang_table` — id, pembelian_id (FK), supplier string, sisa decimal(15,2), status (enum: BELUM/LUNAS default BELUM), timestamps
17. `2026_08_24_000017_create_pelunasan_table` — id, piutang_id (FK nullable cascade), hutang_id (FK nullable cascade), tanggal date, jumlah decimal(15,2), timestamps
18. `2026_08_24_000018_add_pajak_to transaksi` — +pajak decimal(15,2) default 0
19. `2026_08_24_000019_add_pajak_to pembelian` — +pajak decimal(15,2) default 0

**MFC Components:**
- `pages::transaksi.piutang-list` — list piutang + tombol bayar
- `pages::transaksi.hutang-list` — list hutang + tombol bayar

**Routes:**
```
transaksi.piutang.list → GET /transaksi/piutang
transaksi.hutang.list  → GET /transaksi/hutang
```

**Logic (pelunasan piutang):**
- Insert `pelunasan` → update `piutang.sisa` → jika 0, status = LUNAS
- Insert `kas_mutasi` MASUK → insert `jurnal` (KAS debet, Piutang kredit)

**Logic (pelunasan hutang):**
- Insert `pelunasan` → update `hutang.sisa` → jika 0, status = LUNAS
- Insert `kas_mutasi` KELUAR → insert `jurnal` (Hutang debet, KAS kredit)

**Pajak (PPN):**
- Penjualan: PPN Keluaran → jurnal terpisah (KAS/Piutang debet, PPN Keluaran kredit)
- Pembelian: PPN Masukan → jurnal terpisah (PPN Masukan debet, HUTANG/KAS kredit)

**Additional Permissions:** `transaksi.piutang.pay`, `transaksi.hutang.pay`

---

### Tahap 8 — Laporan Gabungan & Final

**MFC Components:**
- `pages::laporan.penjualan-list` — per invoice / per barang / per cabang
- `pages::laporan.stok-list` — mutasi stok_mutasi per barang/cabang
- `pages::laporan.neraca-list` — ASET = UTANG + MODAL (dari saldo akun)
- `pages::laporan.arus-kas-list` — dari kas_mutasi per cabang

**Routes:**
```
laporan.penjualan.list → GET /laporan/penjualan
laporan.stok.list      → GET /laporan/stok
laporan.neraca.list    → GET /laporan/neraca
laporan.arus-kas.list  → GET /laporan/arus-kas
```

**Final Steps:**
- `php artisan test` → happy path, auth, validation, failure
- Update `docs/CHANGELOG.md`
- Update `docs/MODULE_milestone_desktop_pos_ledger.md`

---

## Catatan Penting

1. **Semua migration butuh approval** — jangan buat sebelum di-approve.
2. **Urutan dependency** — cabang dulu → barang_stok → sisanya.
3. **DB::transaction()** wajib untuk aksi multi-tabel (simpan transaksi, terima pembelian, pelunasan).
4. **Snapshot** — harga_beli & nama di `transaksi_detail` adalah SAAT JUAL, bukan harga saat ini.
5. **Stok per cabang** — pakai tabel `barang_stok`, bukan `barang.stok` (kolom `barang.stok` bisa tetap ada sebagai backward compat atau di-deprecate).
6. **Permission sync** — setelah routes baru dibuat, jalankan `php artisan framework:permission-sync`.
7. **Saat artisan** — pakai `docker compose exec app bash -c "cd src && artisan ..."`.

---

## Flow Kerja Tiap Tahap

1. Baca MEGA PLAN tahap yang sedang dikerjakan
2. Baca MODULE progress log (jika ada)
3. Buat migration (setelah approval)
4. Buat/update Model + relationships
5. Buat/update MFC components + routes
6. Tambah `$additionalPermissions` jika perlu
7. `php artisan framework:route-sync` + `framework:permission-sync`
8. Test: `php artisan test`
9. Update MODULE progress log
10. Lanjut tahap berikutnya
