# Database POS Ledger — Analisa & Proposal Skema

> Tujuan: dokumentasikan struktur database POS + Ledger, temukan kekurangan,
> dan usulkan skema lengkap (harga beli/jual, multi-satuan, multi-cabang, laporan).
>
> Status: **DOKUMENTASI / ANALISA**. Sesuai `PROJECT_RULES_v2.md` pasal 3 & 12,
> migration **tidak boleh dibuat/diubah tanpa approval**. Di bawah ini hanya usulan.

---

## 1. Dasar: POS & Ledger buat pemula

- **POS (Point of Sale)** = alat kasir untuk catat penjualan harian.
  Intinya: barang → keranjang (cart) → transaksi → bayar → struk.
- **Ledger** = "buku besar" = catatan mutasi (masuk/keluar) yang **berurutan &
  traceable**, dipakai untuk audit & rekonsiliasi uang/stok.
  Di POS ada 2 ledger utama:
  1. **Sales Ledger / Buku Penjualan** → 1 baris per invoice (nomor_transaksi).
  2. **Cash Ledger / Buku Kas** → 1 baris per mutasi uang (setoran, kembalian, dll).
- **Laporan** = ringkasan dari ledger (per hari / per cabang / per barang),
  bukan tabel baru, tapi `VIEW` atau query agregat.

---

## 2. Skema Existing (hasil analisa `src/database/migrations`)

| Tabel | Kolom penting | Fungsi | Catatan |
|---|---|---|---|
| `barang` | `kode_barang`, `nama_barang`, `stok` (pcs) | Master barang | ❌ belum ada **harga beli** |
| `barang_satuan` | `barang_id`, `nama_satuan`, `konversi`, `harga_jual` | Multi-satuan + harga jual | ❌ belum ada **harga beli** per satuan |
| `transaksi` | `nomor_transaksi` (invoice), `tanggal`, `customer`, `grand_total` | Header penjualan | ❌ belum ada cabang, kasir, metode bayar, status |
| `transaksi_detail` | `transaksi_id`, `barang_id`, `barang_satuan_id`, `qty`, `harga`, `diskon`, `subtotal`, `qty_pcs` | Baris penjualan | ❌ belum ada harga beli (untuk laba) & snapshot nama |
| `stok_mutasi` | `barang_id`, `tanggal`, `tipe` (MASUK/KELUAR), `qty`, `keterangan` | Mutasi stok | ❌ belum ada cabang & referensi transaksi |

**Yang SUDAH jalan:**
- Invoice number → `transaksi.nomor_transaksi` ✅ (ini "nama invoice" yang kamu tanyakan).
- Multi-satuan → `barang_satuan` + `konversi` + `qty_pcs` ✅ (sudah benar).

---

## 3. Gap / Kekurangan (yang belum ada)

1. **Harga beli (COGS)** — tidak ada di mana pun. Tanpa ini tidak bisa hitung **laba/rugi**.
   Perlu di `barang` (harga beli per pcs) dan/atau `barang_satuan` (harga beli per satuan).
2. **Multi-cabang** — tidak ada tabel `cabang` dan tidak ada `cabang_id` di transaksi/stok.
3. **Kasir / user** — `transaksi` belum mencatat siapa yang jual (`user_id`).
4. **Pembayaran** — belum ada metode (tunai/transfer/qris), jumlah bayar, & kembalian.
   `grand_total` ada, tapi "uang dibayar berapa & kembali berapa" belum tercatat → Cash Ledger gagal.
5. **Status transaksi** — belum ada (LUNAS, BATAL, PIUTANG).
6. **Snapshot nama** di `transaksi_detail` — kalau barang diubah namanya nanti,
   laporan histori jadi salah. Baiknya simpan `nama_barang` & `nama_satuan` saat transaksi.
7. **Referensi stok** — `stok_mutasi` belum nyambung ke `transaksi_id`, jadi sulit audit
   "stok keluar ini dari invoice mana".
8. **Stok per cabang** — `barang.stok` global, tidak per cabang.

---

## 4. Proposal Skema Lengkap (usulan, butuh approval)

### 4.1 `cabang` (BARU — untuk multi-cabang)
```
id
kode_cabang   (unique)
nama_cabang
alamat        (nullable)
is_aktif      (default true)
timestamps
```

### 4.2 `barang` (TAMBAH kolom)
```
+ harga_beli   decimal(18,2) default 0   -- harga beli per PCS (acuan COGS)
```
> Stok tetap dihitung pcs. Harga beli satuan lain dihitung: `harga_beli * konversi`.

### 4.3 `barang_satuan` (TAMBAH kolom)
```
+ harga_beli   decimal(18,2) default 0   -- harga beli per satuan ini
+ is_default   boolean default false     -- satuan utama tampil di kasir
```

### 4.4 `transaksi` (TAMBAH kolom)
```
+ cabang_id        foreignId -> cabang
+ user_id          foreignId -> users     -- kasir
+ status           enum(SELESAI, BATAL, PIUTANG) default SELESAI
+ metode_bayar     enum(TUNAI, TRANSFER, QRIS) default TUNAI
+ bayar            decimal(15,2) default 0  -- uang diterima
+ kembali          decimal(15,2) default 0  -- uang kembalian
+ diskon_total     decimal(15,2) default 0
+ catatan          text nullable
```
> `nomor_transaksi` sudah jadi nomor invoice. Saran format: `INV/{cabang}/{YYYYMMDD}/{seq}`.

### 4.5 `transaksi_detail` (TAMBAH kolom)
```
+ harga_beli      decimal(15,2) default 0  -- snapshot COGS saat jual (untuk laba)
+ nama_barang     string                  -- snapshot nama
+ nama_satuan     string                  -- snapshot satuan
```
> Perhitungan laba per baris: `(harga - diskon - harga_beli) * qty`.

### 4.6 `stok_mutasi` (REVISI kolom)
```
+ cabang_id        foreignId -> cabang
+ transaksi_id     foreignId -> transaksi (nullable)  -- referensi sumber
+ barang_satuan_id foreignId -> barang_satuan (nullable)
+ qty_satuan       decimal(15,2)  -- qty dalam satuan dipakai (bukan cuma pcs)
```
> `qty` tetap pcs (untuk akumulasi `barang.stok`), `qty_satuan` untuk info satuan.

### 4.7 `kas_mutasi` (BARU — Cash Ledger / Buku Kas)
Ini inti "ledger" uang. 1 baris tiap ada uang masuk/keluar per cabang.
```
id
cabang_id      foreignId -> cabang
tanggal        date
tipe           enum(MASUK, KELUAR)
sumber         enum(PENJUALAN, SETOR, TARIK, REFUND, LAIN)
transaksi_id   foreignId -> transaksi (nullable)
jumlah         decimal(15,2)
saldo_akhir    decimal(15,2)   -- saldo kas cabang setelah mutasi (untuk rekonsiliasi)
keterangan     string nullable
timestamps
```
> Saat `transaksi` LUNAS tunai: insert `kas_mutasi` MASUK sejumlah `bayar`
> (atau `grand_total`), dan saat kembalian: KELUAR sejumlah `kembali`.

---

## 5. Multi-Satuan — STATUS: SUDAH ADA ✅

Sudah benar pakai `barang_satuan` (`konversi` + `harga_jual` + `qty_pcs`).
Tinggal tambahkan `harga_beli` & `is_default` di atas agar lengkap.
Contoh:
```
barang "Aqua 1 galon" stok 100 pcs
  satuan: pcs   konversi 1   harga_jual 5000  harga_beli 4000
  satuan: dus   konversi 12  harga_jual 55000 harga_beli 48000
```

---

## 6. Multi-Cabang — STATUS: BELUM, USULAN ✅ bisa

Cara paling aman & scalable:
1. Buat tabel `cabang`.
2. Tambah `cabang_id` ke `transaksi`, `stok_mutasi`, `kas_mutasi`.
3. **Stok per cabang**: pilih salah satu:
   - (a) Tambah tabel `barang_stok` (`barang_id`, `cabang_id`, `stok`) → stok terpisah per cabang (REKOMENDASI), atau
   - (b) Biarkan `barang.stok` global & catat cabang di `stok_mutasi` (cukup untuk laporan, tapi stok global kurang akurat).
4. Semua laporan di-filter `WHERE cabang_id = ...`.

---

## 7. Laporan (dari Ledger, bukan tabel baru)

| Laporan | Sumber | Filter |
|---|---|---|
| Penjualan harian | `transaksi` + `transaksi_detail` | tanggal, cabang |
| Laba/rugi | `transaksi_detail` (`harga - diskon - harga_beli`) | periode, cabang |
| Buku kas | `kas_mutasi` | cabang, sumber |
| Mutasi stok | `stok_mutasi` | barang, cabang |
| Per barang (invoice) | `transaksi_detail` → `transaksi.nomor_transaksi` | kode_barang |

> "Nama invoice" = `transaksi.nomor_transaksi`. Sudah ada, tinggal ditampilkan di laporan.

---

## 8. Ringkasan Keputusan yang Perlu Approval

- [ ] Tambah tabel `cabang` & `kas_mutasi`; kolom `harga_beli` di barang & barang_satuan.
- [ ] Tambah `cabang_id`, `user_id`, `status`, `metode_bayar`, `bayar`, `kembali` ke `transaksi`.
- [ ] Tambah snapshot `harga_beli`, `nama_barang`, `nama_satuan` ke `transaksi_detail`.
- [ ] Revisi `stok_mutasi` (cabang_id, transaksi_id, barang_satuan_id, qty_satuan).
- [ ] Pilih skema stok per cabang: tabel `barang_stok` (a) atau global (b).

Setelah approval, baru dibuat migration-nya.

---

## 9. Modul Akuntansi (biar jadi "akunting" beneran)

> Kamu baru belajar, jadi kita bahas pakai analogi dulu.
> POS = "kasir nyatat jualan". Akunting = "buku yang mencatat UANG MASUK, UANG KELUAR,
> dan UTANG, biar tahu untung/rugi & posisi keuangan".

### 9.1 Konsep wajib (debit & kredit) — simpelnya

Lupa istilah akuntansi rumit. Intinya **setiap transaksi uang dicatat 2 kali**:
- SATU sisi "dari mana uangnya" (kas masuk / utang naik)
- SATU sisi "untuk apa" (penjualan / beban / aset)

Contoh: jualan tunai Rp 100.000.
```
KAS           +100.000   (uang masuk ke kas)
PENJUALAN     -100.000   (pendapatan bertambah)
```
Dua baris ini disebut **JURNAL**. Total harus SELALU 0 (seimbang). Itu inti akuntansi.

### 9.2 `akun` — Daftar Akun (Chart of Accounts) — BARU
Sebelum jurnal, kita butuh "nama-nama buku" tempat mencatat.
```
id
kode_akun      (contoh: 1001 Kas, 4001 Penjualan, 5001 HPP)
nama_akun      (Kas Toko, Penjualan, Beban Listrik, dll)
tipe           enum(ASET, UTANG, MODAL, PENDAPATAN, BEBAN)
cabang_id      foreignId -> cabang (nullable, kalau global kosong)
timestamps
```
> Bayangkan `akun` seperti label folder: "Kas", "Utang", "Penjualan", "Beban".

### 9.3 `jurnal` — Jurnal Umum (double-entry) — BARU
Ini "buku besar" utama akuntansi.
```
id
tanggal        date
nomor_jurnal   string unique
keterangan     string
transaksi_id   foreignId -> transaksi (nullable)  -- kalau dari POS
cabang_id      foreignId -> cabang
timestamps
```
```
id
jurnal_id      foreignId -> jurnal
akun_id        foreignId -> akun
debit          decimal(15,2) default 0
kredit         decimal(15,2) default 0
```
> Setiap `jurnal` punya minimal 2 baris `jurnal_detail` (debit & kredit) yang totalnya SAMA.

**Otomatis dari POS:** saat transaksi LUNAS tunai, sistem bikin 1 jurnal:
```
KAS         DEBIT   grand_total
PENJUALAN   KREDIT  grand_total
HPP         DEBIT   total_harga_beli
PERSEDIAAN  KREDIT  total_harga_beli
```
(Jadi laba otomatis kehitung di laporan.)

### 9.4 Pembelian & HPP — BARU
POS sekarang cuma tahu "barang keluar", tapi **barang masuk (beli dari supplier)** belum ada.
Tanpa ini, `harga_beli` & stok masuk tidak punya sumber asli.
```
id
nomor_beli    string unique            -- contoh: PO/2026/001
cabang_id     foreignId -> cabang
supplier      string
tanggal       date
total         decimal(15,2)
status        enum(ORDER, TERIMA, BATAL)
timestamps

id
pembelian_id  foreignId -> pembelian
barang_id     foreignId -> barang
barang_satuan_id foreignId -> barang_satuan
qty           decimal(15,2)
harga_beli    decimal(15,2)   -- harga beli aktual (ini yang update barang.harga_beli)
subtotal      decimal(15,2)
```
> Saat `pembelian` TERIMA → insert `stok_mutasi` MASUK + update `barang.harga_beli`.

### 9.5 Piutang & Hutang — BARU
- **Piutang** = uang dari pelanggan yang belum dibayar (transaksi status PIUTANG).
- **Hutang** = uang ke supplier belum dibayar.
```
id   -- PIUTANG
transaksi_id  foreignId -> transaksi
customer      string
sisa          decimal(15,2)   -- sisa yang belum lunas
status        enum(BELUM, LUNAS)
timestamps

id   -- HUTANG
pembelian_id  foreignId -> pembelian
supplier      string
sisa          decimal(15,2)
status        enum(BELUM, LUNAS)
timestamps

-- Tabel pelunasan (cicil):
id
piutang_id / hutang_id
tanggal
jumlah
```
> Tiap pelunasan → `kas_mutasi` KELUAR/MASUK + `jurnal`.

### 9.6 Pajak (PPN) — TAMBAH kolom
```
transaksi: + pajak decimal(15,2) default 0
pembelian: + pajak decimal(15,2) default 0
```
> Pajak masuk juga ke `jurnal` (akun PPN Masukan / PPN Keluaran).

### 9.7 Laporan Akuntansi (dari jurnal, bukan tabel baru)
| Laporan | Cara dapat | Gunanya |
|---|---|---|
| **Laba-Rugi** | PENDAPATAN - BEBAN - HPP (dari jurnal) | Tahau untung/rugi periode |
| **Neraca** | ASET = UTANG + MODAL (saldo akun) | Posisi keuangan saat ini |
| **Buku Besar** | semua jurnal per `akun_id` | Audit tiap akun |
| **Arus Kas** | `kas_mutasi` per cabang | Uang masuk/keluar nyata |

---

## 10. Kesimpulan: Cukup atau Belum?

- **POS + pembukuan kas/stok sederhana** → skema bagian 4–7 **SUDAH CUKUP**.
- **Akunting standar (neraca, laba-rugi, journal entry)** → butuh tambahan bagian 9:
  `akun`, `jurnal` + `jurnal_detail`, `pembelian` + detail, `piutang`, `hutang`, kolom pajak.

Saran jalur belajar & bangun bertahap:
1. Selesaikan POS dulu (section 4–7): jualan, stok, multi-satuan, multi-cabang.
2. Tambah **pembelian** (section 9.4) supaya harga_beli & stok punya sumber.
3. Baru tambah **jurnal akuntansi** (section 9.2–9.3) untuk laba-rugi otomatis.
4. Terakhir **piutang/hutang & pajak** (section 9.5–9.6).

---

## 11. Ringkasan Keputusan yang Perlu Approval (FULL)

- [ ] Tabel `cabang`, `kas_mutasi`
- [ ] Kolom `harga_beli` di `barang` & `barang_satuan`; `is_default` di `barang_satuan`
- [ ] Kolom `cabang_id`, `user_id`, `status`, `metode_bayar`, `bayar`, `kembali`, `diskon_total`, `pajak` di `transaksi`
- [ ] Snapshot `harga_beli`, `nama_barang`, `nama_satuan` di `transaksi_detail`
- [ ] Revisi `stok_mutasi` (cabang_id, transaksi_id, barang_satuan_id, qty_satuan)
- [ ] Tabel `barang_stok` (stok per cabang)
- [ ] Modul akuntansi: `akun`, `jurnal` + `jurnal_detail`
- [ ] Modul pembelian: `pembelian` + `pembelian_detail`
- [ ] Modul piutang/hutang + pelunasan
- [ ] Kolom `pajak` di `transaksi` & `pembelian`

Setelah approval, baru dibuat migration-nya.
