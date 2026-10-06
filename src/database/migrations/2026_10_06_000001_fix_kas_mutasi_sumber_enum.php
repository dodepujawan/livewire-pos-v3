<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kas_mutasi MODIFY COLUMN sumber ENUM('PENJUALAN', 'SETOR', 'TARIK', 'REFUND', 'LAIN', 'PELUNASAN_PIUTANG', 'PELUNASAN_HUTANG') DEFAULT 'PENJUALAN'");
        }

        // SQLite (testing) — enum tidak di-enforce; migration asli sudah diperbarui.
        // Tidak perlu aksi tambahan di sini.
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kas_mutasi MODIFY COLUMN sumber ENUM('PENJUALAN', 'SETOR', 'TARIK', 'REFUND', 'LAIN') DEFAULT 'PENJUALAN'");
        }
    }
};
