<!-- INI YNG DI COPY KE AI AGENT -->
# Milestone — Desktop POS & Ledger

> Panduan bertahap membangun aplikasi POS + Ledger dari `docs/database_pos_ledger.md`.
> Ditulis rapi & sederhana biar siapa pun (termasuk AI lain) bisa lanjutkan.
>
> **Dokumentasi terkait:**
> - `docs/MEGA_PLAN_pos_ledger.md` — Rencana teknis lengkap (migration, model, routes, components, urutan pengerjaan). AI selanjutnya **WAJIB** baca ini dulu sebelum lanjut coding.
> - `docs/MODULE_milestone_desktop_pos_ledger.md` — Log progress per tahap + hints untuk AI selanjutnya.
>
> Aturan wajib (dari `PROJECT_RULES_v2.md` & `hak-akses-ai.md`):
> - **Migration tidak boleh dibuat/diubah tanpa approval.**
> - Livewire pakai **MFC** (satu component = satu folder), bukan SFC.
> - Route & permission: `module.resource.action` (lihat `src/routes/web.php`).
> - Route `.list/.create/.edit/.show` → permission otomatis.
>   Aksi bisnis lain (delete/print/export/import/cancel) → tambah ke `$additionalPermissions`.
> - Pakai `DB::transaction()` kalau 1 aksi ubah banyak tabel.
>
> **Cara membaca status lama:** `[x]` di Tahap 0–8 berarti fiturnya pernah dibuat,
> bukan berarti seluruh alur sudah lolos audit akuntansi. Perbaikan hasil audit
> dilacak pada Tahap 9 ke atas.
>
> **Keamanan data:** jangan ubah data produksi, menjalankan migration, atau
> menjalankan seeder tanpa approval dan backup yang sudah diverifikasi. Database
> aplikasi ini terhubung ke MySQL di Windows; rekonsiliasi stok/jurnal harus
> direncanakan terpisah dari perubahan kode.

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

- [x] Migration: kolom `transaksi` (section 4.4 db doc):
  `cabang_id`, `user_id`, `status`, `metode_bayar`, `bayar`, `kembali`, `diskon_total`, `pajak`, `catatan`.
- [x] Migration: snapshot `transaksi_detail` (`harga_beli`, `nama_barang`, `nama_satuan`) + revisi `stok_mutasi` (`cabang_id`, `transaksi_id`, `barang_satuan_id`, `qty_satuan`).
- [x] Update component `transaksi.penjualan.create`:
  - Pilih cabang (default cabang kasir), metode bayar, input bayar → hitung kembali otomatis.
  - Simpan `harga_beli` & nama snapshot ke detail.
- [x] Saat simpan: `DB::transaction()` → insert transaksi + detail + `stok_mutasi` KELUAR per cabang + kurangi `barang_stok`.
- [x] Tambah `transaksi.penjualan.cancel` (void) → `$additionalPermissions = ['transaksi.penjualan.cancel']`, balikkan stok.

---

## Tahap 4 — Cash Ledger (Buku Kas)

**Tujuan:** mutasi uang tercatat rapi per cabang (inti "ledger").

- [x] Migration: tabel `kas_mutasi` (section 4.7 db doc).
- [x] Saat transaksi LUNAS tunai: insert `kas_mutasi` MASUK (`bayar`) + KELUAR (`kembali`).
- [x] UI Laporan Kas (`laporan.kas.list`): filter cabang, per tanggal, saldo akhir.
- [x] Saat transaksi BATAL (cancel) tunai: insert `kas_mutasi` KELUAR (`bayar`) + MASUK (`kembali`) dengan sumber REFUND.
- [x] Permission `laporan.kas.view` + `laporan.kas.export` (auto via route naming).

---

## Tahap 5 — Pembelian & HPP

**Tujuan:** barang masuk & harga beli punya sumber asli.

- [x] Migration: `pembelian` + `pembelian_detail` (section 9.4 db doc).
- [x] UI `pembelian.list / .create / .edit` (module `transaksi.pembelian.*`).
- [x] Status `TERIMA` → `DB::transaction()`: `stok_mutasi` MASUK + update `barang.harga_beli` & `barang_stok`.
- [x] Aksi `transaksi.pembelian.receive`, `.cancel` → `$additionalPermissions`.
- [x] `cancelPembelian()` fix: jika status TERIMA, rollback stok (KELUAR) sebelum set BATAL.

---

## Tahap 6 — Jurnal Akuntansi (Laba-Rugi otomatis)

**Tujuan:** setiap uang tercatat 2 sisi, laporan keuangan jadi otomatis.

- [x] Migration: `akun`, `jurnal`, `jurnal_detail` (section 9.2–9.3 db doc).
- [x] Seed akun dasar: Kas, Persediaan, HPP, Penjualan, Beban, Utang, Modal.
- [x] Service `JurnalService`:
  - Dari transaksi LUNAS → jurnal (Kas debet, Penjualan kredit, HPP debet, Persediaan kredit).
  - Dari pembelian TERIMA → jurnal (Persediaan debet, Utang/Hutang kredit).
  - Refund → kebalikan jurnal penjualan.
- [x] UI `laporan.buku-besar.list`, `laporan.laba-rugi.list` (agregat dari `jurnal_detail`).
- [x] Aksi `laporan.laba-rugi.export` → `$additionalPermissions`.
- [x] Update `transaksi-create` trigger `JurnalService::buatJurnalPenjualan()` saat status SELESAI.
- [x] Update `pembelian-edit` trigger `JurnalService::buatJurnalPembelian()` saat receive.

---

## Tahap 7 — Piutang, Hutang & Pajak

**Tujuan:** transaksi belum lunas & utang supplier tercatat.

- [x] Migration: `piutang`, `hutang`, tabel pelunasan (section 9.5 db doc) + kolom `pajak` di `transaksi` & `pembelian`.
- [x] UI `transaksi.piutang.*`, `transaksi.hutang.*` (list + pelunasan).
- [x] Saat pelunasan → `kas_mutasi` + `jurnal` + update sisa piutang/hutang.
- [x] Pajak masuk ke `jurnal` (akun PPN Masukan/Keluaran).
- [x] Aksi `*.pay`, `*.cancel` → `$additionalPermissions`.
- [x] Status transaksi PIUTANG → auto-create `piutang` record.

---

## Tahap 8 — Laporan Gabungan & Final

- [x] `laporan.penjualan.list` (per invoice `nomor_transaksi`, per barang, per cabang).
- [x] `laporan.stok.list` (mutasi `stok_mutasi` per barang/cabang).
- [x] `laporan.neraca.list` (ASET = UTANG + MODAL dari saldo `akun`).
- [x] `laporan.arus-kas.list` (dari `kas_mutasi`).
- [x] Update `MenuSeeder` — sidebar menu lengkap.
- [x] Update dokumentasi.

---

## Cara menjalankan (tiap milestone)

1. Baca milestone dan dokumen teknis terkait, termasuk `docs/MEGA_PLAN_pos_ledger.md`.
2. Mulai dengan sesi tanya-jawab: jelaskan istilah memakai bahasa sederhana dan contoh angka sebelum memilih aturan.
3. Sepakati contoh hasil yang diinginkan, lalu buat Mega Plan dan tunggu approval sebelum coding.
4. Buat/ubah migration hanya setelah approval Tahap 0, backup, dan pengecekan status migration.
5. Buat atau ubah Model + relationship sesuai kebutuhan.
6. Buat Livewire MFC component + route (ikuti `module.resource.action`) dan permission yang sesuai.
7. Pakai `DB::transaction()` untuk aksi multi-tabel; lindungi pelunasan dari pembayaran bersamaan.
8. Jalankan `php artisan framework:permission-sync` bila ada route/permission baru.
9. Test skenario yang disepakati dengan `php artisan test`; jangan menjalankan test yang mereset database produksi.
10. Perbarui checklist milestone dan `docs/MODULE_milestone_desktop_pos_ledger.md`, termasuk istilah yang dipelajari dan pertanyaan yang belum terjawab.

> Ingat: jangan ubah migration/program inti tanpa approval. Dokumentasi ini
> adalah acuan bersama, bisa dilanjutkan AI mana pun asal baca `docs/` dulu.

> tambahan tolong catat progress disini docs/MODULE_milestone_desktop_pos_ledger.md kalo perlu kasi sedikit hints untuk selanjutnya sehingga ai lain ada acuan lebih clear

---

## Upgrade Setelah Audit (Tahap 9+)

Tahap ini memperbaiki fitur yang sudah ada. Kerjakan berurutan karena jurnal dan
laporan bergantung pada aturan akun, metode pembayaran, serta stok yang benar.
Jangan tandai selesai hanya karena halaman atau kolomnya sudah tersedia; gunakan
kriteria selesai di setiap tahap.

### Snapshot Audit (3 Oktober 2026)

Pengecekan database di bawah hanya membaca hitungan agregat, tidak mengubah data.
Angka dapat berubah; cek ulang sebelum membuat rencana perbaikan data.

- 13 transaksi `SELESAI` memiliki jurnal penjualan, tetapi 3 transaksi memiliki jurnal penjualan lebih dari satu.
- Ada 1 transaksi `DRAFT` dan 1 `BATAL` yang ikut terhitung pada total default Laporan Penjualan.
- Ada 16 mutasi kas: 14 `MASUK`, 2 `KELUAR`; semuanya bersumber dari `PENJUALAN`.
- Semua 16 mutasi kas memiliki `saldo_akhir` kosong; halaman menampilkannya sebagai nol.
- Kode akun transaksi masih ditulis langsung di service; belum ada halaman pengelolaan akun.
- Tidak ada test khusus yang menguji alur akuntansi, jurnal, atau stok per cabang.

### Tahap 9 — Sepakati Aturan Pembukuan

**Tujuan:** programmer tidak menebak-nebak aturan uang, pajak, dan persediaan.

- [x] Akun Kas berarti uang fisik; transfer masuk ke Bank; QRIS masuk ke akun penampung sampai penyedia mencairkannya.
- [x] Pakai dasar akrual: penjualan/piutang dicatat saat barang dijual; pelunasan hanya menambah Kas/Bank dan mengurangi Piutang.
- [x] Dukung pembayaran sebagian; uang yang diterima dan sisa Piutang dicatat terpisah.
- [x] Pembelian supplier memisahkan penerimaan barang dari pembayaran: barang menambah stok saat diterima, tagihan yang belum dibayar menjadi Hutang, dan pembayaran mengurangi Hutang serta Kas/Bank.
- [x] Laporan Kas mencatat penerimaan bersih penjualan. Nilai Bayar dan Kembali tetap disimpan di transaksi/struk.
- [x] Karena cabang satu perusahaan, gunakan bagan akun bersama dan tandai setiap transaksi dengan cabang untuk laporan per cabang/gabungan.
- [x] `barang_stok` menjadi sumber stok tiap cabang; total semua cabang di layar dihitung dari tabel ini. Kolom lama `barang.stok` bukan lagi saldo operasional dan dipertahankan sementara untuk kompatibilitas/migrasi.
- [x] Sepakati pembatalan transaksi selesai dengan pencatatan pembalik; rincian biaya QRIS, setoran, dan koreksi kas tetap perlu diputuskan.
- [ ] Catat keputusan pajak dan saldo awal bersama pemilik/akuntan; jangan menetapkan aturan pajak hanya dari asumsi programmer.

#### Hasil Tanya-Jawab

Keputusan berikut disetujui pada 6 Oktober 2026 dan menjadi dasar contoh di tahap selanjutnya:

- Tunai Rp80.000 dicatat sebagai uang masuk bersih Rp80.000. Jika pelanggan menyerahkan Rp100.000 dan menerima kembalian Rp20.000, kedua nilai itu tetap ada di struk, tetapi laporan kas penjualan mencatat netonya.
- Transfer dicatat ke Bank setelah pembayaran terkonfirmasi.
- QRIS dicatat sementara sebagai Dana QRIS Belum Cair, lalu dipindahkan ke Bank saat penyedia mencairkannya.
- Jika tagihan Rp80.000 dibayar Rp30.000 sekarang, catat Rp30.000 ke Kas/Bank dan sisa Rp50.000 sebagai Piutang.
- Pembelian mengikuti pola serupa: barang menambah stok saat diterima; bila belum dibayar, catat Hutang; pembayaran sebagian/lunas mengurangi Hutang dan Kas/Bank.
- Cabang-cabang memakai bagan akun bersama; laporan tetap bisa difilter per cabang dan dilihat gabungan.
- Stok transaksi selalu dibaca/diubah per cabang. Total pusat dihitung dengan menjumlahkan baris cabang; nilai lama `barang.stok` bukan lagi total yang disimpan/diedit.
- Transaksi selesai yang dibatalkan tidak dihapus: buat catatan pembalik, pulihkan stok, kembalikan uang bila sudah diterima, dan simpan alasan pembatalan.
- Pajak belum diputuskan; validasi tarif dan cara pencatatannya dengan akuntan sebelum posting pajak dibuat.

**Selesai jika:** contoh pembayaran tunai, transfer/QRIS, Piutang/Hutang dan pembayaran sebagian sudah dipahami; pembatalan memakai jejak pembalik; biaya QRIS, pajak, dan saldo awal ditandai jelas sebelum alur terkait dibuat.

### Tahap 10 — Jadikan Stok Per Cabang sebagai Sumber Utama

**Tujuan:** stok cabang A tidak ikut berubah saat cabang B menjual atau menerima barang.

- [x] Gunakan `barang_stok(barang_id, cabang_id)` sebagai angka stok yang dibaca dan diubah transaksi.
- [x] Hentikan penggunaan `barang.stok` global untuk validasi penjualan, penerimaan pembelian, edit barang, dan saldo stok cabang.
- [x] Hentikan edit manual atas `barang.stok`; layar/export menghitung total dari stok cabang. Kolom lama tetap ada dan baru boleh dihapus setelah semua pemakaian ditemukan serta data lama direkonsiliasi.
- [x] Sesuaikan stok saat jual, edit transaksi, batal/refund, terima/batal pembelian, koreksi stok manual, dan transfer antar cabang.
- [x] Pisahkan data katalog barang dari saldo stok. Stok awal/koreksi manual memerlukan cabang; koreksi membutuhkan alasan dan dicatat di `stok_mutasi`.
- [x] Excel tambah barang: stok awal opsional; bila ada stok, wajib pilih cabang. Tanpa stok awal, import hanya membuat katalog.
- [x] Excel update barang hanya mengubah data barang/satuan; kolom total stok export hanya informasi dan tidak ditulis kembali.
- [x] Excel memiliki mode penyesuaian stok terpisah: pilih cabang, isi jumlah akhir dan alasan; mutasi dicatat satu per barang.
- [ ] Rekonsiliasi nilai `barang.stok` lama ke stok cabang melalui rencana yang disetujui; jangan membagi stok global ke semua cabang secara otomatis.

**Progress 6 Oktober 2026:** perubahan kode stok per cabang dan mode koreksi Excel sudah dibuat. Pengecekan service terisolasi di SQLite in-memory, PHP lint, dan kompilasi Blade lulus. Test PHPUnit belum dapat dijalankan karena paket PHPUnit tidak ada di container dan PHP lokal 8.3.17 (project meminta >=8.4.1). Belum ada migration atau perubahan data produksi. Tahap 10 belum selesai sampai stok historis direkonsiliasi dan pengujian aplikasi dijalankan di lingkungan yang sesuai.

**Selesai jika:** menerima 5 barang di cabang A hanya menambah stok A; menjual 2 di cabang B hanya mengurangi stok B; total pusat merupakan jumlah cabang, bukan sumber stok transaksi.

### Tahap 11 — Rapikan Daftar dan Pemetaan Akun

**Tujuan:** sistem tahu uang transfer masuk ke Bank, bukan selalu ke Kas.

- [ ] Pertahankan akun dasar dari seeder yang idempotent, lalu sediakan halaman admin untuk akun tambahan yang memang dibutuhkan usaha.
- [ ] Validasi kode akun unik, kategori akun terkontrol, akun yang sudah dipakai jurnal tidak bisa dihapus, dan akun lama bisa dinonaktifkan.
- [ ] Tambahkan akun Bank dan/atau penampung QRIS sesuai rekening/provider yang digunakan; jangan memakai kode yang bentrok dengan akun Piutang/Persediaan saat ini.
- [ ] Ganti kode akun hard-coded (`1001`, `4001`, dan seterusnya) dengan pemetaan akun yang bisa ditinjau admin.
- [ ] Tentukan apakah bagan akun satu untuk semua cabang atau per cabang, lalu samakan aturan kode unik dan filter laporan.

**Selesai jika:** setiap metode pembayaran menunjuk akun tujuan yang benar dan akun yang tidak tersedia membuat transaksi gagal dengan pesan jelas, bukan membuat jurnal kosong.

### Tahap 12 — Betulkan Jurnal Penjualan, Pembelian, dan Pelunasan

**Tujuan:** setiap kejadian dicatat satu kali, pada akun dan waktu yang tepat.

- [ ] Penjualan tunai mencatat Kas; transfer mencatat Bank; QRIS mencatat Bank atau akun penampung QRIS sesuai waktu pencairan.
- [ ] Penjualan `PIUTANG` mencatat Piutang dan Penjualan saat penjualan terjadi. Saat pelanggan membayar, catat Kas/Bank bertambah dan Piutang berkurang; jangan mengakui Penjualan untuk kedua kalinya.
- [ ] Dukung pelunasan sebagian: saldo Piutang/Hutang turun hanya sebesar pembayaran yang berhasil diterima/dibayar.
- [ ] Pembelian berstatus diterima membuat catatan Hutang yang dapat dilunasi, jurnal, mutasi stok, dan saldo stok cabang dalam satu transaksi database.
- [ ] Pelunasan Piutang/Hutang menghormati metode Tunai/Transfer/QRIS. Samakan daftar sumber yang diizinkan database dengan sumber yang ditulis service.
- [ ] Edit transaksi yang sudah dijurnal memperbarui jurnal terkait atau membuat pembalikan dan jurnal pengganti; jangan menambah jurnal penjualan penuh berulang kali.
- [ ] Pembatalan penjualan/pembelian yang sudah berdampak membuat jurnal pembalik dan menyesuaikan kas/bank serta stok dengan benar.
- [ ] Pastikan debit dan kredit jurnal seimbang, posting tidak membuat jurnal duplikat, dan kegagalan salah satu langkah membatalkan seluruh perubahan terkait.
- [ ] Periksa transaksi lama yang memiliki 3 jurnal penjualan duplikat. Buat rencana koreksi dengan daftar transaksi, nilai, dan dampak laporan; minta approval sebelum mengubah data produksi.

**Selesai jika:** contoh tunai, transfer, QRIS, piutang lunas/sebagian, pembelian diterima/dibatalkan, edit, dan refund menghasilkan jurnal seimbang dan tidak ganda.

### Tahap 13 — Pisahkan Buku Kas dan Bank

**Tujuan:** laporan menunjukkan uang berada di laci kas, rekening bank, atau masih di penyedia QRIS.

- [ ] Catat hanya uang tunai fisik pada Laporan Kas, kecuali keputusan Tahap 9 menetapkan bentuk lain.
- [ ] Catat transfer pada rekening Bank yang sesuai; catat QRIS yang belum cair pada akun penampung dan pindahkan ke Bank saat dana benar-benar cair.
- [ ] Catat biaya transfer/QRIS terpisah dari nilai penjualan jika memang ada.
- [ ] Tambahkan sumber pelunasan, setoran, penarikan, refund, dan koreksi kas dengan daftar tipe yang konsisten antara aplikasi dan database.
- [ ] Hitung saldo berjalan dari saldo awal + uang masuk - uang keluar, atau gunakan cara lain yang disetujui dan selalu terbarui. Jangan menampilkan `0` sebagai saldo jika nilainya sebenarnya kosong.
- [ ] Sediakan laporan Kas/Bank dengan filter cabang, rekening/akun, metode/sumber, dan periode; tidak perlu halaman terpisah per metode kecuali dibutuhkan untuk rekonsiliasi provider.

**Selesai jika:** saldo laporan cocok dengan saldo awal ditambah/kurang semua mutasi dan dapat dicocokkan dengan hitung kas/rekening nyata.

### Tahap 14 — Betulkan Rumus dan Filter Laporan

**Tujuan:** angka di laporan mengikuti transaksi/jurnal yang benar dan bisa ditelusuri.

- [ ] Laporan Penjualan tidak memasukkan `DRAFT`/`BATAL` ke total penjualan normal; status tersebut tetap bisa dilihat lewat filter khusus.
- [ ] Samakan rumus subtotal, diskon, pajak, total invoice, dan nilai jurnal. Jangan mengurangi diskon dua kali.
- [ ] Perbaiki Laporan Arus Kas agar total MASUK dan KELUAR memakai query independen; saat ini filter MASUK terbawa ke perhitungan KELUAR.
- [ ] Perbaiki Neraca: tanda saldo ASET, UTANG, MODAL, Pendapatan, dan Beban mengikuti kategori akun; uji bahwa Aset = Utang + Modal + laba/rugi.
- [ ] Perbaiki Laba Rugi agar jurnal refund/pembalik mengurangi pendapatan, bukan diabaikan.
- [ ] Perbaiki Buku Besar agar filter nomor jurnal, tanggal, cabang, dan akun tidak saling bocor; sediakan pilihan akun dan saldo berjalan.
- [ ] Putuskan serta terapkan filter cabang yang konsisten: laporan per cabang atau gabungan seluruh cabang.

**Selesai jika:** total laporan dapat ditelusuri ke transaksi/jurnal sumber, hasil filter tidak mengambil data di luar filter, dan Neraca seimbang.

### Tahap 15 — Tes dan Rekonsiliasi Pra-Closing

**Tujuan:** pastikan stok, pembayaran, jurnal, dan laporan sudah dapat dipercaya sebelum menambah proses closing.

- [ ] Tambahkan test untuk stok dua cabang, import Excel katalog/stok, penjualan tunai/non-tunai/piutang, pelunasan sebagian, penerimaan/pembatalan pembelian, refund, dan semua laporan terkait.
- [ ] Tambahkan pemeriksaan otomatis: jurnal seimbang, tidak ada jurnal duplikat untuk satu kejadian, saldo stok cabang tidak berubah karena cabang lain, dan saldo kas/bank cocok dengan mutasi.
- [ ] Sebelum migration: minta approval, cek `php artisan migrate:status`, backup MySQL dan verifikasi backup. Jalankan hanya migration baru yang sudah disetujui.
- [ ] Jangan gunakan `migrate:fresh`, `migrate:refresh`, `migrate:reset`, atau `db:seed` pada database ini tanpa approval eksplisit dan backup terverifikasi.
- [ ] Rekonsiliasi stok global lama, jurnal duplikat, dan saldo kas secara terpisah. Simpan hasil sebelum/sesudah dan approval; jangan otomatis menghapus jurnal lama.
- [ ] Perbarui `docs/MODULE_milestone_desktop_pos_ledger.md` setiap tahap selesai, termasuk test, migration, keputusan yang dipakai, dan sisa risiko.

**Selesai jika:** test relevan lulus, laporan cocok dengan contoh yang disepakati, data lama punya rencana rekonsiliasi yang disetujui, dan dokumen progress diperbarui.

### Tahap 16 — Closing Kasir dan Penutupan Periode (tahap terakhir)

**Tujuan:** memastikan uang dan laporan sudah diperiksa sebelum satu hari atau satu periode dianggap selesai. Closing dibuat setelah stok, akun, jurnal, dan laporan pada Tahap 9–15 dapat dipercaya.

#### A. Tutup kasir harian

- [ ] Sepakati apakah kasir membuka shift dengan mencatat uang awal dan apakah satu shift dimiliki satu kasir/cabang.
- [ ] Hitung uang tunai yang seharusnya ada: saldo awal + uang tunai masuk - uang tunai keluar.
- [ ] Minta kasir memasukkan jumlah uang fisik saat tutup, idealnya per pecahan.
- [ ] Tampilkan selisih antara uang yang dihitung dan angka yang diharapkan; jika berbeda, wajib isi alasan dan catatan.
- [ ] Cocokkan transaksi tunai, kembalian/refund, setoran, dan pengeluaran kas. Transfer/QRIS direkonsiliasi pada akun Bank/penampungnya, bukan dicampur sebagai uang laci.
- [ ] Setelah ditutup, jangan izinkan perubahan diam-diam pada transaksi shift; koreksi harus melalui izin, alasan, dan riwayat audit.

#### B. Tutup buku periode

- [ ] Tentukan periode yang akan ditutup (misalnya bulanan) dan siapa yang boleh menutup/membuka ulang periode.
- [ ] Periksa transaksi tertunda, saldo stok, Piutang/Hutang, Kas/Bank/QRIS, pajak, jurnal debit-kredit, Laba Rugi, dan Neraca.
- [ ] Simpan hasil laporan dan daftar perbedaan yang belum selesai sebelum periode dikunci.
- [ ] Setelah dikunci, cegah transaksi bertanggal lama diedit tanpa pembukaan kembali yang tercatat; koreksi harus melalui jurnal penyesuaian yang disetujui.
- [ ] Putuskan bersama akuntan apakah perlu jurnal penutup untuk memindahkan Pendapatan/Beban ke akun laba ditahan. Jangan membuat jurnal penutup otomatis hanya karena tombol “Tutup Periode” ditekan.
- [ ] Bedakan “menutup/mengunci periode” dari “jurnal penutup”: yang pertama mencegah perubahan periode lama; yang kedua adalah pencatatan akuntansi yang aturan waktunya perlu disepakati.

**Selesai jika:** kasir dapat menjelaskan selisih kas, laporan periode dapat ditelusuri dan disetujui, periode terkunci, serta setiap koreksi setelah closing meninggalkan jejak audit.
