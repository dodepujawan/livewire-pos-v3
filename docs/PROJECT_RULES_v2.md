# PROJECT RULES v2
<!-- INI YANG PATEN -->
## 1. Framework
- Laravel 12
- Livewire 4 + MFC + Volt
- TailwindCSS + Alpine.js
- Ikuti struktur project existing.
- Library baru wajib approval.

## 2. Architecture & Files
- Livewire MFC: satu component = satu folder.
- Jangan membuat Single File Component.
- Cari file/component existing sebelum membuat baru.
- Hindari duplicate code/component.
- Utamakan `resources/views/components/`.
- buat fungsi atau button link spa dengan wire:navigate

## 3. Database & Query
- Migration tidak boleh dibuat/diubah/dihapus tanpa approval.
- Gunakan Eloquent relationship.
- Query Builder hanya jika diperlukan.
- Hindari N+1 dan query di Blade.
- Gunakan pagination untuk list.

## 4. Naming & Route
- Class: PascalCase
- Method/property/variable: camelCase
- Database: snake_case
- Route name: dot.notation

Pola route:
`module.resource.action`

Contoh:
- `master.barang.list`
- `master.barang.create`
- `master.barang.edit`
- `transaksi.penjualan.list`
- `transaksi.penjualan.create`
- `transaksi.penjualan.show`

Mapping permission:
- list/show → view
- create/store → create
- edit/update → update
- destroy/delete → delete
- print/export/import → action yang sama

Permission memakai:
`module.resource.action`

Contoh:
- `master.barang.view`
- `master.barang.create`
- `master.barang.update`
- `master.barang.delete`

Jangan membuat nama route/permission sembarangan karena digunakan Permission Engine.
lengakpanya bisa baca disisni path:docs/hak-akses-ai.md

## 5. Authorization
- Jangan membuat sistem role/permission baru.
- Jangan memakai pengecekan manual seperti `role === 'admin'`.
- Gunakan permission framework yang tersedia.
- Route yang membutuhkan permission memakai permission middleware.
- Permission tambahan gunakan mekanisme `additionalPermissions`.

## 6. Controller & Service
- Controller menangani HTTP/request orchestration.
- Business logic kompleks gunakan Service.
- Hindari business logic besar di Controller.
- Jangan duplicate logic existing.

## 7. Livewire
- Gunakan `wire:navigate`.
- Gunakan `WithPagination` jika diperlukan.
- Search gunakan debounce.
- Jangan gunakan protected `$casts` pada Livewire Component.

Input:
- Search/barcode/autocomplete → `wire:model.live`
- Qty/diskon/pembayaran → `wire:model.live.debounce.500ms`
- Input biasa → `wire:model.blur`
- Readonly → jangan gunakan `wire:model`

Numeric:
- Input boleh berupa string saat mengetik.
- `""`/`null` dianggap 0.
- Normalisasi sebelum operasi matematika.
- Gunakan helper seperti `toFloat()` jika diperlukan.

Keyboard:
- Enter wajib `.prevent`.
- Enter tidak submit form kecuali tombol Simpan.
- Enter Kode Barang → `searchBarang()`.
- Enter Qty → `addToCart()`.

## 8. POS UI/UX
Desktop-first: 1920x1080, 1600x900, 1366x768.

Prioritas:
1. Keyboard > mouse
2. Kecepatan input > estetika
3. Informasi penting tanpa scroll
4. Cart sebagai area kerja utama
5. Total pembayaran selalu terlihat
6. Fokus cursor jelas
7. Klik seminimal mungkin

Gunakan Grid, sticky area, dan overflow seperlunya.

Cart:
- Qty/diskon inline.
- Subtotal dan grand total langsung dihitung.
- Tanpa modal/page reload.
- Mendukung Tab/Enter.
- Update Livewire seminimal mungkin.

## 9. UI Components
- Maksimalkan `resources/views/components/`.
- Reuse component existing sebelum membuat baru.
- Gunakan TailwindCSS + Alpine.js.
- Jangan membuat markup UI berulang tanpa alasan.
- UI change tidak boleh mengubah business logic tanpa alasan.
- ketika ada fungsi simpan update dan delete maka maximalakan component/form/loading

## 10. Transactions & Error Handling
Jika satu operasi bisnis mengubah beberapa tabel, gunakan `DB::transaction()`.

Contoh:
- transaksi + detail + stok
- pembayaran + jurnal
- jurnal + ledger

Gunakan `try/catch` jika ada recovery atau feedback yang diperlukan.
Jangan membungkus semua operasi sederhana dengan try/catch.
Gunakan `report($e)` untuk error penting.
Jangan tampilkan exception mentah kepada user.

## 11. Testing
Fitur penting harus memiliki test yang relevan:
- happy path
- authorization
- validation
- failure case untuk logic penting

Setelah perubahan:
`php artisan test`

Jangan menghapus/melemahkan test hanya agar pass.

## 12. Framework Core Protection
Framework core yang sudah selesai jangan diubah hanya untuk satu module.

Sebelum mengubah framework:
1. Pastikan kebutuhan tidak bisa diselesaikan di module.
2. Jelaskan alasan dan dampaknya.
3. Tunggu approval.

## 13. AI Workflow
Sebelum coding:
1. Analisa project, database, model, dan component terkait.
2. Cari implementation existing.
3. Buat Mega Plan.
4. Tunggu approval.
5. Implement per milestone.

Setelah coding:
1. Self-review.
2. Jalankan test relevan.
3. Laporkan file yang berubah.
4. Laporkan hasil test.

Saat review/analisa:
- Bahasa Indonesia.
- Jangan langsung mengubah file.
- Jelaskan akar masalah, dampak, dan solusi.
- Tunggu approval.

## 14. Documentation
Setiap milestone selesai:
- Update `docs/CHANGELOG.md`
- Update `MODULE_*.md` yang relevan.

Project knowledge wajib ada di repository, bukan hanya chat.

## 15. Property Naming
Gunakan nama property yang jelas dan sesuai konteks.

Untuk component kompleks, gunakan prefix/domain
untuk menghindari property ambigu.

Contoh:
- transNoInvoice
- transGrandTotal
- itemQty
- itemHarga
- bayarNominal
- kembaliNominal
- searchKeyword

Nama generic seperti nama, harga, qty, atau total boleh digunakan
jika konteks component sudah jelas dan tidak menimbulkan ambiguitas.

catatn tambahan ketita pelru mengedit dan membuat file baru wajib tanya programmer untuk aproval tapi kalo delete file itu big no, harus di hapus manual dan akmu buat notifikasi besar wajib hapus file ini pathnya biar prgrammer yang hapus

apbila butuh artisan jalankan docker compose exec app bash -> cd src -> artisan 

#untuk lebih hemat token baca juga ini
.kiloignore