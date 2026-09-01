# Database List

Dokumentasi lengkap struktur database POS berdasarkan migrasi di `src/database/migrations`.

---

## 1. users
**Sumber:** `0001_01_01_000000_create_users_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| name | string | Nama lengkap user |
| email | string | Unique |
| email_verified_at | timestamp | Nullable |
| password | string | Password |
| remember_token | string | Token remember |
| cabang_id | bigint | Foreign key ke `cabang.id` (nullable) |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 2. cabang
**Sumber:** `2026_08_24_000001_create_cabang_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| kode_cabang | string | Unique |
| nama_cabang | string | |
| alamat | text | Nullable |
| is_aktif | boolean | Default true |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 3. barang
**Sumber:** `2026_06_13_081130_create_barang_table.php`, `2026_08_24_000004_add_harga_beli_to_barang_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| kode_barang | string | Unique |
| nama_barang | string | |
| stok | integer | Default 0 (dalam pcs) |
| harga_beli | decimal(18,2) | Default 0 |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 4. barang_satuan
**Sumber:** `2026_06_13_081225_create_barang_satuan_table.php`, `2026_08_24_000005_add_columns_to_barang_satuan_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| barang_id | bigint | Foreign key ke `barang.id`, cascade delete |
| nama_satuan | string | Nama satuan (pcs, lusin, dll) |
| konversi | integer | Konversi ke pcs |
| harga_jual | decimal(18,2) | Default 0 |
| harga_beli | decimal(18,2) | Default 0 |
| is_default | boolean | Default false |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 5. barang_stok
**Sumber:** `2026_08_24_000002_create_barang_stok_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| barang_id | bigint | Foreign key ke `barang.id`, cascade delete |
| cabang_id | bigint | Foreign key ke `cabang.id`, cascade delete |
| stok | integer | Default 0 |
| created_at | timestamp | |
| updated_at | timestamp | |

**Constraint:** Unique (`barang_id`, `cabang_id`)

---

## 6. transaksi
**Sumber:** `2026_06_13_081429_create_transaksi_table.php`, `2026_08_24_000006_add_fields_to_transaksi_table.php`, `2026_08_28_000004_add_pajak_to_transaksi_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| nomor_transaksi | string | Unique |
| tanggal | date | |
| customer | string | Nullable |
| grand_total | decimal(15,2) | Default 0 |
| cabang_id | bigint | Foreign key ke `cabang.id`, nullable |
| user_id | bigint | Foreign key ke `users.id`, nullable |
| status | enum | SELESAI / BATAL / PIUTANG, default SELESAI |
| metode_bayar | enum | TUNAI / TRANSFER / QRIS, default TUNAI |
| bayar | decimal(15,2) | Default 0 |
| kembali | decimal(15,2) | Default 0 |
| diskon_total | decimal(15,2) | Default 0 |
| pajak | decimal(10,2) | Default 0 |
| catatan | text | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 7. transaksi_detail
**Sumber:** `2026_06_13_081449_create_transaksi_detail_table.php`, `2026_07_14_000000_add_diskon_to_transaksi_detail_table.php`, `2026_08_24_000007_add_snapshot_to_transaksi_detail_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| transaksi_id | bigint | Foreign key ke `transaksi.id`, cascade delete |
| barang_id | bigint | Foreign key ke `barang.id` |
| barang_satuan_id | bigint | Foreign key ke `barang_satuan.id` |
| qty | decimal(15,2) | |
| harga | decimal(15,2) | |
| diskon | decimal(15,2) | Default 0 |
| harga_beli | decimal(15,2) | Default 0 (snapshot) |
| nama_barang | string | Nullable (snapshot) |
| nama_satuan | string | Nullable (snapshot) |
| subtotal | decimal(15,2) | |
| qty_pcs | integer | Qty dikonversi ke pcs |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 8. stok_mutasi
**Sumber:** `2026_06_13_083512_create_stok_mutasis_table.php`, `2026_08_24_000008_add_fields_to_stok_mutasi_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| barang_id | bigint | Foreign key ke `barang.id`, cascade delete |
| cabang_id | bigint | Foreign key ke `cabang.id`, nullable |
| transaksi_id | bigint | Foreign key ke `transaksi.id`, nullable |
| barang_satuan_id | bigint | Foreign key ke `barang_satuan.id`, nullable |
| tanggal | date | |
| tipe | enum | MASUK / KELUAR |
| qty | integer | |
| qty_satuan | decimal(15,2) | Nullable |
| keterangan | string | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 9. kas_mutasi
**Sumber:** `2026_08_24_000009_create_kas_mutasi_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| cabang_id | bigint | Foreign key ke `cabang.id`, cascade delete |
| tanggal | date | |
| tipe | enum | MASUK / KELUAR |
| sumber | enum | PENJUALAN / SETOR / TARIK / REFUND / LAIN, default PENJUALAN |
| transaksi_id | bigint | Foreign key ke `transaksi.id`, nullable |
| jumlah | decimal(15,2) | |
| saldo_akhir | decimal(15,2) | Nullable |
| keterangan | string | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 10. pembelian
**Sumber:** `2026_08_24_000010_create_pembelian_table.php`, `2026_08_28_000005_add_pajak_to_pembelian_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| nomor_beli | string | Unique |
| cabang_id | bigint | Foreign key ke `cabang.id`, cascade delete |
| supplier | string | |
| tanggal | date | |
| total | decimal(15,2) | Default 0 |
| pajak | decimal(10,2) | Default 0 |
| status | enum | ORDER / TERIMA / BATAL, default ORDER |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 11. pembelian_detail
**Sumber:** `2026_08_24_000011_create_pembelian_detail_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| pembelian_id | bigint | Foreign key ke `pembelian.id`, cascade delete |
| barang_id | bigint | Foreign key ke `barang.id` |
| barang_satuan_id | bigint | Foreign key ke `barang_satuan.id` |
| qty | decimal(15,2) | |
| harga_beli | decimal(15,2) | |
| subtotal | decimal(15,2) | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 12. akun
**Sumber:** `2026_08_24_000012_create_akun_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| kode_akun | string | Unique |
| nama_akun | string | |
| tipe | enum | ASET / UTANG / MODAL / PENDAPATAN / BEBAN |
| cabang_id | bigint | Foreign key ke `cabang.id`, nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 13. jurnal
**Sumber:** `2026_08_24_000013_create_jurnal_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| tanggal | date | |
| nomor_jurnal | string | Unique |
| keterangan | text | Nullable |
| transaksi_id | bigint | Foreign key ke `transaksi.id`, nullable |
| cabang_id | bigint | Foreign key ke `cabang.id`, cascade delete |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 14. jurnal_detail
**Sumber:** `2026_08_24_000014_create_jurnal_detail_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| jurnal_id | bigint | Foreign key ke `jurnal.id`, cascade delete |
| akun_id | bigint | Foreign key ke `akun.id`, cascade delete |
| debit | decimal(15,2) | Default 0 |
| kredit | decimal(15,2) | Default 0 |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 15. piutang
**Sumber:** `2026_08_28_000001_create_piutang_table.php`, `2026_08_28_000006_add_customer_to_piutang_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| cabang_id | bigint | Foreign key ke `cabang.id`, cascade delete |
| transaksi_id | bigint | Foreign key ke `transaksi.id`, cascade delete |
| customer | string | |
| nomor_piutang | string | Unique |
| tanggal | date | |
| jumlah | decimal(15,2) | |
| sisa | decimal(15,2) | Default 0 |
| status | enum | BELUM_LUNAS / LUNAS, default BELUM_LUNAS |
| catatan | text | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 16. hutang
**Sumber:** `2026_08_28_000002_create_hutang_table.php`, `2026_08_28_000007_add_supplier_to_hutang_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| cabang_id | bigint | Foreign key ke `cabang.id`, cascade delete |
| pembelian_id | bigint | Foreign key ke `pembelian.id`, cascade delete |
| supplier | string | |
| nomor_hutang | string | Unique |
| tanggal | date | |
| jumlah | decimal(15,2) | |
| sisa | decimal(15,2) | Default 0 |
| status | enum | BELUM_LUNAS / LUNAS, default BELUM_LUNAS |
| catatan | text | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 17. pelunasan
**Sumber:** `2026_08_28_000003_create_pelunasan_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| cabang_id | bigint | Foreign key ke `cabang.id`, cascade delete |
| jenis | enum | PIUTANG / HUTANG |
| referensi_id | bigint | Foreign key ke `piutang.id` atau `hutang.id` (nullable) |
| tanggal | date | |
| jumlah | decimal(15,2) | |
| metode_bayar | enum | TUNAI / TRANSFER / QRIS |
| catatan | text | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

**Index:** (`jenis`, `referensi_id`)

---

## 18. system_routes
**Sumber:** `2026_07_24_085534_create_system_routes_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| route_name | string | Unique, nama route Laravel |
| display_name | string | Nama baku halaman |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 19. menus
**Sumber:** `2026_07_25_015934_create_menus_table.php`, `2026_08_18_150000_add_launcher_group_to_menus_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| parent_id | bigint | Foreign key ke `menus.id`, nullable, restrict on delete |
| system_route_id | bigint | Foreign key ke `system_routes.id`, unique, restrict on delete |
| title | string | Judul menu |
| icon | string | Nullable |
| sort_order | unsigned integer | Default 0 |
| is_sidebar | boolean | Default true |
| launcher_group | string(50) | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 20. launcher_groups
**Sumber:** `2026_08_18_160000_create_launcher_groups_table.php`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| key | string | Unique |
| label | string | |
| icon | string | Nullable |
| sort_order | unsigned integer | Default 0 |
| is_active | boolean | Default true |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Tabel Laravel Default

| Tabel | Sumber | Keterangan |
|-------|--------|------------|
| password_reset_tokens | 0001_01_01_000000 | Reset password |
| sessions | 0001_01_01_000000 | Session storage |
| cache | 0001_01_01_000001 | Cache storage |
| cache_locks | 0001_01_01_000001 | Cache lock |
| jobs | 0001_01_01_000002 | Queue jobs |
| job_batches | 0001_01_01_000002 | Batch jobs |
| failed_jobs | 0001_01_01_000002 | Failed queue jobs |

---

## Tabel Spatie Permission

| Tabel | Sumber | Keterangan |
|-------|--------|------------|
| permissions | 2026_05_04_090117 | Daftar permission |
| roles | 2026_05_04_090117 | Daftar role |
| model_has_permissions | 2026_05_04_090117 | Pivot permission-user/role |
| model_has_roles | 2026_05_04_090117 | Pivot role-user |
| role_has_permissions | 2026_05_04_090117 | Pivot role-permission |

---

## Ringkasan ERD (Hubungan Utama)

```
cabang (1) ———— (N) barang_stok
cabang (1) ———— (N) transaksi
cabang (1) ———— (N) kas_mutasi
cabang (1) ———— (N) pembelian
cabang (1) ———— (N) akun
cabang (1) ———— (N) jurnal
cabang (1) ———— (N) piutang
cabang (1) ———— (N) hutang
cabang (1) ———— (N) pelunasan

users (1) ———— (N) transaksi (via user_id)
barang (1) ———— (N) barang_satuan
barang (1) ———— (N) barang_stok
barang (1) ———— (N) transaksi_detail
barang (1) ———— (N) stok_mutasi
barang (1) ———— (N) pembelian_detail

barang_satuan (1) ———— (N) transaksi_detail
barang_satuan (1) ———— (N) pembelian_detail
barang_satuan (1) ———— (N) stok_mutasi

transaksi (1) ———— (N) transaksi_detail
transaksi (1) ———— (1) piutang
transaksi (1) ———— (N) kas_mutasi
transaksi (1) ———— (N) jurnal

pembelian (1) ———— (1) hutang
pembelian (1) ———— (N) pembelian_detail

jurnal (1) ———— (N) jurnal_detail
akun (1) ———— (N) jurnal_detail

piutang (1) ———— (N) pelunasan
hutang (1) ———— (N) pelunasan
```
