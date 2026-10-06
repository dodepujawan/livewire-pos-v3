<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transaksi MODIFY COLUMN status ENUM('DRAFT', 'SELEpaI', 'BATAL', 'PIUTANG') DEFAULT 'SELEPAI'");
        }
        // SQLite tidak enforce enum constraint (disimpan TEXT), jadi nilai 'DRAFT'
        // sudah bisa dipakai tanpa alter column.
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transaksi MODIFY COLUMN status ENUM('SELEpaI', 'BATAL', 'PIUTANG') DEFAULT 'SELEPAI'");
        }
    }
};
