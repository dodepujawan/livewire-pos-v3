# Milestone — Desktop POS & Ledger

> Panduan bertahap membangun aplikasi POS + Ledger dari `docs/database_pos_ledger.md`.
> Ditulis rapi & sederhana biar siapa pun (termasuk AI lain) bisa lanjutkan.
>
> Aturan wajib (dari `PROJECT_RULES_v2.md` & `hak-akses-ai.md`):
> - **Migration tidak boleh dibuat/diubah tanpa approval.**
> - Livewire pakai **MFC** (satu component = satu folder), bukan SFC.
> - Route & permission: `module.resource.action` (lihat `src/routes/web.php`).
> - Route `.list/.create/.edit/.show` → permission otomatis.
>   Aksi bisnis lain (delete/print/export/import/cancel) → tambah ke `$additionalPermissions`.
> - Pakai `DB::transaction()` kalau 1 aksi ubah banyak tabel.

---

## Tahap 0 — Persiapan (wajib sebelum coding)

- [ ] Baca `docs/database_pos_ledger.md` & `docs/PROJECT_RULES_v2.md`.
- [ ] **Minta approval** ke programmer untuk migration di section 11 doc database.
- [ ] Setelah approval: buat migration sesuai urutan dependency (cabang dulu, lalu sisanya).
- [ ] Jalankan `php artisan framework:permission-sync` setelah route baru dibuat.

---

## Tahap 1 — Multi-Cabang (fondasi dulu)

**Tujuan:** semua transaksi/stok nanti punya `cabang_id`.

- [ ] Migration: tabel `cabang` (section 4.1 db doc).
- [ ] Migration: tabel `barang_stok` (`barang_id`, `cabang_id`, `stok`) — stok per cabang (pilihan a).
- [ ] Model `Cabang`, `BarangStok` + Eloquent relationship.
- [ ] Seed/update: pindahkan `barang.stok` lama ke `barang_stok` cabang default.
- [ ] UI Master Cabang (MFC):
  - Route: `master.cabang.list / .create / .edit`
  - Component: `pages::master.cabang-list`, `cabang-create`, `cabang-edit`
- [ ] Tambah `$additionalPermissions` bila ada aksi `master.cabang.delete`.

---

## Tahap 2 — Perkuat Master Barang & Satuan

**Tujuan:** harga beli & satuan lengkap (butuh untuk laba & HPP).

- [ ] Migration: `+ harga_beli` di `barang`; `+ harga_beli`, `+ is_default` di `barang_satuan`.
- [ ] Update UI `master.barang` (create/edit) agar input harga beli & tanda satuan default.
- [ ] Update `barang_satuan` UI (bisa inline di halaman barang).
- [ ] Validasi: `is_default` hanya 1 per barang.

---

## Tahap 3 — POS Penjualan (upgrade transaksi)

**Tujuan:** transaksi catat cabang, kasir, metode bayar, bayar/kembali, status.

- [ ] Migration: kolom `transaksi` (section 4.4 db doc):
  `cabang_id`, `user_id`, `status`, `metode_bayar`, `bayar`, `kembali`, `diskon_total`, `pajak`, `catatan`.
- [ ] Migration: snapshot `transaksi_detail` (`harga_beli`, `nama_barang`, `nama_satuan`) + revisi `stok_mutasi` (`cabang_id`, `transaksi_id`, `barang_satuan_id`, `qty_satuan`).
- [ ] Update component `transaksi.penjualan.create`:
  - Pilih cabang (default cabang kasir), metode bayar, input bayar → hitung kembali otomatis.
  - Simpan `harga_beli` & nama snapshot ke detail.
- [ ] Saat simpan: `DB::transaction()` → insert transaksi + detail + `stok_mutasi` KELUAR per cabang + kurangi `barang_stok`.
- [ ] Tambah `transaksi.penjualan.cancel` (void) → `$additionalPermissions = ['transaksi.penjualan.cancel']`, balikkan stok.

---

## Tahap 4 — Cash Ledger (Buku Kas)

**Tujuan:** mutasi uang tercatat rapi per cabang (inti "ledger").

- [ ] Migration: tabel `kas_mutasi` (section 4.7 db doc).
- [ ] Saat transaksi LUNAS tunai: insert `kas_mutasi` MASUK (`bayar`) + KELUAR (`kembali`).
- [ ] UI Laporan Kas (`laporan.kas.list`): filter cabang, per tanggal, saldo akhir.
- [ ] Aksi `laporan.kas.export` → `$additionalPermissions = ['laporan.kas.export']`.

---

## Tahap 5 — Pembelian & HPP

**Tujuan:** barang masuk & harga beli punya sumber asli.

- [ ] Migration: `pembelian` + `pembelian_detail` (section 9.4 db doc).
- [ ] UI `pembelian.list / .create / .edit` (module `transaksi.pembelian.*`).
- [ ] Status `TERIMA` → `DB::transaction()`: `stok_mutasi` MASUK + update `barang.harga_beli` & `barang_stok`.
- [ ] Aksi `transaksi.pembelian.receive`, `.cancel` → `$additionalPermissions`.

---

## Tahap 6 — Jurnal Akuntansi (Laba-Rugi otomatis)

**Tujuan:** setiap uang tercatat 2 sisi, laporan keuangan jadi otomatis.

- [ ] Migration: `akun`, `jurnal`, `jurnal_detail` (section 9.2–9.3 db doc).
- [ ] Seed akun dasar: Kas, Persediaan, HPP, Penjualan, Beban, Utang, Modal.
- [ ] Service `JurnalService`:
  - Dari transaksi LUNAS → jurnal (Kas debet, Penjualan kredit, HPP debet, Persediaan kredit).
  - Dari pembelian TERIMA → jurnal (Persediaan debet, Utang/Hutang kredit).
- [ ] UI `laporan.buku-besar.list`, `laporan.laba-rugi.list` (agregat dari `jurnal_detail`).
- [ ] Aksi `laporan.laba-rugi.export` → `$additionalPermissions`.

---

## Tahap 7 — Piutang, Hutang & Pajak

**Tujuan:** transaksi belum lunas & utang supplier tercatat.

- [ ] Migration: `piutang`, `hutang`, tabel pelunasan (section 9.5 db doc) + kolom `pajak` di `transaksi` & `pembelian`.
- [ ] UI `transaksi.piutang.*`, `transaksi.hutang.*` (list + pelunasan).
- [ ] Saat pelunasan → `kas_mutasi` + `jurnal` + update sisa piutang/hutang.
- [ ] Pajak masuk ke `jurnal` (akun PPN Masukan/Keluaran).
- [ ] Aksi `*.pay`, `*.cancel` → `$additionalPermissions`.

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
