# AGENTS.md — Peringatan Database

## ⚠️ PERINGATAN PENTING

Database pointing ke **Windows MySQL** (`192.168.1.6`, database `laravel_pos_true`).

**JANGAN jalankan perintah berikut tanpa konfirmasi user:**
- `php artisan migrate:fresh` — DROP semua tabel + re-create (DATA HILANG)
- `php artisan migrate:refresh` — rollback + re-migrate (DATA HILANG)
- `php artisan db:seed` — bisa hapus & re-create data
- `php artisan migrate:reset` — rollback semua migration

**Perintah AMAN:**
- `php artisan migrate` — hanya jalankan migration yang belum
- `php artisan migrate --force` — sama, force tanpa prompt
- `php artisan db:seed --class=NamaSeeder --force` — seed specific class saja

**Sebelan SELALU:**
1. Backup database dulu
2. Cek dengan `php artisan migrate:status` untuk lihat migration mana yang belum jalan
3. Hanya jalankan migration baru, JANGAN fresh/refresh

**Jika data hilang:**
- Cek apakah ada backup MySQL di Windows
- Atau restore dari backup terakhir
