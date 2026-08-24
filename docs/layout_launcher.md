                    POPS
                     │
 ┌───────────────────┼────────────────────┐
 │                   │                    │
 ▼                   ▼                    ▼
PENJUALAN         PEMBELIAN          PERSEDIAAN
 │                   │                    │
 ├ Penjualan         ├ Pembelian          ├ Stok
 ├ Retur             ├ Retur              ├ Mutasi
 ├ Piutang           ├ Hutang             ├ Penyesuaian
 └ Pelunasan         └ Pelunasan          └ Transfer


 ┌───────────────────┼────────────────────┐
 │                   │                    │
 ▼                   ▼                    ▼
KEUANGAN          MASTER DATA           LAPORAN
 │                   │                    │
 ├ Kas & Bank        ├ Barang             ├ Penjualan
 ├ Daftar Akun       ├ Customer            ├ Pembelian
 ├ Jurnal Umum       ├ Supplier            ├ Persediaan
 └ Rekonsiliasi      └ Cabang              ├ Kas
                                          ├ Laba Rugi
                                          ├ Neraca
                                          ├ Buku Besar
                                          └ Arus Kas


                     │
                     ▼
                   SISTEM
                     │
                     ├ User
                     ├ Role & Permission
                     ├ Menu
                     ├ Pengaturan
                     └ Audit Log